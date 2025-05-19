<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // สร้างโฟลเดอร์ถ้ายังไม่มี
        $upload_path = '../../uploads/promotions/';
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        // จัดการอัพโหลดรูปภาพ
        $image_url = null;
        if (!empty($_FILES['image']['name'])) {
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_ext, $allowed_ext)) {
                throw new Exception('รองรับเฉพาะไฟล์รูปภาพ (jpg, jpeg, png, gif)');
            }

            $file_name = uniqid() . '.' . $file_ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path . $file_name)) {
                throw new Exception('ไม่สามารถอัพโหลดรูปภาพได้');
            }
            $image_url = 'uploads/promotions/' . $file_name;
        }

        // เพิ่มข้อมูลโปรโมชั่น
        $stmt = $pdo->prepare("
            INSERT INTO promotions (
                name, description, price,
                start_date, end_date, max_bookings,
                status, image_url, created_at
            ) VALUES (
                :name, :description, :price,
                :start_date, :end_date, :max_bookings,
                :status, :image_url, NOW()
            )
        ");

        $stmt->execute([
            ':name' => $_POST['name'],
            ':description' => $_POST['description'],
            ':price' => $_POST['price'],
            ':start_date' => $_POST['start_date'],
            ':end_date' => $_POST['end_date'],
            ':max_bookings' => $_POST['max_bookings'],
            ':status' => $_POST['status'],
            ':image_url' => $image_url
        ]);

        $_SESSION['success'] = 'เพิ่มโปรโมชั่นใหม่เรียบร้อยแล้ว';
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
    <title>เพิ่มโปรโมชั่นใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>เพิ่มโปรโมชั่นใหม่</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">ชื่อโปรโมชั่น</label>
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
                <label class="form-label">วันที่เริ่มต้น</label>
                <input type="date" class="form-control" name="start_date" required>
            </div>
            <div class="mb-3">
                <label class="form-label">วันที่สิ้นสุด</label>
                <input type="date" class="form-control" name="end_date" required>
            </div>
            <div class="mb-3">
                <label class="form-label">จำนวนที่รับได้</label>
                <input type="number" class="form-control" name="max_bookings" required>
            </div>
            <div class="mb-3">
                <label class="form-label">รูปภาพ</label>
                <input type="file" class="form-control" name="image" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">สถานะ</label>
                <select class="form-control" name="status">
                    <option value="active">เปิดใช้งาน</option>
                    <option value="inactive">ปิดใช้งาน</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> บันทึก
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> ยกเลิก
            </a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>