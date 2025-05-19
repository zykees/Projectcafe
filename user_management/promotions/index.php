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

// ดึงข้อมูลโปรโมชั่นที่ active
$stmt = $pdo->query("
    SELECT p.*,
           COALESCE((
               SELECT COUNT(*)
               FROM bookings b
               WHERE b.promotion_id = p.id
               AND b.status != 'cancelled'
           ), 0) as current_bookings,
           (p.max_bookings - COALESCE((
               SELECT COUNT(*)
               FROM bookings b
               WHERE b.promotion_id = p.id
               AND b.status != 'cancelled'
           ), 0)) as available_slots
    FROM promotions p
    WHERE p.status = 'active' 
    AND CURDATE() BETWEEN p.start_date AND p.end_date
    ORDER BY p.created_at DESC
");
$promotions = $stmt->fetchAll();

// Debug information
if (isset($_SESSION['debug'])) {
    echo "<!-- Debug: Found " . count($promotions) . " promotions -->";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>โปรโมชั่นทั้งหมด</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .promotion-card {
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .promotion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .promotion-image {
            height: 200px;
            object-fit: cover;
        }
        .badge-corner {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 8px 12px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="../index.php">
                    <i class="fas fa-home"></i> หน้าหลัก
                </a>
                <div class="d-flex">
                    <a href="my_bookings.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-list"></i> การจองของฉัน
                    </a>
                    <a href="../user/profile.php" class="btn btn-outline-secondary">
                        <i class="fas fa-user"></i> โปรไฟล์
                    </a>
                </div>
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

        <h2 class="mb-4"><i class="fas fa-tag"></i> โปรโมชั่นที่กำลังจัด</h2>

        <?php if (empty($promotions)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> ขณะนี้ไม่มีโปรโมชั่นที่กำลังจัด
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($promotions as $promo): ?>
                    <div class="col-md-4">
                        <div class="card promotion-card h-100">
                            <?php if ($promo['image_url']): ?>
                                <img src="../../<?= htmlspecialchars($promo['image_url']) ?>" 
                                     class="card-img-top promotion-image" 
                                     alt="<?= htmlspecialchars($promo['name']) ?>">
                            <?php endif; ?>
                            
                            <div class="badge-corner badge bg-<?= $promo['available_slots'] > 0 ? 'success' : 'danger' ?>">
                                <?php if ($promo['available_slots'] > 0): ?>
                                    <i class="fas fa-chair"></i> เหลือ <?= $promo['available_slots'] ?> ที่นั่ง
                                <?php else: ?>
                                    <i class="fas fa-exclamation-circle"></i> เต็มแล้ว
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($promo['name']) ?></h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars($promo['description'])) ?></p>
                                <ul class="list-unstyled mb-3">
                                    <li class="mb-2">
                                        <strong><i class="fas fa-tag"></i> ราคา:</strong> 
                                        <span class="text-primary">฿<?= number_format($promo['price'], 2) ?></span>
                                    </li>
                                    <li>
                                        <strong><i class="fas fa-calendar-alt"></i> ระยะเวลา:</strong><br>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($promo['start_date'])) ?> - 
                                            <?= date('d/m/Y', strtotime($promo['end_date'])) ?>
                                        </small>
                                    </li>
                                </ul>
                                <div class="d-grid">
                                    <a href="view.php?id=<?= $promo['id'] ?>" 
                                       class="btn btn-primary <?= $promo['available_slots'] <= 0 ? 'disabled' : '' ?>">
                                        <i class="fas fa-info-circle"></i> 
                                        <?= $promo['available_slots'] > 0 ? 'จองเลย' : 'เต็มแล้ว' ?>
                                    </a>
                                </div>
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