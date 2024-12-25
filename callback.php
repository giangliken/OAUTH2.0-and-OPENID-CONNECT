<?php
require './vendor/autoload.php'; // Tải các thư viện qua Composer

use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleServiceOauth2;
use Google\Service\PeopleService as GooglePeopleService;
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
$client->addScope("https://www.googleapis.com/auth/user.birthday.read");
// Nếu người dùng nhấn nút đăng xuất
if (isset($_GET['logout'])) {
    // Hủy session và chuyển hướng về trang chính
    session_destroy();
    header('Location: index.php');
    exit();
}

// Nếu mã ủy quyền đã được trả về
if (isset($_GET['code'])) {
    try {
        // Đổi mã ủy quyền lấy Access Token và ID Token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (isset($token['access_token'], $token['id_token'])) {
            $_SESSION['access_token'] = $token['access_token'];
            $_SESSION['id_token'] = $token['id_token'];

            // Sử dụng API để lấy thông tin người dùng
            $client->setAccessToken($token['access_token']);
            $oauth = new GoogleServiceOauth2($client);
            $userInfo = $oauth->userinfo->get();

            // Lưu thông tin người dùng vào session
            $_SESSION['user'] = [
                'id' => $userInfo->id,
                'name' => $userInfo->name,
                'email' => $userInfo->email,
                'picture' => $userInfo->picture
            ];

            // Hiển thị thông tin người dùng
            //echo 'ID người dùng: ' . htmlspecialchars($userInfo->id) . '<br>';
            //echo 'Tên: ' . htmlspecialchars($userInfo->name) . '<br>';
            //echo 'Email: ' . htmlspecialchars($userInfo->email) . '<br>';
            //echo 'ID Token: ' . $_SESSION['id_token']. '<br>';

            // Xác thực ID Token
            $payload = $client->verifyIdToken($token['id_token']);
            if ($payload) {
                // Chuyển đổi thời gian cấp token và hết hạn token từ UTC sang múi giờ Việt Nam (UTC+7)
                $issuedAt = new DateTime("@{$payload['iat']}");
                $issuedAt->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));  // Thiết lập múi giờ cho Việt Nam (UTC+7)

                $expirationTime = new DateTime("@{$payload['exp']}");
                $expirationTime->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));  // Thiết lập múi giờ cho Việt Nam (UTC+7)

                //echo 'Thời gian cấp token: ' . $issuedAt->format('Y-m-d H:i:s') . '<br>';
                //echo 'Thời gian hết hạn token: ' . $expirationTime->format('Y-m-d H:i:s') . '<br>';
            } else {
                echo 'ID Token không hợp lệ hoặc đã hết hạn.';
            }
        } else {
            //throw new Exception("Không lấy được Access Token hoặc ID Token");
        }
    } catch (Exception $e) {
        echo 'Lỗi: ' . $e->getMessage();
    }
} elseif (isset($_GET['error'])) {
    // Trường hợp người dùng từ chối quyền truy cập
    echo 'Người dùng đã từ chối quyền truy cập.';
} else {
    // Hiển thị nút đăng nhập Google nếu chưa có mã ủy quyền
    $authUrl = $client->createAuthUrl();
    echo '<a href="' . htmlspecialchars($authUrl) . '">Đăng nhập với Google</a>';
}

// Kiểm tra xem người dùng đã đăng nhập chưa trên mọi trang
function checkLoginStatus() {
    global $client;
    
    if (isset($_SESSION['access_token'])) {
        // Người dùng đã đăng nhập, sử dụng thông tin từ session
        echo '<span style="font-size: 24px; font-weight: bold; text-transform: uppercase;">Chào, ' . htmlspecialchars($_SESSION['user']['name']) . ' BẠN ĐÃ ĐĂNG NHẬP THÀNH CÔNG!</span><br>';

        echo 'ID người dùng của bạn: ' . $_SESSION['user']['id'] . '<br>';
        echo 'Email của bạn: ' . $_SESSION['user']['email'] . '<br>';
        // Hiển thị ảnh đại diện
        echo 'Ảnh đại diện: <br>';
        echo '<img src="' . $_SESSION['user']['picture'] . '" alt="Ảnh đại diện" style="width:100px; height:100px; border-radius:50%;"> <br>';
        echo '<a href="http://localhost/PHPTEST/weba.php" target="_blank" style="font-size: 16px; color: blue; text-decoration: none; margin-right: 10px;">Web A</a>';
        echo '<a href="http://localhost/PHPTEST/webb.php" target="_blank" style="font-size: 16px; color: blue; text-decoration: none; margin-right: 10px;">Web B</a>';
        // Hiển thị nút đăng xuất
        echo '<a href="?logout=true">Đăng xuất</a><br>';
    } else {
        // Người dùng chưa đăng nhập, chuyển hướng tới trang đăng nhập
        echo '<span style="font-size: 24px; font-weight: bold; text-transform: uppercase;"> BẠN ĐÃ ĐĂNG XUẤT!</span><br>';

        echo 'Vui lòng đăng nhập. <br>';
        $authUrl = $client->createAuthUrl();
        echo '<a href="' . htmlspecialchars($authUrl) . '">Đăng nhập với Google</a>';
    }
}

// Để kiểm tra trạng thái đăng nhập trên bất kỳ trang nào, gọi hàm này
checkLoginStatus();
?>
