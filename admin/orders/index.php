<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลคำสั่งซื้อทั้งหมด
$stmt = $pdo->query("
    SELECT o.*, u.name as user_name
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการคำสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">Admin Panel</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>จัดการคำสั่งซื้อ</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>เลขที่คำสั่งซื้อ</th>
                        <th>ชื่อผู้สั่ง</th>
                        <th>ยอดรวม</th>
                        <th>สถานะ</th>
                        <th>วันที่สั่งซื้อ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                        <td><?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $order['status'] === 'completed' ? 'success' : 
                                    ($order['status'] === 'pending' ? 'warning' : 
                                    ($order['status'] === 'cancelled' ? 'danger' : 'info')); 
                            ?>">
                                <?php echo $order['status']; ?>
                            </span>
                        </td>
                        <td><?php echo $order['created_at']; ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $order['id']; ?>" 
                               class="btn btn-sm btn-info">ดูรายละเอียด</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>