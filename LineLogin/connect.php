<?php
session_start();
require 'config.php';

$state = uniqid('', true);
$_SESSION['line_state'] = $state;

$url = "https://access.line.me/oauth2/v2.1/authorize?";
$url .= "response_type=code";
$url .= "&client_id=" . '2007056322';
$url .= "&redirect_uri=" . urlencode('http://localhost/Projectcafe/LineLogin/callback.php');
$url .= "&state=" . $state;
$url .= "&scope=profile%20openid";

header("Location: " . $url);
exit;