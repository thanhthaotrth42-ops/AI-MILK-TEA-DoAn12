
<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $redis = new Predis\Client([
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 6379
    ]);

    $result = $redis->ping();

    echo "Ket noi Redis thanh cong!<br>";
    echo "Redis PING: " . htmlspecialchars((string)$result);
} catch (Throwable $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo "Khong the ket noi Redis. Kiem tra Redis Server.";
}
?>
