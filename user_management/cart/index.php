<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: ../auth/login.php');
    exit();
}

// ดึงข้อมูลตะกร้าสินค้า
function getCartItems($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT ci.*, p.name, p.price, p.image_url, p.stock,
               (p.price * ci.quantity) as total_price 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.id 
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

$cart_items = getCartItems($pdo, $_SESSION['user_id']);

// คำนวณราคารวม
$total = array_sum(array_column($cart_items, 'total_price'));
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตะกร้าสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .quantity-input {
            width: 80px !important;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
        .loading {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>ตะกร้าสินค้า</h2>
            <a href="../products/index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> กลับไปหน้าสินค้า
            </a>
        </div>

        <div id="cartContent">
            <?php if (empty($cart_items)): ?>
                <div class="alert alert-info">
                    ไม่มีสินค้าในตะกร้า
                    <a href="../products/index.php" class="alert-link">เลือกซื้อสินค้า</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>สินค้า</th>
                                <th>ราคา/ชิ้น</th>
                                <th>จำนวน</th>
                                <th>ราคารวม</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr id="item-<?= $item['id'] ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../../<?= htmlspecialchars($item['image_url']) ?>" 
                                                 class="product-image"
                                                 alt="<?= htmlspecialchars($item['name']) ?>">
                                            <span class="ms-3"><?= htmlspecialchars($item['name']) ?></span>
                                        </div>
                                    </td>
                                    <td>฿<?= number_format($item['price'], 2) ?></td>
                                    <td>
                                        <input type="number" 
                                               class="form-control form-control-sm quantity-input" 
                                               value="<?= $item['quantity'] ?>"
                                               min="1"
                                               max="<?= $item['stock'] ?>"
                                               onchange="updateQuantity(<?= $item['id'] ?>, this)">
                                    </td>
                                    <td class="item-total">฿<?= number_format($item['total_price'], 2) ?></td>
                                    <td>
                                        <button onclick="removeItem(<?= $item['id'] ?>)" 
                                                class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>ราคารวมทั้งหมด:</strong></td>
                                <td id="cartTotal">฿<?= number_format($total, 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <a href="../products/index.php" class="btn btn-outline-secondary">
                        เลือกซื้อสินค้าเพิ่ม
                    </a>
                    <a href="checkout.php" class="btn btn-success">
                        <i class="fas fa-shopping-cart"></i> ดำเนินการสั่งซื้อ
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function updateQuantity(itemId, input) {
        const quantity = parseInt(input.value);
        const row = document.getElementById(`item-${itemId}`);
        
        // Validate input
        if (quantity < 1) {
            input.value = 1;
            return;
        }
        if (quantity > parseInt(input.max)) {
            input.value = input.max;
            return;
        }

        // Add loading effect
        row.classList.add('loading');

        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `item_id=${itemId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update item total
                row.querySelector('.item-total').textContent = '฿' + data.item_total.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                
                // Update cart total
                document.getElementById('cartTotal').textContent = '฿' + data.cart_total.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            } else {
                alert(data.message);
                input.value = data.current_quantity;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการอัพเดตจำนวนสินค้า');
        })
        .finally(() => {
            row.classList.remove('loading');
        });
    }

    function removeItem(itemId) {
        if (!confirm('คุณแน่ใจหรือไม่ที่จะลบสินค้านี้ออกจากตะกร้า?')) {
            return;
        }

        const row = document.getElementById(`item-${itemId}`);
        row.classList.add('loading');

        fetch('remove_item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `item_id=${itemId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.remove();
                document.getElementById('cartTotal').textContent = '฿' + data.cart_total.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                
                // If cart is empty, refresh the page
                if (data.cart_total === 0) {
                    location.reload();
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการลบสินค้า');
        })
        .finally(() => {
            row.classList.remove('loading');
        });
    }
    </script>
</body>
</html>