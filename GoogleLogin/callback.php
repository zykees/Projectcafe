<?php
require '../Api/vendor/autoload.php';

session_start();

$client = new Google\Client();
$client->setClientId('441539623798-k1kksnt8aj8e5gch9gjvm96soon7kj1a.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/Projectcafe/GoogleLogin/callback.php');
$client->addScope('email');
$client->addScope('profile');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $oauth2 = new Google\Service\Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    $_SESSION['user_email'] = $userInfo->email;
    $_SESSION['user_name'] = $userInfo->name;
    $_SESSION['user_picture'] = $userInfo->picture;

    header('Location: profile.php');
    exit();
} else {
    echo "Google authentication failed!";
}
