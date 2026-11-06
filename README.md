# AI MILK TEA – TỐI ƯU HÓA HIỆU NĂNG TRUY VẤN AI WEB

**Đồ án Backend – Đề tài 12**

**Chủ đề:** Tối ưu hóa hiệu năng truy vấn AI Web bằng Redis/Memcached và MySQL Index.

**Tên ứng dụng:** AI MILK TEA – Hệ thống tra cứu công thức trà sữa tích hợp trí tuệ nhân tạo.

## 1. Giới thiệu dự án

AI MILK TEA là ứng dụng web được xây dựng bằng PHP và MySQL, cho phép người dùng tra cứu công thức pha chế trà sữa, tìm kiếm nguyên liệu và sử dụng trợ lý AI để nhận hướng dẫn pha chế.

Điểm trọng tâm của đồ án không chỉ là xây dựng website mà còn là **tối ưu hóa hiệu năng hệ thống** thông qua hai kỹ thuật:

- **Redis Cache:** Lưu trữ tạm thời câu trả lời của Gemini AI để hạn chế việc gọi API nhiều lần khi người dùng đặt câu hỏi trùng lặp.
- **MySQL Index:** Tối ưu truy vấn tìm kiếm công thức trong cơ sở dữ liệu.

Hệ thống kết hợp giao diện website, cơ sở dữ liệu, AI và bộ nhớ đệm để minh họa cách cải thiện hiệu suất ứng dụng Backend.

## 2. Mục tiêu đồ án

- Xây dựng website tra cứu công thức trà sữa.
- Tích hợp Gemini AI hỗ trợ giải đáp câu hỏi pha chế.
- Ứng dụng Redis để lưu và tái sử dụng câu trả lời AI.
- Giảm số lần gọi Gemini API khi câu hỏi đã được lưu trong cache.
- Sử dụng MySQL Index để tối ưu truy vấn tìm kiếm.
- Xây dựng chức năng đăng ký, đăng nhập và phân quyền người dùng.
- Xây dựng trang quản trị để quản lý công thức và dữ liệu.
- Kiểm thử hoạt động của Redis Cache và MySQL Index.

## 3. Công nghệ sử dụng

| Thành phần | Công nghệ |
|---|---|
| Frontend | HTML5, CSS3, JavaScript, AJAX |
| Backend | PHP 8.x |
| Database | MySQL |
| Kết nối cơ sở dữ liệu | PDO |
| AI | Google Gemini API |
| Caching | Redis |
| Thư viện Redis PHP | Predis thông qua Composer |
| Quản lý database | phpMyAdmin |
| Môi trường phát triển | WampServer64 |
| Quản lý source code | GitHub |

## 4. Chức năng hệ thống

### 4.1. Người dùng

- Đăng ký tài khoản.
- Đăng nhập và đăng xuất.
- Xem danh sách công thức trà sữa.
- Xem nguyên liệu và hướng dẫn pha chế chi tiết.
- Tìm kiếm công thức theo tên hoặc nguyên liệu.
- Lọc công thức theo danh mục.
- Sử dụng trợ lý Gemini AI để hỏi về cách pha chế.

### 4.2. Quản trị viên (Admin)

- Truy cập trang quản trị.
- Xem thống kê tổng quan.
- Thêm công thức mới.
- Chỉnh sửa công thức.
- Xóa công thức.
- Quản lý danh mục.
- Quản lý tài khoản người dùng.

### 4.3. Bảo mật

- Phân quyền Admin và User.
- Sử dụng PDO và Prepared Statements để hạn chế SQL Injection.
- Kiểm tra phiên đăng nhập.
- Sử dụng CSRF Token cho các thao tác cần bảo vệ.
- Giới hạn số lần đăng nhập sai.
- Lưu Gemini API Key trong file cấu hình riêng, không công khai trên GitHub.

## 5. Tích hợp Gemini AI

Hệ thống tích hợp Google Gemini API để hỗ trợ người dùng đặt câu hỏi liên quan đến pha chế trà sữa.

Ví dụ câu hỏi:

> Hướng dẫn pha trà sữa khoai môn.

Gemini AI có thể trả lời bằng tiếng Việt, bao gồm nguyên liệu cần chuẩn bị và các bước thực hiện.

Chức năng AI được xử lý thông qua Backend PHP, không gọi trực tiếp API từ trình duyệt bằng khóa bí mật.

## 6. Tối ưu hiệu năng bằng Redis Cache

### 6.1. Nguyên lý hoạt động

Khi người dùng gửi câu hỏi:

**Trường hợp 1 – Cache MISS:**

