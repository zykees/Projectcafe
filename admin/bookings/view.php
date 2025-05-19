<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header('Location: ../login.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT b.*, u.name as user_name, u.email as user_email,
           p.name as promotion_title, p.price
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN promotions p ON b.promotion_id = p.id
    WHERE b.id = ?
");
$stmt->execute([$_GET['id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดการจอง #<?= $booking['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>รายละเอียดการจอง #<?= $booking['id'] ?></h2>
        
        <div class="card mt-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>ข้อมูลผู้จอง</h5>
                        <p><strong>ชื่อ:</strong> <?= htmlspecialchars($booking['user_name']) ?></p>
                        <p><strong>อีเมล:</strong> <?= htmlspecialchars($booking['user_email']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>ข้อมูลการจอง</h5>
                        <p><strong>โปรโมชั่น:</strong> <?= htmlspecialchars($booking['promotion_title']) ?></p>
                        <p><strong>ราคา:</strong> ฿<?= number_format($booking['price'], 2) ?></p>
                        <p><strong>วันที่จอง:</strong> <?= date('d/m/Y H:i', strtotime($booking['booking_date'])) ?></p>
                        <p>
                            <strong>สถานะ:</strong> 
                            <span class="badge bg-<?= 
                                $booking['status'] === 'confirmed' ? 'success' : 
                                ($booking['status'] === 'pending' ? 'warning' : 'danger') 
                            ?>">
                                <?= $booking['status'] === 'confirmed' ? 'ยืนยันแล้ว' : 
                                    ($booking['status'] === 'pending' ? 'รอยืนยัน' : 'ยกเลิก') ?>
                            </span>
                        </p>
                        <?php if ($booking['status'] === 'cancelled' && $booking['cancellation_reason']): ?>
                            <div class="alert alert-danger">
                                <strong>เหตุผลที่ยกเลิก:</strong><br>
                                <?= nl2br(htmlspecialchars($booking['cancellation_reason'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($booking['status'] === 'pending'): ?>
                    <div class="mt-4">
                        <button onclick="updateStatus(<?= $booking['id'] ?>, 'confirmed')" 
                                class="btn btn-success">
                            <i class="fas fa-check"></i> ยืนยันการจอง
                        </button>
                        <button onclick="updateStatus(<?= $booking['id'] ?>, 'cancelled')" 
                                class="btn btn-danger">
                            <i class="fas fa-times"></i> ยกเลิกการจอง
                        </button>
                    </div>
                <?php endif; ?>

                <a href="index.php" class="btn btn-secondary mt-3">
                    <i class="fas fa-arrow-left"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function updateStatus(id, status) {
        let reason = '';
        if (status === 'cancelled') {
            reason = prompt('กรุณาระบุเหตุผลในการยกเลิก:');
            if (reason === null) return;
        }
        
        if (confirm('คุณแน่ใจหรือไม่ที่จะ' + (status === 'confirmed' ? 'ยืนยัน' : 'ยกเลิก') + 'การจองนี้?')) {
            window.location.href = `update-status.php?id=${id}&status=${status}&reason=${encodeURIComponent(reason)}`;
        }
    }
    </script>
</body>
</html>