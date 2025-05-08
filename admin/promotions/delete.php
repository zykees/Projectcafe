<?php
session_start();
require '../config/db.php';

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id'])) {
    // ลบการจองที่เกี่ยวข้องก่อน (ถ้ามี)
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE promotion_id = :id");
    $stmt->execute([':id' => $_GET['id']]);
    
    // จากนั้นลบโปรโมชั่น
    $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
}

header('Location: index.php');
exit();