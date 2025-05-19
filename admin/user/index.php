<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$stmt = $pdo->query("
    SELECT u.*, 
           COUNT(DISTINCT o.id) as total_orders,
           COUNT(DISTINCT b.id) as total_bookings
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    LEFT JOIN bookings b ON u.id = b.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">Admin Panel</a>
        </div>
    </nav>
    <div class="container mt-4">
        <h2>จัดการผู้ใช้</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>รูปโปรไฟล์</th>
                        <th>ชื่อ</th>
                        <th>อีเมล</th>
                        <th>การเชื่อมต่อ</th>
                        <th>คำสั่งซื้อ</th>
                        <th>การจอง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td>
                            <?php if ($user['picture_url']): ?>
                                <img src="<?php echo htmlspecialchars($user['picture_url']); ?>" 
                                     alt="Profile" style="width: 40px; height: 40px; border-radius: 50%;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if ($user['google_id']): ?>
                                <span class="badge bg-primary">Google</span>
                            <?php endif; ?>
                            <?php if ($user['line_id']): ?>
                                <span class="badge bg-success">Line</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $user['total_orders']; ?></td>
                        <td><?php echo $user['total_bookings']; ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $user['id']; ?>" 
                               class="btn btn-info btn-sm">ดูรายละเอียด</a>
                            <a href="edit.php?id=<?php echo $user['id']; ?>" 
                               class="btn btn-primary btn-sm">แก้ไข</a>
                            <button onclick="deleteUser(<?php echo $user['id']; ?>)" 
                                    class="btn btn-danger btn-sm">ลบ</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>