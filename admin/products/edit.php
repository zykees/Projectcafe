<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลสินค้า
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$_GET['id']]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = 'ไม่พบสินค้าที่ต้องการแก้ไข';
    header('Location: index.php');
    exit();
}

// ดึงหมวดหมู่ที่มีอยู่
$categories = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $image_url = $product['image_url'];

        // จัดการอัพโหลดรูปภาพใหม่
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_path = '../../uploads/products/';
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_ext, $allowed_ext)) {
                throw new Exception('รองรับเฉพาะไฟล์รูปภาพ (jpg, jpeg, png, gif)');
            }

            $new_filename = uniqid() . '.' . $file_ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path . $new_filename)) {
                throw new Exception('ไม่สามารถอัพโหลดรูปภาพได้');
            }

            // ลบรูปเก่า
            if ($product['image_url'] && file_exists('../../' . $product['image_url'])) {
                unlink('../../' . $product['image_url']);
            }
            
            $image_url = 'uploads/products/' . $new_filename;
        }

        // อัพเดตข้อมูล
        $stmt = $pdo->prepare("
            UPDATE products SET 
                name = :name,
                description = :description,
                price = :price,
                stock = :stock,
                category = :category,
                image_url = :image_url,
                status = :status
            WHERE id = :id
        ");

        $stmt->execute([
            ':name' => $_POST['name'],
            ':description' => $_POST['description'],
            ':price' => $_POST['price'],
            ':stock' => $_POST['stock'],
            ':category' => $_POST['category'],
            ':image_url' => $image_url,
            ':status' => $_POST['status'],
            ':id' => $product['id']
        ]);

        $_SESSION['success'] = 'อัพเดตสินค้าเรียบร้อยแล้ว';
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>แก้ไขสินค้า</h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> กลับ
            </a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">ชื่อสินค้า</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?= htmlspecialchars($product['name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">รายละเอียด</label>
                                <textarea class="form-control" name="description" 
                                          rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">ราคา</label>
                                        <input type="number" class="form-control" name="price" 
                                               value="<?= $product['price'] ?>" 
                                               step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">จำนวนในสต็อก</label>
                                        <input type="number" class="form-control" name="stock" 
                                               value="<?= $product['stock'] ?>" 
                                               min="0" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">หมวดหมู่</label>
                                <select class="form-select" name="category">
                                    <option value="">เลือกหมวดหมู่</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['category']) ?>" 
                                                <?= $product['category'] === $cat['category'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['category']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="new">เพิ่มหมวดหมู่ใหม่</option>
                                </select>
                                <div id="newCategoryInput" style="display: none;" class="mt-2">
                                    <input type="text" class="form-control" 
                                           placeholder="ระบุหมวดหมู่ใหม่">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">รูปภาพปัจจุบัน</label>
                                <?php if ($product['image_url']): ?>
                                    <img src="../../<?= htmlspecialchars($product['image_url']) ?>" 
                                         class="img-thumbnail d-block mb-2" 
                                         style="max-height: 200px;">
                                <?php endif; ?>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <small class="text-muted">อัพโหลดรูปใหม่เพื่อเปลี่ยน</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">สถานะ</label>
                                <select class="form-select" name="status">
                                    <option value="available" <?= $product['status'] === 'available' ? 'selected' : '' ?>>
                                        วางขาย
                                    </option>
                                    <option value="unavailable" <?= $product['status'] === 'unavailable' ? 'selected' : '' ?>>
                                        ไม่วางขาย
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelector('select[name="category"]').addEventListener('change', function(e) {
        const newCategoryInput = document.getElementById('newCategoryInput');
        if (e.target.value === 'new') {
            newCategoryInput.style.display = 'block';
            const input = newCategoryInput.querySelector('input');
            input.name = 'category';
            e.target.name = '';
        } else {
            newCategoryInput.style.display = 'none';
            newCategoryInput.querySelector('input').name = '';
            e.target.name = 'category';
        }
    });
    </script>
</body>
</html>