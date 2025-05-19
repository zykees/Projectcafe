<?php
session_start();
require_once '../config/db.php';
require_once '../config/line-config.php';

if (!isset($_GET['code']) || !isset($_SESSION['line_state']) || $_GET['state'] !== $_SESSION['line_state']) {
    $_SESSION['error'] = 'การยืนยันตัวตนผิดพลาด';
    header('Location: ../user/profile.php');
    exit;
}
if (!isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE line_id = ?");
    $stmt->execute([$profile['userId']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
    }
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

    $token_data = json_decode($token_response, true);
    
    // ดึงข้อมูลผู้ใช้ LINE
    $profile_response = file_get_contents('https://api.line.me/v2/profile', false, stream_context_create([
        'http' => [
            'header' => 'Authorization: Bearer ' . $token_data['access_token']
        ]
    ]));

    $profile = json_decode($profile_response, true);

    // อัพเดตข้อมูลในฐานข้อมูล
    $stmt = $pdo->prepare("UPDATE users SET line_id = ? WHERE id = ?");
    $stmt->execute([$profile['userId'], $_SESSION['user_id']]);

    $_SESSION['success'] = 'เชื่อมต่อบัญชี LINE เรียบร้อยแล้ว';

} catch (Exception $e) {
    $_SESSION['error'] = 'เกิดข้อผิดพลาดในการเชื่อมต่อบัญชี LINE';
}

header('Location: ../user/profile.php');
exit;