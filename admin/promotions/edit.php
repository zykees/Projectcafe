<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลโปรโมชั่น
$stmt = $pdo->prepare("SELECT * FROM promotions WHERE id = ?");
$stmt->execute([$_GET['id']]);
$promotion = $stmt->fetch();

if (!$promotion) {
    $_SESSION['error'] = 'ไม่พบโปรโมชั่นที่ต้องการแก้ไข';
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $image_url = $_POST['current_image'];

        // จัดการอัพโหลดรูปภาพใหม่
        if (!empty($_FILES['image']['name'])) {
            $upload_path = '../../uploads/promotions/';
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_ext, $allowed_ext)) {
                throw new Exception('รองรับเฉพาะไฟล์รูปภาพ (jpg, jpeg, png, gif)');
            }

            $file_name = uniqid() . '.' . $file_ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path . $file_name)) {
                throw new Exception('ไม่สามารถอัพโหลดรูปภาพได้');
            }

            // ลบรูปภาพเก่า
            if (!empty($_POST['current_image'])) {
                $old_file = '../../' . $_POST['current_image'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            $image_url = 'uploads/promotions/' . $file_name;
        }

        // ตรวจสอบการจองก่อนเปลี่ยนสถานะ
        if ($_POST['status'] === 'inactive') {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as pending_bookings 
                FROM bookings 
                WHERE promotion_id = ? AND status = 'pending'
            ");
            $stmt->execute([$_POST['id']]);
            if ($stmt->fetch()['pending_bookings'] > 0) {
                throw new Exception('ไม่สามารถปิดโปรโมชั่นได้เนื่องจากมีการจองที่รอดำเนินการอยู่');
            }
        }

        // อัปเดตข้อมูล
        $stmt = $pdo->prepare("
            UPDATE promotions SET
                name = :name,
                description = :description,
                price = :price,
                start_date = :start_date,
                end_date = :end_date,
                max_bookings = :max_bookings,
                status = :status,
                image_url = :image_url,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':name' => $_POST['name'],
            ':description' => $_POST['description'],
            ':price' => $_POST['price'],
            ':start_date' => $_POST['start_date'],
            ':end_date' => $_POST['end_date'],
            ':max_bookings' => $_POST['max_bookings'],
            ':status' => $_POST['status'],
            ':image_url' => $image_url,
            ':id' => $_POST['id']
        ]);

        $pdo->commit();
        $_SESSION['success'] = 'แก้ไขโปรโมชั่นเรียบร้อยแล้ว';
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขโปรโมชั่น</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>แก้ไขโปรโมชั่น</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $promotion['id'] ?>">
            <input type="hidden" name="current_image" value="<?= $promotion['image_url'] ?>">
            
            <div class="mb-3">
                <label class="form-label">ชื่อโปรโมชั่น</label>
                <input type="text" class="form-control" name="name" 
                       value="<?= htmlspecialchars($promotion['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">รายละเอียด</label>
                <textarea class="form-control" name="description" 
                          rows="3"><?= htmlspecialchars($promotion['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">ราคา</label>
                <input type="number" class="form-control" name="price" 
                       value="<?= $promotion['price'] ?>" step="0.01" required>
            </div>
            <div class="mb-3">
                <label class="form-label">วันที่เริ่มต้น</label>
                <input type="date" class="form-control" name="start_date" 
                       value="<?= $promotion['start_date'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">วันที่สิ้นสุด</label>
                <input type="date" class="form-control" name="end_date" 
                       value="<?= $promotion['end_date'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">จำนวนที่รับได้</label>
                <input type="number" class="form-control" name="max_bookings" 
                       value="<?= $promotion['max_bookings'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">รูปภาพปัจจุบัน</label>
                <?php if ($promotion['image_url']): ?>
                    <div>
                        <img src="../../<?= htmlspecialchars($promotion['image_url']) ?>" 
                             alt="Current promotion image" 
                             style="max-width: 200px; height: auto;">
                    </div>
                <?php else: ?>
                    <p class="text-muted">ไม่มีรูปภาพ</p>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">อัพโหลดรูปภาพใหม่</label>
                <input type="file" class="form-control" name="image" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">สถานะ</label>
                <select class="form-control" name="status">
                    <option value="active" <?= $promotion['status'] === 'active' ? 'selected' : '' ?>>
                        เปิดใช้งาน
                    </option>
                    <option value="inactive" <?= $promotion['status'] === 'inactive' ? 'selected' : '' ?>>
                        ปิดใช้งาน
                    </option>
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