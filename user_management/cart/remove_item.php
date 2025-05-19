<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit();
}

if (!isset($_POST['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit();
}

$item_id = intval($_POST['item_id']);

try {
    $pdo->beginTransaction();

    // ตรวจสอบว่าเป็นสินค้าในตะกร้าของผู้ใช้จริง
    $stmt = $pdo->prepare("
        SELECT * FROM cart_items 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$item_id, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        throw new Exception('ไม่พบสินค้าในตะกร้า');
    }

    // ลบสินค้าออกจากตะกร้า
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
    $stmt->execute([$item_id]);

    // คำนวณราคารวมใหม่
    $stmt = $pdo->prepare("
        SELECT SUM(ci.quantity * p.price) as total
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_total = $stmt->fetch()['total'] ?? 0;

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'cart_total' => $cart_total
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}