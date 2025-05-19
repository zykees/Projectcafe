<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลสินค้าทั้งหมด
$stmt = $pdo->query("
    SELECT p.*, 
           COUNT(ci.id) as cart_count
    FROM products p
    LEFT JOIN cart_items ci ON p.id = ci.product_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();

// ดึงหมวดหมู่ทั้งหมด
$categories = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        .status-badge {
            font-size: 0.85em;
            padding: 5px 10px;
        }
    </style>
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
            <h2>จัดการสินค้า</h2>
            <a href="create.php" class="btn btn-success">
                <i class="fas fa-plus"></i> เพิ่มสินค้าใหม่
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">ข้อมูลสรุป</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center">
                            <h6>จำนวนสินค้าทั้งหมด</h6>
                            <h3><?= count($products) ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center">
                            <h6>สินค้าที่มีในสต็อก</h6>
                            <h3><?= count(array_filter($products, fn($p) => $p['stock'] > 0)) ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center">
                            <h6>หมวดหมู่</h6>
                            <h3><?= count($categories) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>รหัส</th>
                        <th>รูปภาพ</th>
                        <th>ชื่อสินค้า</th>
                        <th>หมวดหมู่</th>
                        <th>ราคา</th>
                        <th>สต็อก</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td>
                            <?php if ($product['image_url']): ?>
                                <img src="../../<?= htmlspecialchars($product['image_url']) ?>" 
                                     class="product-image"
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <img src="../../assets/images/no-image.png" 
                                     class="product-image"
                                     alt="No image">
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                            <br>
                            <small class="text-muted">
                                <?= mb_strimwidth(htmlspecialchars($product['description']), 0, 50, "...") ?>
                            </small>
                        </td>
                        <td><?= htmlspecialchars($product['category'] ?? 'ไม่ระบุ') ?></td>
                        <td>฿<?= number_format($product['price'], 2) ?></td>
                        <td>
                            <span class="badge bg-<?= $product['stock'] > 0 ? 'success' : 'danger' ?>">
                                <?= number_format($product['stock']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $product['status'] === 'available' ? 'success' : 'danger' ?> status-badge">
                                <?= $product['status'] === 'available' ? 'วางขาย' : 'ไม่วางขาย' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="edit.php?id=<?= $product['id'] ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteProduct(<?= $product['id'] ?>, <?= $product['cart_count'] ?>)" 
                                        class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
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
    function deleteProduct(id, cartCount) {
        if (cartCount > 0) {
            if (!confirm(`สินค้านี้มีอยู่ในตะกร้า ${cartCount} รายการ\nคุณแน่ใจหรือไม่ที่จะลบ?`)) {
                return;
            }
        } else if (!confirm('คุณแน่ใจหรือไม่ที่จะลบสินค้านี้?')) {
            return;
        }
        window.location.href = `delete.php?id=${id}`;
    }
    </script>
</body>
</html>