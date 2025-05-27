<?php
session_start();
require_once '../config/db.php';
require_once '../config/line-config.php';

// ตรวจสอบ state เพื่อความปลอดภัย
if (!isset($_GET['code']) || !isset($_SESSION['line_state']) || $_GET['state'] !== $_SESSION['line_state']) {
    $_SESSION['error'] = 'การยืนยันตัวตนผิดพลาด';
    header('Location: ../user/profile.php');
    exit;
}

// ตรวจสอบว่ามีการ login อยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อนเชื่อมต่อบัญชี LINE';
    header('Location: ../auth/login.php');
    exit;
}

try {
    // แลกโค้ดเพื่อรับ access token
    $token_response = file_get_contents('https://api.line.me/oauth2/v2.1/token', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'grant_type' => 'authorization_code',
                'code' => $_GET['code'],
                'redirect_uri' => 'http://localhost/Projectcafe/user_management/auth/line-callback.php',
                'client_id' => '2007056322',
                'client_secret' => '7067df184a82334cb7b0c7753124c83d'
            ])
        ]
    ]));

    if ($token_response === FALSE) {
        throw new Exception('ไม่สามารถรับ access token ได้');
    }

    $token_data = json_decode($token_response, true);
    if (!isset($token_data['access_token'])) {
        throw new Exception('ไม่พบ access token ในการตอบกลับ');
    }
    
    // ดึงข้อมูลผู้ใช้ LINE
    $profile_response = file_get_contents('https://api.line.me/v2/profile', false, stream_context_create([
        'http' => [
            'header' => 'Authorization: Bearer ' . $token_data['access_token']
        ]
    ]));

    if ($profile_response === FALSE) {
        throw new Exception('ไม่สามารถดึงข้อมูลโปรไฟล์ LINE ได้');
    }

    $profile = json_decode($profile_response, true);
    if (!isset($profile['userId'])) {
        throw new Exception('ไม่พบ userId ในข้อมูลโปรไฟล์');
    }

    // อัพเดตข้อมูลในฐานข้อมูล
    $stmt = $pdo->prepare("UPDATE users SET line_id = ? WHERE id = ?");
    if (!$stmt->execute([$profile['userId'], $_SESSION['user_id']])) {
        throw new Exception('ไม่สามารถอัพเดตฐานข้อมูลได้');
    }

    $_SESSION['line_id'] = $profile['userId'];
    $_SESSION['success'] = 'เชื่อมต่อบัญชี LINE เรียบร้อยแล้ว';

} catch (Exception $e) {
    error_log('LINE Connection Error: ' . $e->getMessage());
    $_SESSION['error'] = 'เกิดข้อผิดพลาดในการเชื่อมต่อบัญชี LINE: ' . $e->getMessage();
}

header('Location: ../user/profile.php');
exit;