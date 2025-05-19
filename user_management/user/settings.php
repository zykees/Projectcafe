<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: ../auth/login.php');
    exit();
}

// ดึงข้อมูลผู้ใช้
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$_SESSION['user_email']]);
$user = $stmt->fetch();

// จัดการการอัพเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updateFields = [];
        $params = [];

        // ตรวจสอบการเปลี่ยนแปลงชื่อ
        if (!empty($_POST['name']) && $_POST['name'] !== $user['name']) {
            $updateFields[] = "name = ?";
            $params[] = $_POST['name'];
        }

        // ตรวจสอบการเปลี่ยนแปลงเบอร์โทร
        if (isset($_POST['phone']) && $_POST['phone'] !== $user['phone']) {
            $updateFields[] = "phone = ?";
            $params[] = $_POST['phone'] ?: null;
        }

        // ตรวจสอบการเปลี่ยนรหัสผ่าน
        if (!empty($_POST['new_password'])) {
            if (empty($_POST['current_password'])) {
                throw new Exception('กรุณากรอกรหัสผ่านปัจจุบัน');
            }

            if (!password_verify($_POST['current_password'], $user['password'])) {
                throw new Exception('รหัสผ่านปัจจุบันไม่ถูกต้อง');
            }

            if (strlen($_POST['new_password']) < 6) {
                throw new Exception('รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร');
            }

            $updateFields[] = "password = ?";
            $params[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }

        if (!empty($updateFields)) {
            $params[] = $user['id'];
            $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $_SESSION['success'] = 'อัพเดตข้อมูลเรียบร้อยแล้ว';
            header('Location: settings.php');
            exit();
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตั้งค่าบัญชี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>ตั้งค่าบัญชี</h2>
            <div>
                <a href="profile.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-user"></i> โปรไฟล์
                </a>
                <a href="../index.php" class="btn btn-outline-primary">
                    <i class="fas fa-home"></i> หน้าหลัก
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> 
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">ข้อมูลทั่วไป</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">ชื่อ</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">อีเมล</label>
                                <input type="email" class="form-control" 
                                       value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                <small class="text-muted">ไม่สามารถเปลี่ยนแปลงอีเมลได้</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">เปลี่ยนรหัสผ่าน</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">รหัสผ่านปัจจุบัน</label>
                                <input type="password" class="form-control" name="current_password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">รหัสผ่านใหม่</label>
                                <input type="password" class="form-control" name="new_password" 
                                       minlength="6">
                                <small class="text-muted">รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร</small>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key"></i> เปลี่ยนรหัสผ่าน
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>