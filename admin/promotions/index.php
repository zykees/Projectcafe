<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลโปรโมชั่นทั้งหมด
$stmt = $pdo->query("
    SELECT p.*, 
           COUNT(b.id) as booking_count
    FROM promotions p
    LEFT JOIN bookings b ON p.id = b.promotion_id AND b.status != 'cancelled'
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$promotions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการโปรโมชั่น</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">Admin Panel</a>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>จัดการโปรโมชั่น</h2>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> เพิ่มโปรโมชั่นใหม่
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>รูปภาพ</th>
                        <th>ชื่อ</th>
                        <th>ราคา</th>
                        <th>วันที่เริ่ม</th>
                        <th>วันที่สิ้นสุด</th>
                        <th>จำนวนที่รับ</th>
                        <th>จองแล้ว</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promotions as $promo): ?>
                    <tr>
                        <td><?= $promo['id'] ?></td>
                        <td>
                            <?php if ($promo['image_url']): ?>
                                <img src="../../<?= htmlspecialchars($promo['image_url']) ?>" 
                                     alt="Promotion image" 
                                     style="max-width: 50px; height: auto;">
                            <?php else: ?>
                                <span class="text-muted">ไม่มีรูป</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($promo['name']) ?></td>
                        <td>฿<?= number_format($promo['price'], 2) ?></td>
                        <td><?= date('d/m/Y', strtotime($promo['start_date'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($promo['end_date'])) ?></td>
                        <td><?= number_format($promo['max_bookings']) ?></td>
                        <td><?= number_format($promo['booking_count']) ?></td>
                        <td>
                            <span class="badge bg-<?= $promo['status'] === 'active' ? 'success' : 'danger' ?>">
                                <?= $promo['status'] === 'active' ? 'เปิดใช้งาน' : 'ปิดใช้งาน' ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit.php?id=<?= $promo['id'] ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deletePromotion(<?= $promo['id'] ?>)" 
                                    class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function deletePromotion(id) {
        if (confirm('คุณแน่ใจหรือไม่ที่จะลบโปรโมชั่นนี้?')) {
            window.location.href = `delete.php?id=${id}`;
        }
    }
    </script>
</body>
</html>