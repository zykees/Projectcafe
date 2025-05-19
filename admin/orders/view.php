<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// ดึงข้อมูลคำสั่งซื้อและข้อมูลผู้ใช้
$stmt = $pdo->prepare("
    SELECT o.*, u.name as user_name, u.email,
           SUM(oi.quantity * oi.price) as total_amount
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id = ?
    GROUP BY o.id
");
$stmt->execute([':id' => $_GET['id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit();
}

// ดึงรายการสินค้าในคำสั่งซื้อ
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = :order_id
");
$stmt->execute([':order_id' => $order['id']]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดคำสั่งซื้อ #<?php echo $order['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark no-print">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">Admin Panel</a>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success no-print">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger no-print">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>รายละเอียดคำสั่งซื้อ #<?php echo $order['id']; ?></h2>
            <div class="no-print">
                <button onclick="printOrder()" class="btn btn-secondary">พิมพ์ใบสั่งซื้อ</button>
                <a href="index.php" class="btn btn-primary">กลับไปหน้ารายการ</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h4>ข้อมูลลูกค้า</h4>
                <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
                <p><strong>อีเมล:</strong> <?php echo htmlspecialchars($order['user_email']); ?></p>
                <p><strong>ที่อยู่จัดส่ง:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>
            <div class="col-md-6">
                <h4>ข้อมูลคำสั่งซื้อ</h4>
                <p><strong>วันที่สั่งซื้อ:</strong> <?php echo $order['created_at']; ?></p>
                <p><strong>สถานะ:</strong> 
                    <span class="badge bg-<?php 
                        echo $order['status'] === 'completed' ? 'success' : 
                            ($order['status'] === 'pending' ? 'warning' : 
                            ($order['status'] === 'cancelled' ? 'danger' : 'info')); 
                    ?>">
                        <?php echo $order['status']; ?>
                    </span>
                </p>
                <?php if ($order['payment_proof']): ?>
                    <p><strong>หลักฐานการชำระเงิน:</strong><br>
                        <img src="../../<?php echo htmlspecialchars($order['payment_proof']); ?>" 
                             alt="Payment proof" style="max-width: 300px;" class="img-thumbnail">
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <h4 class="mt-4">รายการสินค้า</h4>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>สินค้า</th>
                        <th>ราคาต่อชิ้น</th>
                        <th>จำนวน</th>
                        <th>ราคารวม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>ยอดรวมทั้งสิ้น:</strong></td>
                        <td><strong><?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 no-print">
            <h5>อัพเดทสถานะคำสั่งซื้อ</h5>
            <form action="update_status.php" method="POST" class="d-flex gap-2">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <select name="status" class="form-select" style="width: auto;">
                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                    <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>ชำระเงินแล้ว</option>
                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>จัดส่งแล้ว</option>
                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>เสร็จสมบูรณ์</option>
                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                </select>
                <button type="submit" class="btn btn-primary">อัพเดทสถานะ</button>
            </form>
        </div>
    </div>

    <script>
    function printOrder() {
        window.print();
    }
    </script>
</body>
</html>