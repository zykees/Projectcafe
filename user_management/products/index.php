<?php
session_start();
require_once '../config/db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_email'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Get filters
$category = isset($_GET['category']) ? $_GET['category'] : null;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Build query
$query = "SELECT * FROM products WHERE status = 'available'";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($category) {
    $query .= " AND category = :category";
    $params[':category'] = $category;
}

if ($min_price !== null) {
    $query .= " AND price >= :min_price";
    $params[':min_price'] = $min_price;
}

if ($max_price !== null) {
    $query .= " AND price <= :max_price";
    $params[':max_price'] = $max_price;
}

$query .= " ORDER BY created_at DESC";

// Execute query
$stmt = $pdo->prepare($query);

$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories
$cats = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL")->fetchAll();

// Get price range
$price_range = $pdo->query("
    SELECT MIN(price) as min_price, MAX(price) as max_price 
    FROM products WHERE status = 'available'
")->fetch();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สินค้าทั้งหมด</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .product-card {
            transition: transform 0.2s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .product-title {
            height: 48px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .product-description {
            height: 72px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .price-filter {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .filter-badge {
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 15px;
            margin: 5px;
            display: inline-block;
        }
    </style>
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
                            <a class="nav-link active" href="index.php">สินค้าทั้งหมด</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">หมวดหมู่</a>
                            <ul class="dropdown-menu">
                                <?php foreach ($cats as $cat): ?>
                                    <li>
                                        <a class="dropdown-item" href="?category=<?= urlencode($cat['category']) ?>">
                                            <?= htmlspecialchars($cat['category']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    </ul>
                    <form class="d-flex" method="GET">
                        <input class="form-control me-2" type="search" name="q" 
                               placeholder="ค้นหาสินค้า..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-outline-success" type="submit">ค้นหา</button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                <?php endif; ?>
                
                <?php if ($category): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                <?php endif; ?>

                <div class="col-md-6">
                    <label class="form-label">ช่วงราคา</label>
                    <div class="price-filter">
                        <input type="number" class="form-control" name="min_price" 
                               placeholder="ราคาต่ำสุด" value="<?= $min_price ?>"
                               min="<?= floor($price_range['min_price']) ?>" 
                               max="<?= ceil($price_range['max_price']) ?>">
                        <span>-</span>
                        <input type="number" class="form-control" name="max_price" 
                               placeholder="ราคาสูงสุด" value="<?= $max_price ?>"
                               min="<?= floor($price_range['min_price']) ?>" 
                               max="<?= ceil($price_range['max_price']) ?>">
                        <button type="submit" class="btn btn-primary">กรอง</button>
                    </div>
                </div>
            </form>

            <!-- Active Filters -->
            <?php if ($category || $min_price || $max_price || $search): ?>
                <div class="mt-3">
                    <div class="d-flex align-items-center flex-wrap">
                        <strong class="me-2">ตัวกรองที่ใช้:</strong>
                        <?php if ($category): ?>
                            <span class="filter-badge">
                                หมวดหมู่: <?= htmlspecialchars($category) ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['category' => null])) ?>" class="text-decoration-none text-dark">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($min_price || $max_price): ?>
                            <span class="filter-badge">
                                ราคา: <?= $min_price ? "฿$min_price" : "฿0" ?> - 
                                      <?= $max_price ? "฿$max_price" : "ไม่จำกัด" ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['min_price' => null, 'max_price' => null])) ?>" class="text-decoration-none text-dark">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($search): ?>
                            <span class="filter-badge">
                                ค้นหา: <?= htmlspecialchars($search) ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['q' => null])) ?>" class="text-decoration-none text-dark">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        <?php endif; ?>
                        
                        <a href="index.php" class="btn btn-outline-secondary btn-sm ms-2">ล้างตัวกรองทั้งหมด</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> ไม่พบสินค้าที่ต้องการ
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php foreach ($products as $product): ?>
                    <div class="col">
                        <div class="card h-100 product-card">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="../../<?= htmlspecialchars($product['image_url']) ?>" 
                                     class="card-img-top product-image"
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <img src="../../assets/images/no-image.png" 
                                     class="card-img-top product-image"
                                     alt="No image available">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="product-title">
                                    <a href="view.php?id=<?= $product['id'] ?>" 
                                       class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </a>
                                </h5>
                                <p class="product-description text-muted">
                                    <?= htmlspecialchars($product['description']) ?>
                                </p>
                                <div class="mt-auto">
                                    <p class="h5 mb-3 text-primary">
                                        ฿<?= number_format($product['price'], 2) ?>
                                    </p>
                                    <?php if ($product['stock'] > 0): ?>
                                        <button onclick="addToCart(<?= $product['id'] ?>)" 
                                                class="btn btn-primary w-100">
                                            <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="fas fa-times"></i> สินค้าหมด
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function addToCart(productId) {
        fetch('../cart/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=1`,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (confirm('เพิ่มสินค้าลงตะกร้าแล้ว ต้องการไปที่ตะกร้าสินค้าหรือไม่?')) {
                    window.location.href = '../cart/index.php';
                }
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