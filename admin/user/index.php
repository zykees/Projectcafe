<?php
session_start();
require '../config/db.php'; // แก้ path ให้ถูกต้อง

// แก้เงื่อนไขการตรวจสอบ admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลผู้ใช้ทั้งหมด
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <th>ชื่อ</th>
                        <th>อีเมล</th>
                        <th>Line ID</th>
                        <th>สถานะ</th>
                        <th>วันที่สร้าง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo $user['line_id'] ? 'เชื่อมต่อแล้ว' : 'ยังไม่เชื่อมต่อ'; ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">แก้ไข</a>
                            <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-sm btn-danger">ลบ</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function deleteUser(id) {
        if (confirm('คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?')) {
            window.location.href = `delete.php?id=${id}`;
        }
    }
    </script>
</body>
</html>