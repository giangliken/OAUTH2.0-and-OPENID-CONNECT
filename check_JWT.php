<?php
require_once 'vendor/autoload.php';

function verify_google_token($id_token) {
    // Khởi tạo Google Client
    $client = new Google_Client(['client_id' => '495995400712-0s0cm5mebekgsbdm64cm9h4umv44n8h0.apps.googleusercontent.com']); 

    // Xác minh ID Token
    try {
        $payload = $client->verifyIdToken($id_token);

        if ($payload) {
            // Nếu ID Token hợp lệ, payload chứa thông tin người dùng
            return $payload;
        } else {
            // Nếu ID Token không hợp lệ
            return null;
        }
    } catch (Exception $e) {
        // Xử lý lỗi
        return null;
    }
}

// Kiểm tra nếu có ID Token gửi lên từ form
if (isset($_POST['id_token'])) {
    $id_token = $_POST['id_token'];
    $user_info = verify_google_token($id_token);

    if ($user_info) {
        $result_message = '<p style="color: green;">ID Token hợp lệ, thông tin người dùng: </p>';
        $result_message .= '<p>ID người dùng: ' . $user_info['sub'] . '</p>';
        $result_message .= '<p>Tên: ' . $user_info['name'] . '</p>';
        $result_message .= '<p>Email: ' . $user_info['email'] . '</p>';
        
        // Chuyển đổi thời gian cấp token và hết hạn token từ UTC sang múi giờ Việt Nam (UTC+7)
        $issuedAt = new DateTime("@{$user_info['iat']}"); // Lấy thời gian cấp từ 'iat'
        $issuedAt->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));  // Thiết lập múi giờ cho Việt Nam (UTC+7)

        $expirationTime = new DateTime("@{$user_info['exp']}"); // Lấy thời gian hết hạn từ 'exp'
        $expirationTime->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));  // Thiết lập múi giờ cho Việt Nam (UTC+7)

        // Hiển thị thời gian cấp và hết hạn token
        $result_message .= '<p>Thời gian cấp token: ' . $issuedAt->format('Y-m-d H:i:s') . '</p>';
        $result_message .= '<p>Thời gian hết hạn token: ' . $expirationTime->format('Y-m-d H:i:s') . '</p>';
    } else {
        $result_message = '<p style="color: red;">ID Token không hợp lệ.</p>';
    }
} else {
    $result_message = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra ID Token</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding-top: 150px; /* Khoảng cách để không bị che bởi fixed div */
        }
        .fixed-form {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
            z-index: 100;
        }
        .result {
            margin-top: 200px; /* Đảm bảo nội dung không bị che khuất */
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 10px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <!-- Form kiểm tra ID Token -->
    <div class="fixed-form">
        <h2>Kiểm tra ID Token</h2>
        <form method="POST">
            <label for="id_token">Dán ID Token vào đây:</label><br>
            <textarea name="id_token" id="id_token" rows="5" cols="50"></textarea><br><br>
            <button type="submit">Kiểm tra</button>
        </form>
    </div>

    <!-- Kết quả kiểm tra -->
    <div class="result">
        <?php echo $result_message; ?>
    </div>

</body>
</html>
