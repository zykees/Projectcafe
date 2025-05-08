<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลการจองทั้งหมด
$stmt = $pdo->query("
    SELECT b.*, u.name as user_name, p.title as promotion_title 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN promotions p ON b.promotion_id = p.id 
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการการจอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">Admin Panel</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>จัดการการจอง</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>รหัสการจอง</th>
                        <th>ผู้จอง</th>
                        <th>โปรโมชั่น</th>
                        <th>วันที่จอง</th>
                        <th>เวลา</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['id']); ?></td>
                        <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['promotion_title']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                        <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $booking['status'] === 'confirmed' ? 'success' : 
                                    ($booking['status'] === 'pending' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo $booking['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="view.php?id=<?php echo $booking['id']; ?>" 
                               class="btn btn-sm btn-info">ดูรายละเอียด</a>
                            <?php if ($booking['status'] === 'pending'): ?>
                                <button onclick="updateStatus(<?php echo $booking['id']; ?>, 'confirmed')" 
                                        class="btn btn-sm btn-success">ยืนยัน</button>
                                <button onclick="updateStatus(<?php echo $booking['id']; ?>, 'cancelled')" 
                                        class="btn btn-sm btn-danger">ยกเลิก</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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