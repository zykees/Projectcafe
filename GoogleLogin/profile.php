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
    <a href="logout.php">Logout</a>
</body>
</html>