<?php
session_start();
require '../config/db.php'; // แก้ path ให้ถูกต้อง

// แก้เงื่อนไขการตรวจสอบ admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}


// ดึงข้อมูลโปรโมชั่นทั้งหมด
$stmt = $pdo->query("SELECT * FROM promotions ORDER BY created_at DESC");
$promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการโปรโมชั่น</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">Admin Panel</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>จัดการโปรโมชั่น</h2>
            <a href="create.php" class="btn btn-success">เพิ่มโปรโมชั่นใหม่</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อโปรโมชั่น</th>
                        <th>ราคา</th>
                        <th>วันที่เริ่ม</th>
                        <th>วันที่สิ้นสุด</th>
                        <th>จำนวนที่รับได้</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promotions as $promo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($promo['id']); ?></td>
                        <td><?php echo htmlspecialchars($promo['title']); ?></td>
                        <td><?php echo number_format($promo['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($promo['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($promo['end_date']); ?></td>
                        <td><?php echo htmlspecialchars($promo['max_bookings']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $promo['status'] === 'active' ? 'success' : 'danger'; ?>">
                                <?php echo $promo['status'] === 'active' ? 'เปิดใช้งาน' : 'ปิดใช้งาน'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $promo['id']; ?>" class="btn btn-sm btn-primary">แก้ไข</a>
                            <button onclick="deletePromotion(<?php echo $promo['id']; ?>)" class="btn btn-sm btn-danger">ลบ</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function deletePromotion(id) {
        if (confirm('คุณแน่ใจหรือไม่ที่จะลบโปรโมชั่นนี้?')) {
            window.location.href = `delete.php?id=${id}`;
        }
    }
    </script>
</body>
</html>