1. Người dùng gửi câu hỏi đến Backend PHP.
2. Hệ thống tạo khóa cache từ nội dung câu hỏi.
3. Redis được kiểm tra nhưng chưa có dữ liệu tương ứng.
4. Backend gửi yêu cầu đến Gemini API.
5. Gemini trả về nội dung trả lời.
6. Backend lưu câu trả lời vào Redis.
7. Kết quả được hiển thị cho người dùng.

**Trường hợp 2 – Cache HIT:**

1. Người dùng gửi lại câu hỏi có cùng nội dung sau khi được chuẩn hóa.
2. Backend kiểm tra Redis.
3. Redis tìm thấy câu trả lời đã lưu.
4. Backend lấy dữ liệu trực tiếp từ Redis.
5. Hệ thống trả kết quả mà không cần gọi lại Gemini API.

### 6.2. Lợi ích

- Giảm số lần gọi Gemini API.
- Hạn chế xử lý lặp lại các câu hỏi giống nhau.
- Giảm thời gian phản hồi đối với câu hỏi đã được cache.
- Giảm lượng yêu cầu gửi đến dịch vụ AI.
- Tăng hiệu quả sử dụng tài nguyên hệ thống.

### 6.3. Cấu hình Cache

- Redis chạy tại `127.0.0.1:6379`.
- Backend kết nối Redis thông qua Predis.
- Khóa cache được tạo từ nội dung câu hỏi đã chuẩn hóa bằng SHA-256.
- Thời gian lưu cache (TTL): **3600 giây (1 giờ)**.

## 7. Tối ưu truy vấn bằng MySQL Index

MySQL Index được sử dụng nhằm cải thiện hiệu suất truy vấn dữ liệu.

Trong quá trình thực hiện đồ án, nhóm sử dụng bảng `recipes_benchmark` để kiểm thử truy vấn tìm kiếm công thức.

Index kiểm thử:

`idx_benchmark_name`

Cột được đánh Index:

`name`

Ví dụ truy vấn:

```sql
EXPLAIN
SELECT *
FROM recipes_benchmark
WHERE name = 'Trà sữa truyền thống';
```

Kết quả kiểm thử trên hệ thống:

- `type = ref`
- `key = idx_benchmark_name`
- `rows = 1`

Kết quả cho thấy MySQL đã lựa chọn sử dụng Index khi thực hiện truy vấn kiểm thử.

## 8. Kiểm thử hệ thống

### 8.1. Kiểm thử website

- Website AI MILK TEA hiển thị danh sách 24 công thức.
- Chức năng tìm kiếm và lọc danh mục hoạt động.
- Trang chi tiết hiển thị nguyên liệu và hướng dẫn pha chế.
- Trang quản trị cho phép quản lý dữ liệu.
- Chức năng Gemini AI trả lời câu hỏi pha chế.

### 8.2. Kiểm thử Redis

Sử dụng Redis CLI:

```bash
PING
```

Kết quả:

```text
PONG
```

Kiểm tra thống kê:

```bash
INFO stats
```

Trong quá trình kiểm thử, chỉ số `keyspace_hits` tăng từ **0 lên 1** sau khi truy vấn lại câu hỏi đã được lưu trong cache.

Điều này xác nhận Redis đã phục vụ ít nhất một lần truy cập cache thành công.

### 8.3. Kiểm thử MySQL Index

Sử dụng `SHOW INDEX` để xác nhận Index đã được tạo và `EXPLAIN` để kiểm tra kế hoạch thực thi truy vấn.

Kết quả cho thấy MySQL sử dụng `idx_benchmark_name` trong truy vấn kiểm thử.

## 9. Cấu trúc source code

Một số file chính:

| File | Chức năng |
|---|---|
| `index.php` | Trang chủ và danh sách công thức |
| `detail.php` | Xem chi tiết công thức |
| `chat.php` | Giao diện trợ lý AI |
| `api_chat.php` | Xử lý câu hỏi Gemini AI |
| `redis_cache.php` | Xử lý kết nối và bộ nhớ đệm Redis |
| `config.php` | Kết nối MySQL |
| `auth.php` | Xác thực và phân quyền |
| `login.php` | Đăng nhập |
| `register.php` | Đăng ký |
| `logout.php` | Đăng xuất |
| `admin.php` | Trang quản trị |
| `admin_recipes.php` | Quản lý công thức |
| `admin_add_recipe.php` | Thêm công thức |
| `admin_edit_recipe.php` | Chỉnh sửa công thức |
| `admin_delete_recipe.php` | Xóa công thức |
| `admin_categories.php` | Quản lý danh mục |
| `admin_users.php` | Quản lý người dùng |
| `composer.json` | Khai báo thư viện PHP |
| `vendor/` | Thư viện được cài đặt bằng Composer |
| `ai_milk_tea_github.sql` | Cấu trúc và dữ liệu database dùng để triển khai |

