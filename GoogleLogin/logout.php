<?php
session_start();
require 'config.php';

// ลบ remember token จากฐานข้อมูล
if (isset($_SESSION['user_email'])) {
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL, token_expires = NULL WHERE email = :email");
    $stmt->execute([':email' => $_SESSION['user_email']]);
}

// ลบ cookie
setcookie('remember_token', '', time() - 3600, '/');

session_destroy();
header('Location: login.php');
exit();
