<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'available'");
$stmt->execute([$_GET['id']]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit();
}

// Fetch categories for navigation
$cats = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL")->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="../index.php">
                    <i class="fas fa-home"></i> หน้าหลัก
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">สินค้าทั้งหมด</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">หมวดหมู่</a>
                            <ul class="dropdown-menu">
                                <?php foreach ($cats as $cat): ?>
                                    <li>
                                        <a class="dropdown-item" href="category.php?category=<?= urlencode($cat['category']) ?>">
                                            <?= htmlspecialchars($cat['category']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    </ul>
                    <form class="d-flex" action="search.php" method="GET">
                        <input class="form-control me-2" type="search" name="q" placeholder="ค้นหาสินค้า...">
                        <button class="btn btn-outline-success" type="submit">ค้นหา</button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Product Details -->
        <div class="row">
            <div class="col-md-6">
                <img src="../../uploads/products/<?= htmlspecialchars($product['image_url']) ?>" 
                     class="img-fluid rounded" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">สินค้า</a></li>
                        <li class="breadcrumb-item">
                            <a href="category.php?category=<?= urlencode($product['category']) ?>">
                                <?= htmlspecialchars($product['category']) ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
                    </ol>
                </nav>

                <h1 class="mb-4"><?= htmlspecialchars($product['name']) ?></h1>
                <p class="lead">฿<?= number_format($product['price'], 2) ?></p>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                
                <?php if ($product['stock'] > 0): ?>
                    <div class="mb-3">
                        <label class="form-label">จำนวน:</label>
                        <input type="number" id="quantity" class="form-control" 
                               min="1" max="<?= $product['stock'] ?>" value="1" style="width: 100px;">
                    </div>
                    <button onclick="addToCart(<?= $product['id'] ?>)" class="btn btn-primary btn-lg">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>สินค้าหมด</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function addToCart(productId) {
        const quantity = document.getElementById('quantity').value;
        fetch('../../cart/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('เพิ่มสินค้าลงตะกร้าแล้ว');
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการเพิ่มสินค้าลงตะกร้า');
        });
    }
    </script>
</body>
</html>