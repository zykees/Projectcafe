<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit();
}

if (!isset($_POST['item_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit();
}

$item_id = intval($_POST['item_id']);
$quantity = intval($_POST['quantity']);

try {
    $pdo->beginTransaction();

    // ตรวจสอบว่าเป็นสินค้าในตะกร้าของผู้ใช้จริง
    $stmt = $pdo->prepare("
        SELECT ci.*, p.stock, p.price 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.id = ? AND ci.user_id = ?
    ");
    $stmt->execute([$item_id, $_SESSION['user_id']]);
    $item = $stmt->fetch();

    if (!$item) {
        throw new Exception('ไม่พบสินค้าในตะกร้า');
    }

    // ตรวจสอบจำนวนสินค้าในสต็อก
    if ($quantity > $item['stock']) {
        throw new Exception('จำนวนสินค้าในสต็อกไม่เพียงพอ');
    }

    // อัพเดตจำนวนสินค้า
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $item_id]);

    // คำนวณราคาใหม่
    $item_total = $quantity * $item['price'];

    // คำนวณราคารวมทั้งตะกร้า
    $stmt = $pdo->prepare("
        SELECT SUM(ci.quantity * p.price) as total
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_total = $stmt->fetch()['total'];

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'item_total' => $item_total,
        'cart_total' => $cart_total
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'current_quantity' => $item['quantity'] ?? 1
    ]);
}