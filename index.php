<?php
require 'vendor/autoload.php'; // Tải các thư viện qua Composer

use Dotenv\Dotenv;

session_start();

$dotenv = Dotenv::createImmutable(__DIR__, 'config.env');
$dotenv->load();

// Lấy giá trị từ biến môi trường
$clientId = $_ENV['GOOGLE_CLIENT_ID'];
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
$redirectUri = $_ENV['GOOGLE_REDIRECT_URI'];


// Khởi tạo Google Client
$client = new Google_Client();
$client->setClientId($clientId); // Đặt Client ID
$client->setClientSecret($clientSecret); // Đặt Client Secret
$client->setRedirectUri($redirectUri); // Đặt Redirect URI
$client->addScope(['openid', 'email', 'profile']); // Yêu cầu quyền truy cập OpenID, Email, và Hồ sơ

// Tạo link để đăng nhập với Google
$authUrl = $client->createAuthUrl();
header('Location: ' . $authUrl);


