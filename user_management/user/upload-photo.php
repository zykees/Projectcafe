<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    try {
        $file = $_FILES['photo'];
        
        // ตรวจสอบข้อผิดพลาดของไฟล์
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('เกิดข้อผิดพลาดในการอัพโหลดไฟล์');
        }

        // ตรวจสอบประเภทไฟล์
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('รองรับเฉพาะไฟล์ JPEG, PNG และ GIF เท่านั้น');
        }

        // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('ขนาดไฟล์ต้องไม่เกิน 5MB');
        }

        // สร้างชื่อไฟล์ใหม่
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $ext;
        
        // กำหนด path ที่จะบันทึกไฟล์
        $upload_path = '../../uploads/profiles/';
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        // ย้ายไฟล์ไปยังโฟลเดอร์ปลายทาง
        if (!move_uploaded_file($file['tmp_name'], $upload_path . $new_filename)) {
            throw new Exception('ไม่สามารถบันทึกไฟล์ได้');
        }

        // อัพเดตฐานข้อมูล
        $stmt = $pdo->prepare("
            UPDATE users 
            SET picture_url = ?, 
                updated_at = NOW() 
            WHERE email = ?
        ");

        // กำหนด path ที่จะเก็บในฐานข้อมูล
        $db_path = 'uploads/profiles/' . $new_filename;
        
        if (!$stmt->execute([$db_path, $_SESSION['user_email']])) {
            // ถ้าอัพเดตฐานข้อมูลไม่สำเร็จ ให้ลบรูปที่อัพโหลด
            unlink($upload_path . $new_filename);
            throw new Exception('ไม่สามารถอัพเดตข้อมูลได้');
        }

        // ลบรูปเก่า (ถ้ามี)
        $old_picture = $pdo->prepare("SELECT picture_url FROM users WHERE email = ?");
        $old_picture->execute([$_SESSION['user_email']]);
        $old_path = $old_picture->fetchColumn();
        
        if ($old_path && $old_path !== $db_path) {
            $full_old_path = '../../' . $old_path;
            if (file_exists($full_old_path)) {
                unlink($full_old_path);
            }
        }

        $_SESSION['success'] = 'อัพโหลดรูปโปรไฟล์เรียบร้อยแล้ว';

    } catch (Exception $e) {
        $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}

header('Location: profile.php');
exit();