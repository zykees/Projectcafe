<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['user_id'] = $user['id']; // Add this line
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            if (!empty($user['picture_url'])) {
                $_SESSION['user_picture'] = $user['picture_url'];
            }
            
            header('Location: ../index.php');
            exit();
        } else {
            $_SESSION['error'] = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
            header('Location: login.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ';
        header('Location: login.php');
        exit();
    }
}