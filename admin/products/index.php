<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลสินค้าทั้งหมด
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>
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
            <h2>จัดการสินค้า</h2>
            <a href="create.php" class="btn btn-success">เพิ่มสินค้าใหม่</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>รูปภาพ</th>
                        <th>ชื่อสินค้า</th>
                        <th>ราคา</th>
                        <th>สต็อก</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td>
                            <?php if ($product['image_url']): ?>
                                <img src="../../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="Product image" style="height: 50px;">
                            <?php else: ?>
                                <img src="../../assets/images/no-image.png" 
                                     alt="No image available" style="height: 50px;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $product['status'] === 'available' ? 'success' : 'danger'; 
                            ?>">
                                <?php echo $product['status'] === 'available' ? 'มีสินค้า' : 'สินค้าหมด'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-sm btn-primary">แก้ไข</a>
                            <button onclick="deleteProduct(<?php echo $product['id']; ?>)" 
                                    class="btn btn-sm btn-danger">ลบ</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function deleteProduct(id) {
        if (confirm('คุณแน่ใจหรือไม่ที่จะลบสินค้านี้?')) {
            window.location.href = `delete.php?id=${id}`;
        }
    }
    </script>
</body>
</html>