<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['id'])) {
    try {
        $pdo->beginTransaction();

        // ตรวจสอบการใช้งานในตะกร้า
        $stmt = $pdo->prepare("SELECT COUNT(*) as cart_count FROM cart_items WHERE product_id = ?");
        $stmt->execute([$_GET['id']]);
        $cart_count = $stmt->fetch()['cart_count'];

        if ($cart_count > 0) {
            // ลบรายการในตะกร้า
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE product_id = ?");
            $stmt->execute([$_GET['id']]);
        }

        // ดึงข้อมูลสินค้าเพื่อลบรูปภาพ
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $product = $stmt->fetch();

        // ลบรูปภาพ
        if ($product['image_url'] && file_exists('../../' . $product['image_url'])) {
            unlink('../../' . $product['image_url']);
        }

        // ลบสินค้า
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_GET['id']]);

        $pdo->commit();
        $_SESSION['success'] = 'ลบสินค้าเรียบร้อยแล้ว';

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}

header('Location: index.php');
exit();