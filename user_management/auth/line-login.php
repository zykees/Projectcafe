<?php
session_start();
require_once '../config/line-config.php';

$state = bin2hex(random_bytes(16));
$_SESSION['line_state'] = $state;

$params = [
    'response_type' => 'code',
    'client_id' => '2007056322',
    'redirect_uri' => 'http://localhost/Projectcafe/user_management/auth/line-callback.php',
    'state' => $state,
    'scope' => 'profile openid email'
];

$auth_url = 'https://access.line.me/oauth2/v2.1/authorize?' . http_build_query($params);
header('Location: ' . $auth_url);
exit;