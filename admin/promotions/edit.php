<?php
session_start();
require '../config/db.php'; // แก้ path ให้ถูกต้อง

// แก้เงื่อนไขการตรวจสอบ admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE promotions SET 
                          title = :title,
                          description = :description,
                          price = :price,
                          start_date = :start_date,
                          end_date = :end_date,
                          max_bookings = :max_bookings,
                          status = :status
                          WHERE id = :id");
    
    $stmt->execute([
        ':title' => $_POST['title'],
        ':description' => $_POST['description'],
        ':price' => $_POST['price'],
        ':start_date' => $_POST['start_date'],
        ':end_date' => $_POST['end_date'],
        ':max_bookings' => $_POST['max_bookings'],
        ':status' => $_POST['status'],
        ':id' => $_POST['id']
    ]);

    header('Location: index.php');
    exit();
}

// ดึงข้อมูลโปรโมชั่น
$stmt = $pdo->prepare("SELECT * FROM promotions WHERE id = :id");
$stmt->execute([':id' => $_GET['id']]);
$promotion = $stmt->fetch();

if (!$promotion) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขโปรโมชั่น</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">Admin Panel</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>แก้ไขโปรโมชั่น</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $promotion['id']; ?>">
            
            <div class="mb-3">
                <label class="form-label">ชื่อโปรโมชั่น</label>
                <input type="text" class="form-control" name="title" 
                       value="<?php echo htmlspecialchars($promotion['title']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">รายละเอียด</label>
                <textarea class="form-control" name="description" rows="3"><?php 
                    echo htmlspecialchars($promotion['description']); 
                ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">ราคา</label>
                <input type="number" class="form-control" name="price" step="0.01" 
                       value="<?php echo htmlspecialchars($promotion['price']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">วันที่เริ่ม</label>
                <input type="date" class="form-control" name="start_date" 
                       value="<?php echo htmlspecialchars($promotion['start_date']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">วันที่สิ้นสุด</label>
                <input type="date" class="form-control" name="end_date" 
                       value="<?php echo htmlspecialchars($promotion['end_date']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">จำนวนที่รับได้</label>
                <input type="number" class="form-control" name="max_bookings" 
                       value="<?php echo htmlspecialchars($promotion['max_bookings']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">สถานะ</label>
                <select class="form-select" name="status">
                    <option value="active" <?php echo $promotion['status'] === 'active' ? 'selected' : ''; ?>>
                        เปิดใช้งาน
                    </option>
                    <option value="inactive" <?php echo $promotion['status'] === 'inactive' ? 'selected' : ''; ?>>
                        ปิดใช้งาน
                    </option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">บันทึก</button>
            <a href="index.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
</body>
</html>