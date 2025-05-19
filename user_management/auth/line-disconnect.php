<?php
session_start();
require '../config/db.php';

if (isset($_SESSION['user_email'])) {
    $stmt = $pdo->prepare("UPDATE users SET line_id = NULL WHERE email = :email");
    $stmt->execute([':email' => $_SESSION['user_email']]);
    unset($_SESSION['line_id']);
}

header('Location: ../user_management/user/profile.php');
exit;