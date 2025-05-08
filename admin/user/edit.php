<?php
session_start();
require '../config/db.php'; // แก้ path ให้ถูกต้อง

// แก้เงื่อนไขการตรวจสอบ admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE users SET 
                          name = :name,
                          role = :role
                          WHERE id = :id");
    
    $stmt->execute([
        ':name' => $_POST['name'],
        ':role' => $_POST['role'],
        ':id' => $_POST['id']
    ]);

    header('Location: index.php');
    exit();
}

// ดึงข้อมูลผู้ใช้
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $_GET['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>แก้ไขข้อมูลผู้ใช้</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
            
            <div class="mb-3">
                <label class="form-label">ชื่อ</label>
                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">บันทึก</button>
            <a href="index.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
</body>
</html>