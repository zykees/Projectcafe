<?php
require '../Api/vendor/autoload.php';
require 'config.php';

session_start();

// Initialize Google Client
$client = new Google\Client();
$client->setClientId('441539623798-k1kksnt8aj8e5gch9gjvm96soon7kj1a.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/Projectcafe/GoogleLogin/callback.php');
$client->addScope('email');
$client->addScope('profile');

if (isset($_GET['code'])) {
    // ...existing code...
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    // ...remaining code...
}
    $oauth2 = new Google\Service\Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    // เก็บข้อมูลใน Session
    $_SESSION['user_email'] = $userInfo->email;
    $_SESSION['user_name'] = $userInfo->name;
    $_SESSION['user_picture'] = $userInfo->picture;

    // บันทึกหรืออัพเดทข้อมูลในฐานข้อมูล
    $stmt = $pdo->prepare("INSERT INTO users (email, name, picture_url, google_id) 
                          VALUES (:email, :name, :picture, :google_id)
                          ON DUPLICATE KEY UPDATE 
                          name = :name, picture_url = :picture");
    
    $stmt->execute([
        ':email' => $userInfo->email,
        ':name' => $userInfo->name,
        ':picture' => $userInfo->picture,
        ':google_id' => $userInfo->id
    ]);

    header('Location: profile.php');
    exit();