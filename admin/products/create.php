<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_path = '../../uploads/products/';
    $image_url = '';
    
    // Create directories if they don't exist
    if (!file_exists('../../uploads')) {
        mkdir('../../uploads', 0777, true);
    }
    if (!file_exists($upload_path)) {
        mkdir($upload_path, 0777, true);
    }
    
    // จัดการอัพโหลดรูปภาพ
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        // Only allow certain file types
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_types)) {
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_file = $upload_path . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
                $image_url = 'uploads/products/' . $new_filename;
            } else {
                $_SESSION['error'] = 'Failed to upload image. Please check directory permissions.';
                $image_url = '';
            }
        } else {
            $_SESSION['error'] = 'Invalid file type. Allowed types: ' . implode(', ', $allowed_types);
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, image_url, status) 
                              VALUES (:name, :description, :price, :stock, :image_url, :status)");
        
        $stmt->execute([
            ':name' => $_POST['name'],
            ':description' => $_POST['description'],
            ':price' => $_POST['price'],
            ':stock' => $_POST['stock'],
            ':image_url' => $image_url,
            ':status' => $_POST['status']
        ]);

        $_SESSION['success'] = 'Product added successfully';
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มสินค้าใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>เพิ่มสินค้าใหม่</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- ...existing form code... -->
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">รายละเอียด</label>
                <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">ราคา</label>
                <input type="number" class="form-control" name="price" step="0.01" required>
            </div>
            <div class="mb-3">
                <label class="form-label">จำนวนในสต็อก</label>
                <input type="number" class="form-control" name="stock" required>
            </div>
            <div class="mb-3">
                <label class="form-label">รูปภาพ</label>
                <input type="file" class="form-control" name="image" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">สถานะ</label>
                <select class="form-select" name="status">
                    <option value="available">มีสินค้า</option>
                    <option value="out_of_stock">สินค้าหมด</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">บันทึก</button>
            <a href="index.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
</body>
</html>