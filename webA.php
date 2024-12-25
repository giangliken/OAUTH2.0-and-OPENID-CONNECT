<?php
// account_info.php

require './vendor/autoload.php'; // Tải các thư viện qua Composer

use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleServiceOauth2;
use Dotenv\Dotenv;

// Kiểm tra nếu session chưa bắt đầu thì mới gọi session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$dotenv = Dotenv::createImmutable(__DIR__, 'config.env');
$dotenv->load();

// Lấy giá trị từ biến môi trường
$clientId = $_ENV['GOOGLE_CLIENT_ID'];
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
$redirectUri = $_ENV['GOOGLE_REDIRECT_URI'];

// Cài đặt Google Client
$client = new GoogleClient();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

// Nếu người dùng nhấn nút đăng xuất
if (isset($_GET['logout'])) {
    // Hủy session và chuyển hướng về trang chính
    session_destroy();
    header('Location: weba.php'); // Chuyển hướng về trang chính
    exit();
}

// Hiển thị giao diện tài khoản
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website A</title>
    <link rel="stylesheet" href="styles.css"> <!-- File CSS -->
</head>
<body>
    <h1 style="text-align: center; margin-top: 300px;">CHÀO MỪNG BẠN ĐẾN VỚI WEBSITE DEMO A</h1>
    <?php
    
    if (isset($_SESSION['access_token'])) {
        echo '
        <div class="user-info">
            <div class="user-avatar">
                <img src="' . $_SESSION['user']['picture'] . '" alt="Ảnh đại diện">
            </div>
            <div class="user-details">
                <span>Xin chào, <strong>' . htmlspecialchars($_SESSION['user']['name']) . '</strong></span><br>
                <span>ID tài khoản: ' . htmlspecialchars($_SESSION['user']['id']) . '</span><br>
                <span>Email: ' . htmlspecialchars($_SESSION['user']['email']) . '</span><br>
                <a href="?logout=true" class="logout-btn">Đăng xuất</a>
            </div>
        </div>';
    } else {
        $authUrl = $client->createAuthUrl();
        echo '
        <div class="login-container">
            <span>Vui lòng đăng nhập:</span><br>
            <a href="' . htmlspecialchars($authUrl) . '" class="login-btn">Đăng nhập với Google</a>
        </div>';
    }
    ?>

</body>
</html>
