<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $allowed_statuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
    
    if (in_array($_POST['status'], $allowed_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $stmt->execute([
                ':status' => $_POST['status'],
                ':id' => $_POST['order_id']
            ]);

            // ถ้าสถานะเป็น cancelled ให้คืนสต็อกสินค้า
            if ($_POST['status'] === 'cancelled') {
                $stmt = $pdo->prepare("
                    SELECT oi.product_id, oi.quantity
                    FROM order_items oi
                    WHERE oi.order_id = :order_id
                ");
                $stmt->execute([':order_id' => $_POST['order_id']]);
                $items = $stmt->fetchAll();

                foreach ($items as $item) {
                    $stmt = $pdo->prepare("
                        UPDATE products 
                        SET stock = stock + :quantity 
                        WHERE id = :product_id
                    ");
                    $stmt->execute([
                        ':quantity' => $item['quantity'],
                        ':product_id' => $item['product_id']
                    ]);
                }
            }

            $_SESSION['success'] = 'อัพเดทสถานะสำเร็จ';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

header('Location: view.php?id=' . $_POST['order_id']);
exit();