<?php
session_start();
require 'config/db.php';

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
$todayBookings = $pdo->query("
    SELECT COUNT(*) as count, 
           SUM(total_price) as total 
    FROM bookings 
    WHERE DATE(created_at) = CURDATE()
")->fetch();

$todayOrders = $pdo->query("
    SELECT COUNT(*) as count, 
           SUM(total_amount) as total 
    FROM orders 
    WHERE DATE(created_at) = CURDATE()
")->fetch();

$activePromotions = $pdo->query("
    SELECT COUNT(*) as count 
    FROM promotions 
    WHERE status = 'active' 
    AND CURDATE() BETWEEN start_date AND end_date
")->fetch();
// ดึงข้อมูลสถิติเบื้องต้น
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-gear-fill"></i> Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="user/index.php"><i class="bi bi-people"></i> จัดการผู้ใช้</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products/index.php"><i class="bi bi-box"></i> จัดการสินค้า</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders/index.php"><i class="bi bi-cart"></i> จัดการคำสั่งซื้อ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="promotions/index.php"><i class="bi bi-tag"></i> จัดการโปรโมชั่น</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings/index.php"><i class="bi bi-calendar"></i> จัดการการจอง</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="bi bi-speedometer2"></i> แผงควบคุม</h2>

        <!-- สรุปข้อมูลเบื้องต้น -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">ผู้ใช้ทั้งหมด</h6>
                                <h2><?php echo number_format($totalUsers); ?></h2>
                            </div>
                            <i class="bi bi-people-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">คำสั่งซื้อทั้งหมด</h6>
                                <h2><?php echo number_format($totalOrders); ?></h2>
                            </div>
                            <i class="bi bi-cart-fill fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">สินค้าทั้งหมด</h6>
                                <h2><?php echo number_format($totalProducts); ?></h2>
                            </div>
                            <i class="bi bi-box-seam fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">การจองทั้งหมด</h6>
                                <h2><?php echo number_format($totalBookings); ?></h2>
                            </div>
                            <i class="bi bi-calendar-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- เมนูลัด -->
        <h3 class="mb-3">เมนูลัด</h3>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-people"></i> จัดการผู้ใช้</h5>
                        <p class="card-text">จัดการข้อมูลผู้ใช้ทั้งหมดในระบบ</p>
                        <a href="user/index.php" class="btn btn-primary">
                            <i class="bi bi-arrow-right"></i> ไปที่หน้าจัดการผู้ใช้
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-box"></i> จัดการสินค้า</h5>
                        <p class="card-text">เพิ่ม แก้ไข ลบสินค้าในระบบ</p>
                        <a href="products/index.php" class="btn btn-primary">
                            <i class="bi bi-arrow-right"></i> ไปที่หน้าจัดการสินค้า
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-tag"></i> จัดการโปรโมชั่น</h5>
                        <p class="card-text">จัดการโปรโมชั่นและการจอง</p>
                        <a href="promotions/index.php" class="btn btn-primary">
                            <i class="bi bi-arrow-right"></i> ไปที่หน้าจัดการโปรโมชั่น
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>