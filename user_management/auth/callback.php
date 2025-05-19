<?php
session_start();
require '../../api/vendor/autoload.php';  // path ที่ถูกต้องไปยัง vendor ใน api folder
require '../config/db.php';

// Google Client configuration
$client = new Google\Client();
$client->setClientId('441539623798-k1kksnt8aj8e5gch9gjvm96soon7kj1a.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/Projectcafe/user_management/auth/callback.php');

try {
    // รับ code จาก Google
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        // Get user info
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google\Service\Oauth2($client);
        $userInfo = $google_oauth->userinfo->get();
        
        // สร้าง remember token
        $remember_token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 วัน

        // เช็คว่ามีผู้ใช้ในระบบแล้วหรือไม่
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $userInfo->email]);
        $user = $stmt->fetch();

        if ($user) {
            // อัพเดทข้อมูลผู้ใช้
            $stmt = $pdo->prepare("UPDATE users SET 
                                name = :name,
                                picture_url = :picture,
                                remember_token = :token,
                                token_expires = :expires,
                                google_id = :google_id 
                                WHERE email = :email");
        } else {
            // เพิ่มผู้ใช้ใหม่
            $stmt = $pdo->prepare("INSERT INTO users 
                                (email, name, picture_url, remember_token, token_expires, google_id) 
                                VALUES 
                                (:email, :name, :picture, :token, :expires, :google_id)");
        }

        // บันทึกข้อมูล
        $stmt->execute([
            ':email' => $userInfo->email,
            ':name' => $userInfo->name,
            ':picture' => $userInfo->picture,
            ':token' => $remember_token,
            ':expires' => date('Y-m-d H:i:s', $expires),
            ':google_id' => $userInfo->id
        ]);

        // เก็บข้อมูลใน Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $userInfo->email;
        $_SESSION['user_name'] = $userInfo->name;
        $_SESSION['user_picture'] = $userInfo->picture;

        // สร้าง cookie
        setcookie('remember_token', $remember_token, $expires, '/');

       // เปลี่ยนจาก profile.php เป็น index.php
        header('Location: ../index.php');
        exit();

    } else {
        // กรณีมี error
        header('Location: login.php?error=token_error');
        exit();
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    header('Location: login.php?error=auth_failed');
    exit();
}