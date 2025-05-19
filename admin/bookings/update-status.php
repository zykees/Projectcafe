<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id']) || !isset($_GET['status'])) {
    header('Location: index.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // ตรวจสอบการจอง
    $stmt = $pdo->prepare("
        SELECT b.*, p.max_bookings, p.name as promotion_name 
        FROM bookings b
        LEFT JOIN promotions p ON b.promotion_id = p.id
        WHERE b.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new Exception('ไม่พบข้อมูลการจอง');
    }

    // อัพเดตสถานะ
    $stmt = $pdo->prepare("
        UPDATE bookings 
        SET status = ?,
            cancellation_reason = ?,
            updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $_GET['status'],
        $_GET['status'] === 'cancelled' ? ($_GET['reason'] ?? 'ยกเลิกโดยผู้ดูแลระบบ') : null,
        $_GET['id']
    ]);

    $pdo->commit();
    $_SESSION['success'] = 'อัพเดตสถานะการจองเรียบร้อยแล้ว';

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}

header('Location: index.php');
exit();