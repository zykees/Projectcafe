<?php
function checkAuth($pdo) {
    // ตรวจสอบ session ก่อน
    if (isset($_SESSION['user_email'])) {
        return true;
    }

    // ตรวจสอบ remember token
    if (isset($_COOKIE['remember_token'])) {
        $token = filter_var($_COOKIE['remember_token'], FILTER_SANITIZE_STRING);
        
        $stmt = $pdo->prepare("SELECT * FROM users 
                              WHERE remember_token = :token 
                              AND token_expires > NOW()
                              AND remember_token IS NOT NULL");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // อัพเดท session
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_picture'] = $user['picture_url'];
            if (!empty($user['line_id'])) {
                $_SESSION['line_id'] = $user['line_id'];
            }
            return true;
        } else {
            // ลบ cookie ถ้า token ไม่ถูกต้องหรือหมดอายุ
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }

    return false;
}