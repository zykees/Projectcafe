<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

// ดึงข้อมูลโปรโมชั่น
$stmt = $pdo->prepare("
    SELECT p.*,
           COUNT(DISTINCT CASE WHEN b.status != 'cancelled' THEN b.id END) as current_bookings
    FROM promotions p
    LEFT JOIN bookings b ON p.id = b.promotion_id
    WHERE p.id = ? 
    AND p.status = 'active'
    AND CURDATE() BETWEEN p.start_date AND p.end_date
    GROUP BY p.id
");
$stmt->execute([$_GET['id']]);
$promotion = $stmt->fetch();

if (!$promotion) {
    $_SESSION['error'] = 'ไม่พบโปรโมชั่นหรือโปรโมชั่นหมดเวลาแล้ว';
    header('Location: index.php');
    exit();
}

$available_slots = $promotion['max_bookings'] - $promotion['current_bookings'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // ตรวจสอบที่นั่งว่างอีกครั้ง
        if ($available_slots <= 0) {
            throw new Exception('ขออภัย โปรโมชั่นนี้เต็มแล้ว');
        }

        // สร้างการจองใหม่
        $stmt = $pdo->prepare("
            INSERT INTO bookings (
                user_id, promotion_id, 
                booking_date, booking_time,
                seats, total_price,
                status, notes,
                created_at
            ) VALUES (
                :user_id, :promotion_id, 
                :booking_date, :booking_time,
                :seats, :total_price,
                'pending', :notes,
                NOW()
            )
        ");

        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':promotion_id' => $promotion['id'],
            ':booking_date' => $_POST['booking_date'],
            ':booking_time' => $_POST['booking_time'],
            ':seats' => $_POST['seats'],
            ':total_price' => $promotion['price'],
            ':notes' => $_POST['notes'] ?? null
        ]);

        $pdo->commit();
        $_SESSION['success'] = 'จองโปรโมชั่นเรียบร้อยแล้ว รอการยืนยันจากผู้ดูแลระบบ';
        header('Location: my_bookings.php');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จองโปรโมชั่น - <?= htmlspecialchars($promotion['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>จองโปรโมชั่น: <?= htmlspecialchars($promotion['name']) ?></h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">วันที่ต้องการจอง</label>
                    <input type="date" class="form-control" name="booking_date" 
                           min="<?= date('Y-m-d') ?>" 
                           max="<?= $promotion['end_date'] ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">เวลาที่ต้องการจอง</label>
                    <input type="time" class="form-control" name="booking_time" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">จำนวนที่นั่ง</label>
                    <input type="number" class="form-control" name="seats" 
                           min="1" max="<?= $available_slots ?>" required>
                    <small class="text-muted">เหลือที่นั่งว่าง <?= $available_slots ?> ที่นั่ง</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">หมายเหตุเพิ่มเติม</label>
                    <textarea class="form-control" name="notes" rows="3"></textarea>
                </div>

                <div class="alert alert-info">
                    <strong>ราคารวม:</strong> ฿<?= number_format($promotion['price'], 2) ?>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> ยืนยันการจอง
                    </button>
                    <a href="view.php?id=<?= $promotion['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> ยกเลิก
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>