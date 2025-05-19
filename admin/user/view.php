<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$userId = $_GET['id'];

// ดึงข้อมูลผู้ใช้
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// ดึงประวัติการสั่งซื้อ
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

// ดึงประวัติการจอง
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อมูลผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>ข้อมูลผู้ใช้</h2>
        
        <!-- ข้อมูลส่วนตัว -->
        <div class="card mb-4">
            <div class="card-body">
                <h3>ข้อมูลส่วนตัว</h3>
                <div class="row">
                    <div class="col-md-2">
                        <?php if ($user['picture_url']): ?>
                            <img src="<?php echo htmlspecialchars($user['picture_url']); ?>" 
                                 class="img-fluid rounded">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-10">
                        <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                        <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>สมัครเมื่อ:</strong> <?php echo $user['created_at']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- แสดงประวัติการสั่งซื้อและการจอง -->
        <div class="row">
            <div class="col-md-6">
                <h3>ประวัติการสั่งซื้อ</h3>
                <!-- แสดงรายการสั่งซื้อ -->
            </div>
            <div class="col-md-6">
                <h3>ประวัติการจอง</h3>
                <!-- แสดงรายการจอง -->
            </div>
        </div>
    </div>
</body>
</html>