<?php
session_start();
require '../GoogleLogin/config.php';

if (isset($_SESSION['user_email'])) {
    $stmt = $pdo->prepare("UPDATE users SET line_id = NULL WHERE email = :email");
    $stmt->execute([':email' => $_SESSION['user_email']]);
    unset($_SESSION['line_id']);
}

header('Location: ../GoogleLogin/profile.php');
exit;