<?php
session_start();
require '../config/db.php'; // แก้ path ให้ถูกต้อง

// แก้เงื่อนไขการตรวจสอบ admin
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