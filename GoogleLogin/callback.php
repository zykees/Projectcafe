<?php
require '../Api/vendor/autoload.php';
require 'config.php';

session_start();

$client = new Google\Client();
$client->setClientId('441539623798-k1kksnt8aj8e5gch9gjvm96soon7kj1a.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/Projectcafe/GoogleLogin/callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $oauth2 = new Google\Service\Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    // เก็บข้อมูลใน Session
    $_SESSION['user_email'] = $userInfo->email;
    $_SESSION['user_name'] = $userInfo->name;
    $_SESSION['user_picture'] = $userInfo->picture;

    // สร้าง remember token
    $remember_token = bin2hex(random_bytes(32));
    $expires = time() + (30 * 24 * 60 * 60); // 30 วัน

    try {
        // ตรวจสอบว่ามีผู้ใช้นี้ในฐานข้อมูลหรือไม่
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $userInfo->email]);
        $user = $stmt->fetch();

        if ($user) {
            // อัพเดท token สำหรับผู้ใช้ที่มีอยู่แล้ว
            $stmt = $pdo->prepare("UPDATE users SET 
                                remember_token = :token,
                                token_expires = :expires,
                                name = :name,
                                picture_url = :picture,
                                google_id = :google_id 
                                WHERE email = :email");
        } else {
            // สร้างผู้ใช้ใหม่พร้อม token
            $stmt = $pdo->prepare("INSERT INTO users 
                                (email, name, picture_url, remember_token, token_expires, google_id) 
                                VALUES 
                                (:email, :name, :picture, :token, :expires, :google_id)");
        }

        $stmt->execute([
            ':email' => $userInfo->email,
            ':name' => $userInfo->name,
            ':picture' => $userInfo->picture,
            ':token' => $remember_token,
            ':expires' => date('Y-m-d H:i:s', $expires),
            ':google_id' => $userInfo->id // เพิ่ม google_id
        ]);

        // สร้าง cookie
        setcookie('remember_token', $remember_token, $expires, '/');
        
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
    }

    header('Location: profile.php');
    exit();
}