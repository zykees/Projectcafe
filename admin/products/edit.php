<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลสินค้าที่ต้องการแก้ไข
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute([':id' => $_GET['id']]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_path = '../../uploads/products/';
    $image_url = $product['image_url'];
    
    // จัดการอัพโหลดรูปภาพใหม่
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_file = $upload_path . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
            // ลบรูปเก่าถ้ามี
            if ($product['image_url'] && file_exists('../../' . $product['image_url'])) {
                unlink('../../' . $product['image_url']);
            }
            $image_url = 'uploads/products/' . $new_filename;
        }
    }

    $stmt = $pdo->prepare("UPDATE products SET 
                          name = :name,
                          description = :description,
                          price = :price,
                          stock = :stock,
                          image_url = :image_url,
                          status = :status
                          WHERE id = :id");
    
    $stmt->execute([
        ':name' => $_POST['name'],
        ':description' => $_POST['description'],
        ':price' => $_POST['price'],
        ':stock' => $_POST['stock'],
        ':image_url' => $image_url,
        ':status' => $_POST['status'],
        ':id' => $_GET['id']
    ]);

    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>แก้ไขสินค้า</h2>
        <?php if ($product['image_url']): ?>
            <div class="mb-3">
                <img src="../../<?php echo htmlspecialchars($product['image_url']); ?>" 
                     alt="Current product image" style="max-height: 200px;">
            </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input type="text" class="form-control" name="name" 
                       value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">รายละเอียด</label>
                <textarea class="form-control" name="description" rows="3"><?php 
                    echo htmlspecialchars($product['description']); 
                ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">ราคา</label>
                <input type="number" class="form-control" name="price" step="0.01" 
                       value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">จำนวนในสต็อก</label>
                <input type="number" class="form-control" name="stock" 
                       value="<?php echo htmlspecialchars($product['stock']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">รูปภาพใหม่</label>
                <input type="file" class="form-control" name="image" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">สถานะ</label>
                <select class="form-select" name="status">
                    <option value="available" <?php echo $product['status'] === 'available' ? 'selected' : ''; ?>>
                        มีสินค้า
                    </option>
                    <option value="out_of_stock" <?php echo $product['status'] === 'out_of_stock' ? 'selected' : ''; ?>>
                        สินค้าหมด
                    </option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">บันทึก</button>
            <a href="index.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
</body>
</html>