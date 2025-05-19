<?php
session_start();
require_once '../config/db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_email'])) {
    header('Location: ../auth/login.php');
    exit();
}

// ตรวจสอบและกำหนด user_id
if (!isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$_SESSION['user_email']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
    }
}

// ตรวจสอบ parameter id
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

// ดึงข้อมูลโปรโมชั่นพร้อมจำนวนการจอง
$stmt = $pdo->prepare("
    SELECT p.*,
           COUNT(DISTINCT CASE WHEN b.status != 'cancelled' THEN b.id END) as current_bookings,
           p.max_bookings - COUNT(DISTINCT CASE WHEN b.status != 'cancelled' THEN b.id END) as available_slots
    FROM promotions p
    LEFT JOIN bookings b ON p.id = b.promotion_id
    WHERE p.id = ?
    GROUP BY p.id
");

$stmt->execute([$_GET['id']]);
$promotion = $stmt->fetch();

if (!$promotion) {
    $_SESSION['error'] = 'ไม่พบโปรโมชั่นที่ต้องการ';
    header('Location: index.php');
    exit();
}

// ตรวจสอบการจองของผู้ใช้
$stmt = $pdo->prepare("
    SELECT * FROM bookings 
    WHERE user_id = ? AND promotion_id = ? 
    AND status != 'cancelled'
");
$stmt->execute([$_SESSION['user_id'], $promotion['id']]);
$existing_booking = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($promotion['name']) ?> - รายละเอียดโปรโมชั่น</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .promotion-image {
            max-height: 400px;
            width: 100%;
            object-fit: cover;
            border-radius: 8px;
        }
        .price-tag {
            font-size: 24px;
            color: #28a745;
            font-weight: bold;
        }
        .detail-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <nav class="navbar navbar-light bg-light mb-4 rounded">
            <div class="container-fluid">
                <a class="navbar-brand" href="../index.php">
                    <i class="fas fa-home"></i> หน้าหลัก
                </a>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> กลับไปหน้าโปรโมชั่น
                </a>
            </div>
        </nav>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <?php if ($promotion['image_url']): ?>
                            <img src="../../<?= htmlspecialchars($promotion['image_url']) ?>" 
                                 class="promotion-image mb-4" 
                                 alt="<?= htmlspecialchars($promotion['name']) ?>">
                        <?php endif; ?>

                        <h1 class="card-title mb-4"><?= htmlspecialchars($promotion['name']) ?></h1>
                        
                        <div class="detail-card">
                            <h5><i class="fas fa-info-circle"></i> รายละเอียดโปรโมชั่น</h5>
                            <p class="lead"><?= nl2br(htmlspecialchars($promotion['description'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-clipboard-list"></i> ข้อมูลการจอง
                        </h5>

                        <div class="price-tag mb-4">
                            ฿<?= number_format($promotion['price'], 2) ?>
                        </div>

                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-calendar-alt"></i>
                                <strong> ระยะเวลา:</strong><br>
                                <span class="text-muted">
                                    <?= date('d/m/Y', strtotime($promotion['start_date'])) ?> - 
                                    <?= date('d/m/Y', strtotime($promotion['end_date'])) ?>
                                </span>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-users"></i>
                                <strong> จำนวนที่นั่ง:</strong><br>
                                <span class="text-muted">
                                    จองแล้ว <?= $promotion['current_bookings'] ?> จาก <?= $promotion['max_bookings'] ?> ที่นั่ง
                                </span>
                            </li>
                            <li class="mb-4">
                                <i class="fas fa-info-circle"></i>
                                <strong> สถานะ:</strong><br>
                                <?php if ($promotion['available_slots'] > 0): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> เปิดรับจอง
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> เต็มแล้ว
                                    </span>
                                <?php endif; ?>
                            </li>
                        </ul>

                        <?php if ($existing_booking): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> คุณได้จองโปรโมชั่นนี้แล้ว
                            </div>
                        <?php elseif ($promotion['available_slots'] > 0): ?>
                            <a href="booking.php?id=<?= $promotion['id'] ?>" 
                               class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-calendar-check"></i> จองเลย
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-lg w-100" disabled>
                                <i class="fas fa-ban"></i> เต็มแล้ว
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>