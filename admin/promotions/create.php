<?php
session_start();
require '../config/db.php'; // แก้ path ให้ถูกต้อง

// แก้เงื่อนไขการตรวจสอบ admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO promotions (title, description, price, start_date, end_date, max_bookings, status) 
                          VALUES (:title, :description, :price, :start_date, :end_date, :max_bookings, :status)");
    
    $stmt->execute([
        ':title' => $_POST['title'],
        ':description' => $_POST['description'],
        ':price' => $_POST['price'],
        ':start_date' => $_POST['start_date'],
        ':end_date' => $_POST['end_date'],
        ':max_bookings' => $_POST['max_bookings'],
        ':status' => $_POST['status']
    ]);

    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มโปรโมชั่นใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>เพิ่มโปรโมชั่นใหม่</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">ชื่อโปรโมชั่น</label>
                <input type="text" class="form-control" name="title" required>
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
                <label class="form-label">วันที่เริ่ม</label>
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
                <label class="form-label">สถานะ</label>
                <select class="form-select" name="status">
                    <option value="active">เปิดใช้งาน</option>
                    <option value="inactive">ปิดใช้งาน</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">บันทึก</button>
            <a href="index.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
</body>
</html>