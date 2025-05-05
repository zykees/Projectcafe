<?php
require '../Api/vendor/autoload.php';

session_start();

$client = new Google\Client();
$client->setClientId('441539623798-k1kksnt8aj8e5gch9gjvm96soon7kj1a.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/Projectcafe/GoogleLogin/callback.php');
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
        <img src="https://developers.google.com/identity/images/btn_google_signin_light_normal_web.png" alt="Sign in with Google">
    </a>
</body>
</html>
