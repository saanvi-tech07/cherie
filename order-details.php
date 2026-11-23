<?php
/**
 * CHÉRIE — Order Confirmation & Receipt Details
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

$orderNumber = trim($_GET['order_number'] ?? '');
$order = null;
$orderItems = [];

if ($orderNumber && Database::isConnected()) {
    $order = Database::fetchOne("SELECT * FROM orders WHERE order_number = ? LIMIT 1", [$orderNumber]);
    if ($order) {
        $orderItems = Database::fetchAll("
            SELECT oi.*, 
                   (SELECT image_path FROM product_images WHERE product_id = oi.product_id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
            FROM order_items oi
            WHERE oi.order_id = ?
        ", [$order['id']]);
    }
}

if (!$order) {
    setFlash('warning', 'Order not found.');
    header('Location: ' . BASE_URL . '/orders.php');
    exit;
}

$pageTitle = 'Order #' . $order['order_number'] . ' Confirmation';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 35px; margin-bottom: 80px;">
    
    <!-- Success Celebration Banner -->
    <div style="background:linear-gradient(135deg, var(--blush), var(--lavender)); border-radius:var(--radius-lg); padding:40px 30px; text-align:center; border:1px solid var(--border-color); margin-bottom:40px;">
        <div style="font-size:48px; margin-bottom:12px;">🕊️</div>
        <h1 style="font-size:32px; margin-bottom:8px;">Thank You for Your Order!</h1>
        <p style="font-size:15px; color:#5b474c;">
            Your parcel is being packaged with sweet rose petals and love. A confirmation has been sent to <strong><?= htmlspecialchars($order['customer_email']) ?></strong>.
        </p>
    </div>

    <!-- Invoice Card -->
    <div class="card-box" style="padding:40px;">
        
        <!-- Order Header -->
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:20px; border-bottom:1px solid var(--border-color); padding-bottom:24px; margin-bottom:30px;">
            <div>
                <span class="product-cat">ORDER RECEIPT</span>
                <h2 style="font-size:26px; margin:4px 0;">#<?= htmlspecialchars($order['order_number']) ?></h2>
                <div style="font-size:13px; color:#78666a;">
                    Placed on <?= date('d F Y \a\t h:i A', strtotime($order['created_at'])) ?>
                </div>
            </div>

            <div style="text-align:right;">
                <span style="display:inline-block; padding:6px 16px; border-radius:var(--radius-full); font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:1px; background:#EBF8EE; color:#1F7A38; margin-bottom:8px;">
                    ● <?= ucfirst($order['order_status']) ?>
                </span>
                <div style="font-size:13px; color:#78666a;">
                    Payment: <strong><?= strtoupper($order['payment_method']) ?></strong> (<?= ucfirst($order['payment_status']) ?>)
                </div>
            </div>
        </div>

        <!-- Shipping & Customer Info Grid -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px; margin-bottom:35px; background:var(--soft-cream); padding:20px; border-radius:var(--radius-sm); border:1px solid #f2e2e6;">
            <div>
                <h4 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:1px; color:var(--dusty-rose); margin-bottom:8px;">
                    Shipping Details
                </h4>
                <div style="font-size:14px; line-height:1.6; color:var(--chocolate);">
                    <strong><?= htmlspecialchars($order['customer_name']) ?></strong><br>
                    <?= nl2br(htmlspecialchars($order['shipping_address'])) ?><br>
                    <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['state']) ?> - <?= htmlspecialchars($order['postal_code']) ?><br>
                    India<br>
                    📞 <?= htmlspecialchars($order['customer_phone']) ?>
                </div>
            </div>

            <div>
                <h4 style="font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:1px; color:var(--dusty-rose); margin-bottom:8px;">
                    Order Information
                </h4>
                <div style="font-size:14px; line-height:1.6; color:var(--chocolate);">
                    <strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?><br>
                    <strong>Payment Method:</strong> <?= strtoupper($order['payment_method']) ?><br>
                    <?php if ($order['razorpay_payment_id']): ?>
                        <strong>Transaction ID:</strong> <?= htmlspecialchars($order['razorpay_payment_id']) ?><br>
                    <?php endif; ?>
                    <?php if ($order['notes']): ?>
                        <strong>Gift Note:</strong> &ldquo;<?= htmlspecialchars($order['notes']) ?>&rdquo;
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ordered Items Table -->
        <h3 style="font-size:18px; margin-bottom:16px;">Ordered Pieces</h3>
        <table class="cart-table" style="margin-bottom:30px;">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td>
                            <div class="cart-item-desc">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($item['image_path'] ?? 'assets/images/products/prod_1_1.svg') ?>" class="cart-item-img" style="width:50px; height:60px;">
                                <div>
                                    <div style="font-weight:600;"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <?php if ($item['variant_info']): ?>
                                        <div style="font-size:12px; color:#8c767b;"><?= htmlspecialchars($item['variant_info']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:13px; color:#8c767b;"><?= htmlspecialchars($item['product_sku']) ?></td>
                        <td style="font-size:14px;"><?= formatPrice($item['price']) ?></td>
                        <td style="font-size:14px;"><?= $item['quantity'] ?></td>
                        <td style="text-align:right; font-weight:600; font-size:14.5px;"><?= formatPrice($item['total_price']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals Calculation -->
        <div style="max-width:320px; margin-left:auto; font-size:14px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:#78666a;">Subtotal:</span>
                <span><?= formatPrice($order['subtotal']) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:#78666a;">Shipping:</span>
                <span><?= $order['shipping_fee'] == 0 ? '<span style="color:#1F7A38; font-weight:600;">FREE</span>' : formatPrice($order['shipping_fee']) ?></span>
            </div>
            <?php if ($order['discount_amount'] > 0): ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; color:#1F7A38;">
                    <span>Savings:</span>
                    <span>-<?= formatPrice($order['discount_amount']) ?></span>
                </div>
            <?php endif; ?>
            <hr style="border:none; border-top:1px solid #f2e2e6; margin:12px 0;">
            <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:600;">
                <span>Total Amount:</span>
                <span style="color:var(--chocolate);"><?= formatPrice($order['total_amount']) ?></span>
            </div>
        </div>

        <div style="margin-top:40px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <a href="<?= BASE_URL ?>/shop.php" class="btn btn-outline">&larr; Back to Shop</a>
            <button onclick="window.print()" class="btn btn-dark">Print Invoice Receipt 🖨️</button>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
