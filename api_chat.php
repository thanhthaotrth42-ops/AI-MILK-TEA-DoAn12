<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/vendor/autoload.php";

header("Content-Type: application/json; charset=utf-8");

function respond($message)
{
    echo json_encode(
        ["reply" => $message],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    respond("Vui lòng đăng nhập để sử dụng AI.");
}

// 2. Chỉ nhận yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    respond("Phương thức không hợp lệ.");
}

// 3. Đọc câu hỏi
$data = json_decode(file_get_contents("php://input"), true);
$message = $data["message"] ?? "";

if (!is_string($message)) {
    http_response_code(400);
    respond("Nội dung câu hỏi không hợp lệ.");
}

$message = trim($message);

if ($message === "") {
    respond("Vui lòng nhập câu hỏi.");
}

if (mb_strlen($message) > 1000) {
    respond("Câu hỏi không được vượt quá 1000 ký tự.");
}

// 4. Đọc Gemini API Key
$secretFile = dirname(__DIR__, 2) . "/gemini_secret.php";

if (!is_file($secretFile)) {
    http_response_code(500);
    respond("Chưa cấu hình Gemini API Key.");
}

$secret = require $secretFile;
$apiKey = $secret["api_key"] ?? "";

if (!is_string($apiKey) || $apiKey === "") {
    http_response_code(500);
    respond("Gemini API Key không hợp lệ.");
}

// 5. Kết nối Redis
$redis = null;
$cacheKey = "ai_milk_tea:" . hash(
    "sha256",
    mb_strtolower($message, "UTF-8")
);

try {
    $redis = new Predis\Client([
        "scheme" => "tcp",
        "host" => "127.0.0.1",
        "port" => 6379,
        "timeout" => 2
    ]);

    $cachedReply = $redis->get($cacheKey);

    if ($cachedReply !== null) {
        respond($cachedReply);
    }
} catch (Throwable $e) {
    error_log("Redis error: " . $e->getMessage());
    $redis = null;
}

// 6. Gọi Gemini AI

$prompt = "Bạn là trợ lý pha chế AI MILK TEA chuyên về trà sữa. "
    . "Hãy trả lời bằng tiếng Việt, thân thiện, chính xác và dễ hiểu. "
    . "Trả lời ngắn gọn, ưu tiên khoảng 100 đến 180 từ, trừ khi người dùng yêu cầu chi tiết. "
    . "Nếu hỏi công thức, hãy nêu nguyên liệu, định lượng và các bước pha chế rõ ràng. "
    . "Chỉ sử dụng văn bản thuần, không dùng Markdown, dấu **, ### hoặc bảng. "
    . "Không giới thiệu dài dòng và không lặp lại câu hỏi. "
    . "Chỉ tập trung vào trà sữa, nguyên liệu và pha chế. "
    . "Câu hỏi: " . $message;


$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent";
$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-goog-api-key: " . $apiKey
    ],
    CURLOPT_POSTFIELDS => json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
    ),
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 90
]);

$result = curl_exec($ch);
$curlError = curl_error($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($result === false) {
    error_log("Gemini connection error: " . $curlError);
    http_response_code(502);
    respond("Lỗi kết nối Gemini: " . $curlError);
}

$response = json_decode($result, true);

$reply = $response["candidates"][0]["content"]["parts"][0]["text"] ?? null;

if ($statusCode !== 200 || !is_string($reply) || trim($reply) === "") {
    $apiError = $response["error"]["message"] ?? "Không có thông tin lỗi từ Google.";

    error_log("Gemini API error: HTTP " . $statusCode . " - " . $apiError);

    respond("Lỗi Gemini (HTTP " . $statusCode . "): " . $apiError);
}

// 7. Lưu câu trả lời vào Redis trong 1 giờ
if ($redis !== null) {
    try {
        $redis->setex($cacheKey, 3600, $reply);
    } catch (Throwable $e) {
        error_log("Redis cache error: " . $e->getMessage());
    }
}

// 8. Trả câu trả lời cho giao diện
respond($reply);