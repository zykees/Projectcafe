<?php
session_start();
require 'includes/auth.php';
require 'config/db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_email'])) {
    header('Location: auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก - ระบบจัดการผู้ใช้</title>
    <!-- Google Fonts - Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">   
                <div class="logo">
                    <a href="index.php">
                        <img src="assets/images/logo/logo.png" alt="Logo">
                    </a>
                </div>
                <ul class="nav-links">
                    <li><a href="products/index.php">สินค้า</a></li>
                    <li><a href="promotions/index.php">โปรโมชั่น</a></li>
                    <li><a href="user/profile.php">โปรไฟล์</a></li>
                    <li><a href="cart/">ตะกร้า</a></li>
                     <li><a href="promotions/my_bookings.php">การจองของฉัน</a></li>
                    <li><a href="auth/logout.php" class="btn btn-logout">ออกจากระบบ</a></li>
                </ul>
                <div class="menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="welcome-section">
            <h1>ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <!-- เพิ่มเนื้อหาหน้าหลักตามต้องการ -->
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> ระบบจัดการผู้ใช้. All rights reserved.</p>
        </div>
    </footer>

    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>