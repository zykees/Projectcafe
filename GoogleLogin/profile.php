<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <style>
        picture {
            display: block;
            width: 200px;
            height: 200px;
        }
        picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['user_name']; ?>!</h2>
    <picture>
        <source srcset="<?php echo str_replace(['.jpg', '.jpeg', '.png'], '.webp', $_SESSION['user_picture']); ?>" type="image/webp">
        <img src="<?php echo $_SESSION['user_picture']; ?>" alt="Profile Picture">
    </picture>
    <p>Email: <?php echo $_SESSION['user_email']; ?></p>
    <?php
    // ตรวจสอบว่าเชื่อมต่อ Line แล้วหรือยัง
    if (!isset($_SESSION['line_id'])) {
        echo '<a href="../LineLogin/connect.php" class="line-button">เชื่อมต่อบัญชี LINE</a>';
    } else {
        echo '<p>เชื่อมต่อกับ LINE แล้ว</p>';
        echo '<a href="../LineLogin/disconnect.php">ยกเลิกการเชื่อมต่อ LINE</a>';
    }
    ?>
    
    <a href="logout.php">Logout</a>

    <style>
        .line-button {
            display: inline-block;
            background: #00B900;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</body>
</html>