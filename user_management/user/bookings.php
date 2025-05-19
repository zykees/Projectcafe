<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: ../auth/login.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT b.*, 
           COALESCE(p.name, 'การจองทั่วไป') as promotion_name,
           COALESCE(p.price, b.total_price) as price,
           p.image_url
    FROM bookings b
    LEFT JOIN promotions p ON b.promotion_id = p.id
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
    <title>ประวัติการจอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .booking-card {
            transition: transform 0.2s;
        }
        .booking-card:hover {
            transform: translateY(-5px);
        }
        .promotion-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>ประวัติการจอง</h2>
            <div>
                <a href="profile.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-user"></i> โปรไฟล์
                </a>
                <a href="../index.php" class="btn btn-outline-primary">
                    <i class="fas fa-home"></i> หน้าหลัก
                </a>
            </div>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> ยังไม่มีประวัติการจอง
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($bookings as $booking): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card booking-card h-100">
                            <div class="card-body">
                                <div class="d-flex mb-3">
                                    <?php if ($booking['image_url']): ?>
                                        <img src="../../<?= htmlspecialchars($booking['image_url']) ?>" 
                                             class="promotion-image me-3"
                                             alt="<?= htmlspecialchars($booking['promotion_name']) ?>">
                                    <?php endif; ?>
                                    <div>
                                        <h5 class="card-title"><?= htmlspecialchars($booking['promotion_name']) ?></h5>
                                        <span class="badge bg-<?= 
                                            $booking['status'] === 'confirmed' ? 'success' : 
                                            ($booking['status'] === 'pending' ? 'warning' : 
                                            ($booking['status'] === 'cancelled' ? 'danger' : 'secondary')) 
                                        ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">วันที่จอง</small>
                                        <strong><?= date('d/m/Y', strtotime($booking['booking_date'])) ?></strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">เวลา</small>
                                        <strong><?= $booking['booking_time'] ?></strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">จำนวนที่นั่ง</small>
                                        <strong><?= $booking['seats'] ?> ที่นั่ง</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">ราคา</small>
                                        <strong>฿<?= number_format($booking['price'], 2) ?></strong>
                                    </div>
                                </div>

                                <?php if ($booking['status'] === 'pending'): ?>
                                    <div class="mt-3">
                                        <form method="POST" action="cancel_booking.php" 
                                              onsubmit="return confirm('ยืนยันการยกเลิกการจอง?');">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> ยกเลิกการจอง
                                            </button>
                                        </form>
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
</body>
</html>