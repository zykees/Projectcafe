<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลการจองทั้งหมด
$stmt = $pdo->query("
    SELECT b.*, 
           u.name as user_name,
           u.email as user_email,
           p.name as promotion_name,
           p.price as promotion_price
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN promotions p ON b.promotion_id = p.id
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการการจอง - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .booking-status {
            min-width: 100px;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-gear-fill"></i> Admin Panel
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><i class="bi bi-calendar-check"></i> จัดการการจอง</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive mt-3">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>รหัสการจอง</th>
                        <th>ผู้จอง</th>
                        <th>โปรโมชั่น</th>
                        <th>วันที่จอง</th>
                        <th>เวลา</th>
                        <th>จำนวนที่นั่ง</th>
                        <th>ราคา</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>#<?= str_pad($booking['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <?= htmlspecialchars($booking['user_name']) ?>
                                <div class="small text-muted"><?= htmlspecialchars($booking['user_email']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($booking['promotion_name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($booking['booking_date'])) ?></td>
                            <td><?= date('H:i', strtotime($booking['booking_time'])) ?></td>
                            <td><?= $booking['seats'] ?></td>
                            <td>฿<?= number_format($booking['total_price'], 2) ?></td>
                            <td>
                                <span class="badge booking-status bg-<?= 
                                    $booking['status'] === 'confirmed' ? 'success' : 
                                    ($booking['status'] === 'pending' ? 'warning' : 'danger') 
                                ?>">
                                    <?= $booking['status'] === 'confirmed' ? 'ยืนยันแล้ว' : 
                                        ($booking['status'] === 'pending' ? 'รอยืนยัน' : 'ยกเลิก') ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="view.php?id=<?= $booking['id'] ?>" 
                                       class="btn btn-sm btn-info" title="ดูรายละเอียด">
                                        <i class="bi bi-eye-fill text-white"></i>
                                    </a>
                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <button onclick="updateStatus(<?= $booking['id'] ?>, 'confirmed')" 
                                                class="btn btn-sm btn-success" title="ยืนยันการจอง">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button onclick="updateStatus(<?= $booking['id'] ?>, 'cancelled')" 
                                                class="btn btn-sm btn-danger" title="ยกเลิกการจอง">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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