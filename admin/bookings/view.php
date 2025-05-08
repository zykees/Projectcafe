<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลการจอง
$stmt = $pdo->prepare("
    SELECT b.*, u.name as user_name, u.email as user_email, 
           p.title as promotion_title, p.price
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN promotions p ON b.promotion_id = p.id 
    WHERE b.id = :id
");
$stmt->execute([':id' => $_GET['id']]);
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
    <title>รายละเอียดการจอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">Admin Panel</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>รายละเอียดการจอง #<?php echo $booking['id']; ?></h2>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">ข้อมูลการจอง</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ชื่อผู้จอง:</strong> <?php echo htmlspecialchars($booking['user_name']); ?></p>
                        <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($booking['user_email']); ?></p>
                        <p><strong>โปรโมชั่น:</strong> <?php echo htmlspecialchars($booking['promotion_title']); ?></p>
                        <p><strong>ราคา:</strong> <?php echo number_format($booking['price'], 2); ?> บาท</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>วันที่จอง:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
                        <p><strong>เวลา:</strong> <?php echo htmlspecialchars($booking['booking_time']); ?></p>
                        <p><strong>สถานะ:</strong> 
                            <span class="badge bg-<?php 
                                echo $booking['status'] === 'confirmed' ? 'success' : 
                                    ($booking['status'] === 'pending' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo $booking['status']; ?>
                            </span>
                        </p>
                        <p><strong>วันที่ทำรายการ:</strong> <?php echo $booking['created_at']; ?></p>
                    </div>
                </div>

                <?php if ($booking['status'] === 'pending'): ?>
                    <div class="mt-3">
                        <button onclick="updateStatus(<?php echo $booking['id']; ?>, 'confirmed')" 
                                class="btn btn-success">ยืนยันการจอง</button>
                        <button onclick="updateStatus(<?php echo $booking['id']; ?>, 'cancelled')" 
                                class="btn btn-danger">ยกเลิกการจอง</button>
                    </div>
                <?php endif; ?>

                <a href="index.php" class="btn btn-secondary mt-3">กลับไปหน้าจัดการการจอง</a>
            </div>
        </div>
    </div>

    <script>
    function updateStatus(id, status) {
        if (confirm('คุณแน่ใจหรือไม่ที่จะ' + (status === 'confirmed' ? 'ยืนยัน' : 'ยกเลิก') + 'การจองนี้?')) {
            window.location.href = `update-status.php?id=${id}&status=${status}`;
        }
    }
    </script>
</body>
</html>