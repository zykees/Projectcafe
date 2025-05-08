<?php
session_start();
require 'config/db.php';

// ตรวจสอบว่าเป็น admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Panel</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="user/index.php">จัดการผู้ใช้</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="promotions/index.php">จัดการโปรโมชั่น</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings/index.php">จัดการการจอง</a>
                    </li>
                    <!-- เพิ่มเมนูอื่นๆ ตามต้องการ -->
                </ul>
            </div>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>ยินดีต้อนรับ Admin</h2>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">จัดการผู้ใช้</h5>
                        <p class="card-text">จัดการข้อมูลผู้ใช้ทั้งหมดในระบบ</p>
                        <a href="user/index.php" class="btn btn-primary">ไปที่หน้าจัดการผู้ใช้</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>