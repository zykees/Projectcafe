<?php
session_start();
require 'config.php';
require '../GoogleLogin/config.php';

if (!isset($_GET['code']) || !isset($_GET['state']) || $_GET['state'] !== $_SESSION['line_state']) {
    die('Invalid access');
}

$code = $_GET['code'];

// ขอ Access Token จาก Line
$token_url = "https://api.line.me/oauth2/v2.1/token";
$headers = ['Content-Type: application/x-www-form-urlencoded'];
$post_data = array(
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => 'http://localhost/Projectcafe/LineLogin/callback.php',
    'client_id' => '2007056322',
    'client_secret' => '7067df184a82334cb7b0c7753124c83d'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response);

// อัพเดทข้อมูลในฐานข้อมูล
if (isset($token_data->access_token)) {
    // ดึงข้อมูลโปรไฟล์จาก Line
    $profile_url = "https://api.line.me/v2/profile";
    $headers = ['Authorization: Bearer ' . $token_data->access_token];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $profile_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $profile_response = curl_exec($ch);
    curl_close($ch);
    
    $profile = json_decode($profile_response);
    
    // บันทึกข้อมูล Line ID ลงในฐานข้อมูล
    $stmt = $pdo->prepare("UPDATE users SET line_id = :line_id WHERE email = :email");
    $stmt->execute([
        ':line_id' => $profile->userId,
        ':email' => $_SESSION['user_email']
    ]);
    
    $_SESSION['line_id'] = $profile->userId;
}

header('Location: ../GoogleLogin/profile.php');
exit;