<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id'])) {
    // ดึงข้อมูลสินค้าเพื่อลบรูปภาพ
    $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
    $product = $stmt->fetch();

    // ลบรูปภาพถ้ามี
    if ($product['image_url'] && file_exists('../../' . $product['image_url'])) {
        unlink('../../' . $product['image_url']);
    }

    // ลบข้อมูลสินค้า
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
}

header('Location: index.php');
exit();