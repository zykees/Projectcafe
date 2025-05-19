<?php
// Start the PHP code block without closing it
$rootPath = str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2);
?>
<header class="header">
    <div class="container">
        <nav class="nav">
            <div class="logo">
                <a href="<?php echo $rootPath; ?>index.php">
                    <img src="<?php echo $rootPath; ?>assets/images/logo/logo.png" alt="Logo">
                </a>
            </div>
            <ul class="nav-links">
                <li><a href="<?php echo $rootPath; ?>products/">สินค้า</a></li>
                <li><a href="<?php echo $rootPath; ?>promotions/">โปรโมชั่น</a></li>
                <li><a href="<?php echo $rootPath; ?>user/profile.php">โปรไฟล์</a></li>
                <li><a href="<?php echo $rootPath; ?>cart/">ตะกร้า</a></li>
                <?php if (isset($_SESSION['user_email'])): ?>
                    <li>
                        <span class="user-name">
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </span>
                    </li>
                    <li>
                        <a href="<?php echo $rootPath; ?>auth/logout.php" class="btn btn-logout">
                            ออกจากระบบ
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?php echo $rootPath; ?>auth/login.php" class="btn btn-login">
                            เข้าสู่ระบบ
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </div>
</header>