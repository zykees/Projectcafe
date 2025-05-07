<?php
session_start();
require '../../GoogleLogin/config.php';

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}


if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
}

header('Location: index.php');
exit();