## 10. Hướng dẫn cài đặt và chạy chương trình

### Bước 1: Chuẩn bị môi trường

Cài đặt hoặc chuẩn bị:

- WampServer64 có PHP 8.x và MySQL.
- phpMyAdmin.
- Redis Server.
- Trình duyệt web.
- Kết nối Internet để sử dụng Gemini AI.

### Bước 2: Tải source code

Tải repository từ GitHub và đặt thư mục dự án tại:

```text
C:\wamp64\www\DoAn12
```

### Bước 3: Khởi động WampServer

Khởi động Apache và MySQL, bảo đảm các dịch vụ đang hoạt động.

### Bước 4: Import database

1. Truy cập `http://localhost/phpmyadmin/`.
2. Tạo database `ai_milk_tea`.
3. Chọn database vừa tạo.
4. Chọn chức năng **Import / Nhập**.
5. Import file `ai_milk_tea_github.sql`.

**Lưu ý:** Bản SQL công khai đã loại bỏ dữ liệu tài khoản mẫu. Người dùng cần đăng ký tài khoản mới. Để sử dụng chức năng Admin, cần cấp quyền `admin` cho tài khoản được phép quản trị trong môi trường thử nghiệm.

### Bước 5: Cấu hình MySQL

Mở file `config.php` và điều chỉnh thông tin kết nối phù hợp với máy chạy chương trình.

Ví dụ môi trường WampServer mặc định:

```php
$host = "localhost";
$dbname = "ai_milk_tea";
$username = "root";
$password = "";
```

Không sử dụng tài khoản database không có mật khẩu trong môi trường triển khai công khai.

### Bước 6: Chuẩn bị Redis

Khởi động Redis Server và bảo đảm Redis đang lắng nghe tại:

```text
127.0.0.1:6379
```

Kiểm tra bằng Redis CLI:

```bash
PING
```

Kết quả mong đợi:

```text
PONG
```

### Bước 7: Cấu hình Gemini API Key

Tạo file:

```text
C:\wamp64\gemini_secret.php
```

Nội dung mẫu:

```php
<?php
return [
    'api_key' => 'YOUR_GEMINI_API_KEY'
];
```

Thay `YOUR_GEMINI_API_KEY` bằng khóa API hợp lệ của riêng bạn.

**Không đưa file `gemini_secret.php` lên GitHub.**

### Bước 8: Cấu hình chứng chỉ SSL

PHP cần được cấu hình chứng chỉ CA hợp lệ để xác minh kết nối HTTPS đến Gemini API.

Trên Windows, có thể cấu hình `curl.cainfo` và `openssl.cafile` trong file `php.ini` đang được Apache sử dụng, trỏ đến tệp CA bundle hợp lệ.

Không tắt chức năng xác minh SSL để khắc phục lỗi chứng chỉ.

### Bước 9: Mở website

Truy cập:

```text
http://127.0.0.1/DoAn12/index.php
```

Đăng ký hoặc đăng nhập để sử dụng các chức năng tương ứng.

## 11. Kết quả đạt được

- Xây dựng được website AI MILK TEA với các chức năng tra cứu công thức.
- Tích hợp Gemini AI để trả lời câu hỏi pha chế.
- Tích hợp Redis Cache và kiểm chứng được Cache HIT.
- Tạo MySQL Index và xác nhận việc sử dụng Index thông qua EXPLAIN.
- Xây dựng hệ thống quản lý công thức và phân quyền tài khoản.
- Triển khai và chạy thử chương trình trên máy tính tại trường.

## 12. Hạn chế và hướng phát triển

- Cache hiện được thiết lập theo thời gian sống cố định.
- Chưa thực hiện đo kiểm hiệu năng trên hệ thống có lượng truy cập lớn.
- Có thể bổ sung bảng thống kê thời gian phản hồi trước và sau khi sử dụng Redis.
- Có thể mở rộng hệ thống quản lý cache và tối ưu thêm các truy vấn MySQL.
- Có thể triển khai ứng dụng trên máy chủ trực tuyến trong tương lai.

## 13. Kết luận

Đồ án AI MILK TEA minh họa việc kết hợp PHP, MySQL, Gemini AI và Redis trong một ứng dụng web thực tế.

Thông qua Redis Cache, hệ thống có thể tái sử dụng câu trả lời đã lưu, hạn chế gọi lại Gemini API. MySQL Index hỗ trợ tối ưu việc truy xuất dữ liệu công thức.

Kết quả kiểm thử cho thấy các thành phần chính đã hoạt động và đáp ứng mục tiêu triển khai kỹ thuật của đề tài.

---

**Đồ án Backend – Đề tài 12**

**AI MILK TEA – Gemini AI + Redis Cache + MySQL Index + PHP/MySQL**
