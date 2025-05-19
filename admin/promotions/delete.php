<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // ตรวจสอบการจองที่เกี่ยวข้อง
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as booking_count 
        FROM bookings 
        WHERE promotion_id = ? AND status != 'cancelled'
    ");
    $stmt->execute([$_GET['id']]);
    $result = $stmt->fetch();

    if ($result['booking_count'] > 0) {
        throw new Exception('ไม่สามารถลบโปรโมชั่นได้เนื่องจากมีการจองที่เกี่ยวข้อง');
    }

    // ดึงข้อมูลรูปภาพ
    $stmt = $pdo->prepare("SELECT image_url FROM promotions WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $promotion = $stmt->fetch();

    // ลบรูปภาพ
    if ($promotion && $promotion['image_url']) {
        $image_path = '../../' . $promotion['image_url'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // ลบข้อมูลโปรโมชั่น
    $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
    $stmt->execute([$_GET['id']]);

    $pdo->commit();
    $_SESSION['success'] = 'ลบโปรโมชั่นเรียบร้อยแล้ว';

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}

header('Location: index.php');
exit();