<?php
require '../../api/vendor/autoload.php';  // path ที่ถูกต้องไปยัง vendor ใน api folder
require '../includes/auth.php';
require '../config/db.php';

session_start();

// ถ้ามีการ login อยู่แล้วให้ไปที่หน้า profile
if (checkAuth($pdo)) {
    header('Location: ../index.php');
    exit();
}

// Google Client configuration
$client = new Google\Client();
$client->setClientId('441539623798-k1kksnt8aj8e5gch9gjvm96soon7kj1a.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/Projectcafe/user_management/auth/callback.php'); // แก้ path callback
$client->addScope('email');
$client->addScope('profile');

$login_url = $client->createAuthUrl();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login with Google</title>
</head>
<body>
    <h2>Sign in with Google</h2>
    <a href="<?php echo $login_url; ?>">
        <img src="https://developers.google.com/identity/images/btn_google_signin_light_normal_web.png" 
             alt="Sign in with Google">
    </a>
</body>
</html>