<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: my_bookings.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // ตรวจสอบการจอง
    $stmt = $pdo->prepare("
        SELECT * FROM bookings 
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new Exception('ไม่พบข้อมูลการจองหรือไม่สามารถยกเลิกได้');
    }

    // อัพเดตสถานะการจอง
    $stmt = $pdo->prepare("
        UPDATE bookings 
        SET status = 'cancelled',
            cancellation_reason = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$_GET['reason'] ?? 'ผู้ใช้ยกเลิก', $_GET['id']]);

    $pdo->commit();
    $_SESSION['success'] = 'ยกเลิกการจองเรียบร้อยแล้ว';

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}

header('Location: my_bookings.php');
exit();