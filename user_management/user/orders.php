<?php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: ../auth/login.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR ', ') as items,
           SUM(p.price * oi.quantity) as total_amount
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>ประวัติการสั่งซื้อ</h2>
            <div>
                <a href="profile.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-user"></i> โปรไฟล์
                </a>
                <a href="../index.php" class="btn btn-outline-primary">
                    <i class="fas fa-home"></i> หน้าหลัก
                </a>
            </div>
        </div>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> ยังไม่มีประวัติการสั่งซื้อ
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>รหัสคำสั่งซื้อ</th>
                            <th>วันที่สั่งซื้อ</th>
                            <th>รายการสินค้า</th>
                            <th>ราคารวม</th>
                            <th>สถานะ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                <td><?= htmlspecialchars($order['items']) ?></td>
                                <td>฿<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $order['status'] === 'completed' ? 'success' : 
                                        ($order['status'] === 'pending' ? 'warning' : 
                                        ($order['status'] === 'cancelled' ? 'danger' : 'secondary')) 
                                    ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#orderDetail<?= $order['id'] ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Order Detail Modals -->
            <?php foreach ($orders as $order): ?>
                <div class="modal fade" id="orderDetail<?= $order['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">รายละเอียดคำสั่งซื้อ #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <strong>วันที่สั่งซื้อ:</strong>
                                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                </div>
                                <div class="mb-3">
                                    <strong>รายการสินค้า:</strong>
                                    <ul class="list-unstyled">
                                        <?php
                                        $items = explode(', ', $order['items']);
                                        foreach ($items as $item):
                                        ?>
                                            <li><i class="fas fa-check text-success me-2"></i><?= htmlspecialchars($item) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="mb-3">
                                    <strong>ราคารวม:</strong>
                                    ฿<?= number_format($order['total_amount'], 2) ?>
                                </div>
                                <div>
                                    <strong>สถานะ:</strong>
                                    <span class="badge bg-<?= 
                                        $order['status'] === 'completed' ? 'success' : 
                                        ($order['status'] === 'pending' ? 'warning' : 
                                        ($order['status'] === 'cancelled' ? 'danger' : 'secondary')) 
                                    ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>