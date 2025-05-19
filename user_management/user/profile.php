<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: ../auth/login.php');
    exit();
}

// ดึงข้อมูลผู้ใช้
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$_SESSION['user_email']]);
$user = $stmt->fetch();

// ดึงข้อมูลสรุป
$orderStmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
$orderStmt->execute([$user['id']]);
$orderCount = $orderStmt->fetch()['total_orders'];

$bookingStmt = $pdo->prepare("SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = ?");
$bookingStmt->execute([$user['id']]);
$bookingCount = $bookingStmt->fetch()['total_bookings'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>โปรไฟล์ผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .profile-picture img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
        }
        .stats-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .connection-item {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .connection-item img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
        .connection-status {
            margin-left: auto;
        }
        .connection-btn {
            padding: 5px 15px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>โปรไฟล์ของฉัน</h2>
            <a href="../index.php" class="btn btn-outline-primary">
                <i class="fas fa-home"></i> กลับหน้าหลัก
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="profile-picture mb-3">
                            <?php if ($user['picture_url']): ?>
                                <img src="../../<?= htmlspecialchars($user['picture_url']) ?>" 
                                    alt="Profile Picture" class="img-fluid">
                            <?php else: ?>
                                <img src="../assets/images/default-profile.png" 
                                    alt="Default Profile" class="img-fluid">
                            <?php endif; ?>
                        </div>
                        <h5 class="card-title"><?= htmlspecialchars($user['name']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                        
                        <form action="upload-photo.php" method="POST" enctype="multipart/form-data" class="mt-3">
                            <div class="input-group">
                                <input type="file" class="form-control" name="photo" accept="image/*" required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i>
                                </button>
                            </div>
                            <small class="text-muted">รองรับไฟล์ JPEG, PNG, GIF ขนาดไม่เกิน 5MB</small>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">การเชื่อมต่อบัญชี</h5>
                        
                        <!-- Google Connection -->
                        <div class="connection-item">
                            <img src="../assets/images/google-icon.png" alt="Google">
                            <div>
                                <strong>Google Account</strong>
                                <?php if (!empty($user['google_id'])): ?>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="connection-status">
                                <?php if (!empty($user['google_id'])): ?>
                                    <span class="badge bg-success">เชื่อมต่อแล้ว</span>
                                <?php else: ?>
                                    <a href="../auth/google-login.php" 
                                       class="btn btn-outline-danger connection-btn">
                                        เชื่อมต่อ
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- LINE Connection -->
                        <div class="connection-item">
                            <img src="../assets/images/line-icon.png" alt="LINE">
                            <div>
                                <strong>LINE Account</strong>
                                <?php if (!empty($user['line_id'])): ?>
                                    <br>
                                    <small class="text-muted">เชื่อมต่อแล้ว</small>
                                <?php endif; ?>
                            </div>
                            <div class="connection-status">
                                <?php if (!empty($user['line_id'])): ?>
                                    <a href="../auth/line-disconnect.php" 
                                       class="btn btn-outline-secondary connection-btn"
                                       onclick="return confirm('ยืนยันการยกเลิกการเชื่อมต่อ LINE?')">
                                        ยกเลิกการเชื่อมต่อ
                                    </a>
                                <?php else: ?>
                                    <a href="../auth/line-login.php" 
                                       class="btn btn-success connection-btn">
                                        เชื่อมต่อ
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="list-group mt-4">
                    <a href="profile.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-user"></i> โปรไฟล์
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-bag"></i> ประวัติการสั่งซื้อ 
                        <span class="badge bg-primary rounded-pill"><?= $orderCount ?></span>
                    </a>
                    <a href="bookings.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-check"></i> ประวัติการจอง
                        <span class="badge bg-primary rounded-pill"><?= $bookingCount ?></span>
                    </a>
                    <a href="settings.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog"></i> ตั้งค่าบัญชี
                    </a>
                    <a href="../auth/logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                    </a>
                </div>
            </div>

            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="stats-card">
                            <h3 class="display-4"><?= $orderCount ?></h3>
                            <p class="text-muted mb-0">การสั่งซื้อทั้งหมด</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stats-card">
                            <h3 class="display-4"><?= $bookingCount ?></h3>
                            <p class="text-muted mb-0">การจองทั้งหมด</p>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">ข้อมูลส่วนตัว</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>ชื่อ:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= htmlspecialchars($user['name']) ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>อีเมล:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= htmlspecialchars($user['email']) ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>เบอร์โทรศัพท์:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= htmlspecialchars($user['phone'] ?? '-') ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>สมาชิกตั้งแต่:</strong>
                            </div>
                            <div class="col-md-8">
                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">การสั่งซื้อล่าสุด</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $recentOrders = $pdo->prepare("
                            SELECT o.*, GROUP_CONCAT(p.name) as products
                            FROM orders o
                            LEFT JOIN order_items oi ON o.id = oi.order_id
                            LEFT JOIN products p ON oi.product_id = p.id
                            WHERE o.user_id = ?
                            GROUP BY o.id
                            ORDER BY o.created_at DESC
                            LIMIT 3
                        ");
                        $recentOrders->execute([$user['id']]);
                        $orders = $recentOrders->fetchAll();
                        ?>

                        <?php if (empty($orders)): ?>
                            <p class="text-muted">ยังไม่มีประวัติการสั่งซื้อ</p>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <strong>คำสั่งซื้อ #<?= $order['id'] ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= 
                                            $order['status'] === 'completed' ? 'success' : 
                                            ($order['status'] === 'pending' ? 'warning' : 'secondary') 
                                        ?>">
                                            <?= $order['status'] ?>
                                        </span>
                                        <br>
                                        <strong>฿<?= number_format($order['total_amount'], 2) ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-end mt-3">
                                <a href="orders.php" class="btn btn-outline-primary btn-sm">
                                    ดูทั้งหมด
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>