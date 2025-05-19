<?php
// คำนวณ path สัมพัทธ์สำหรับ CSS
$cssPath = str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'ระบบจัดการผู้ใช้'; ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $cssPath; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $cssPath; ?>assets/css/responsive.css">
</head>
<body>
<?php include 'navbar.php'; ?>