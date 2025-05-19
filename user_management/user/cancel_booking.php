<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'cancelled' 
            WHERE id = ? AND user_id = ? AND status = 'pending'
        ");
        
        if ($stmt->execute([$_POST['booking_id'], $_SESSION['user_id']])) {
            $_SESSION['success'] = 'ยกเลิกการจองเรียบร้อยแล้ว';
        } else {
            $_SESSION['error'] = 'ไม่สามารถยกเลิกการจองได้';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}

header('Location: bookings.php');
exit();