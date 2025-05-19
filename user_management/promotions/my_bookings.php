<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// ดึงข้อมูลการจองของผู้ใช้
$stmt = $pdo->prepare("
    SELECT b.*, p.name, p.price, p.image_url 
    FROM bookings b
    JOIN promotions p ON b.promotion_id = p.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>การจองของฉัน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="../index.php">
                    <i class="fas fa-home"></i> หน้าหลัก
                </a>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="fas fa-calendar"></i> ดูโปรโมชั่นทั้งหมด
                </a>
            </div>
        </nav>

        <h2 class="mb-4">การจองของฉัน</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="alert alert-info">คุณยังไม่มีการจองโปรโมชั่น</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($bookings as $booking): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <?php if ($booking['image_url']): ?>
                                <img src="../../<?= htmlspecialchars($booking['image_url']) ?>" 
                                     class="card-img-top" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($booking['name']) ?></h5>
                                <ul class="list-unstyled">
                                    <li>ราคา: ฿<?= number_format($booking['price'], 2) ?></li>
                                    <li>วันที่จอง: <?= date('d/m/Y H:i', strtotime($booking['booking_date'])) ?></li>
                                    <li>สถานะ: 
                                        <span class="badge bg-<?php
                                            echo $booking['status'] === 'confirmed' ? 'success' : 
                                                ($booking['status'] === 'pending' ? 'warning' : 'danger');
                                        ?>">
                                            <?= $booking['status'] === 'confirmed' ? 'ยืนยันแล้ว' : 
                                                ($booking['status'] === 'pending' ? 'รอการยืนยัน' : 'ยกเลิกแล้ว') ?>
                                        </span>
                                    </li>
                                </ul>
                                
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <button onclick="cancelBooking(<?= $booking['id'] ?>)" 
                                            class="btn btn-danger">
                                        <i class="fas fa-times"></i> ยกเลิกการจอง
                                    </button>
                                <?php endif; ?>

                                <?php if ($booking['status'] === 'cancelled'): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <strong>เหตุผลที่ยกเลิก:</strong><br>
                                            <?= htmlspecialchars($booking['cancellation_reason'] ?? '-') ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function cancelBooking(bookingId) {
    const reason = prompt('กรุณาระบุเหตุผลในการยกเลิก:');
    if (reason !== null) {
        window.location.href = `cancel_booking.php?id=${bookingId}&reason=${encodeURIComponent(reason)}`;
    }
}
    </script>
</body>
</html>