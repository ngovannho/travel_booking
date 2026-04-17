<?php
$host = 'localhost';
$db   = 'travel_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Cấu hình đường dẫn gốc (Base URL) - Hãy đổi localhost thành IP hoặc tên miền của bạn khi đưa lên mạng
define('BASE_URL', 'http://localhost/travel_booking/');

// Cấu hình gửi Mail (SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'vannho14122@gmail.com'); // Đảm bảo đây là email chính xác của bạn
define('SMTP_PASS', 'ymjdleuclkuqufgy'); // Mật khẩu ứng dụng 16 ký tự của bạn
define('MAIL_FROM', 'vannho14122@gmail.com');
define('MAIL_FROM_NAME', 'Lily Travel');
?>