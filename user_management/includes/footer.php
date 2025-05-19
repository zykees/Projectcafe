<?php
$jsPath = str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2);
?>
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>เกี่ยวกับเรา</h3>
                <p>ร้านกาแฟของเรามุ่งมั่นที่จะมอบประสบการณ์ที่ดีที่สุดให้กับลูกค้า</p>
            </div>
            <div class="footer-section">
                <h3>ติดต่อเรา</h3>
                <p>โทร: 02-xxx-xxxx</p>
                <p>อีเมล: contact@projectcafe.com</p>
            </div>
            <div class="footer-section">
                <h3>เวลาทำการ</h3>
                <p>จันทร์ - ศุกร์: 07:00 - 21:00</p>
                <p>เสาร์ - อาทิตย์: 08:00 - 22:00</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> ProjectCafe. สงวนลิขสิทธิ์.</p>
        </div>
    </div>
    <script src="<?php echo $jsPath; ?>assets/js/main.js"></script>
</footer>
</body>
</html>