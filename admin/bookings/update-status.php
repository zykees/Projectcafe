<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $allowed_statuses = ['confirmed', 'cancelled'];
    if (in_array($_GET['status'], $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $_GET['status'],
            ':id' => $_GET['id']
        ]);
    }
}

header('Location: index.php');
exit();