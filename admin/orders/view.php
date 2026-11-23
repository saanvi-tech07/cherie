<?php
/**
 * CHÉRIE — Admin Order Inspection & Status Update
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';

$orderId = (int)($_GET['id'] ?? 0);
$order = null;

if (Database::isConnected() && $orderId) {
    $order = Database::fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
}

if (!$order) {
    setFlash('danger', 'Order not found.');
    header('Location: ' . ADMIN_URL . '/orders/index.php');
    exit;
}

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $newStatus = $_POST['order_status'] ?? $order['order_status'];
        $newPaymentStatus = $_POST['payment_status'] ?? $order['payment_status'];
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $validPayStatuses = ['pending', 'completed', 'failed', 'refunded'];

        if (in_array($newStatus, $validStatuses, true) && in_array($newPaymentStatus, $validPayStatuses, true)) {
            Database::execute("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?", [
                $newStatus, $newPaymentStatus, $orderId
            ]);
            setFlash('success', 'Order status updated to ' . strtoupper($newStatus));
            header('Location: ' . ADMIN_URL . '/orders/view.php?id=' . $orderId);
            exit;
        }
    }
}

$adminTitle = 'Order #' . $order['order_number'];
require_once dirname(__DIR__) . '/includes/header.php';

$items = Database::fetchAll("
    SELECT oi.*, 
           (SELECT image_path FROM product_images WHERE product_id = oi.product_id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
    FROM order_items oi
    WHERE oi.order_id = ?
", [$orderId]);
?>

<div style="margin-bottom:24px;">
    <a href="<?= ADMIN_URL ?>/orders/index.php" style="font-size:13px; color:var(--admin-primary);">&larr; Back to Orders</a>
    <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px; margin-top:8px;">
        <div>
            <h1 style="font-size:24px;">Order #<?= htmlspecialchars($order['order_number']) ?></h1>
            <p style="font-size:13px; color:#78666a;">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
        </div>
        <button onclick="window.print()" class="btn btn-dark btn-sm">Print Invoice Receipt 🖨️</button>
    </div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:30px; align-items:start;">
    
    <!-- Order Line Items & Customer Details -->
    <div>
        <!-- Line Items -->
        <div class="admin-card">
            <h3 style="font-size:17px; margin-bottom:16px;">Ordered Items (<?= count($items) ?>)</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($item['image_path'] ?? 'assets/images/products/prod_1_1.svg') ?>" style="width:45px; height:55px; object-fit:cover; border-radius:6px; background:#f5e8ec;">
                                    <div>
                                        <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                        <div style="font-size:12px; color:#8c767b;">
                                            SKU: <?= htmlspecialchars($item['product_sku']) ?>
                                            <?= $item['variant_info'] ? ' &bull; ' . htmlspecialchars($item['variant_info']) : '' ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?= formatPrice($item['price']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td style="text-align:right; font-weight:600;"><?= formatPrice($item['total_price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Summary Totals -->
            <div style="max-width:280px; margin-left:auto; margin-top:20px; font-size:13.5px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span style="color:#78666a;">Subtotal:</span>
                    <span><?= formatPrice($order['subtotal']) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span style="color:#78666a;">Shipping Fee:</span>
                    <span><?= formatPrice($order['shipping_fee']) ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px; color:#1F7A38;">
                        <span>Discount:</span>
                        <span>-<?= formatPrice($order['discount_amount']) ?></span>
                    </div>
                <?php endif; ?>
                <hr style="border:none; border-top:1px solid #f2e2e6; margin:8px 0;">
                <div style="display:flex; justify-content:space-between; font-weight:600; font-size:16px;">
                    <span>Grand Total:</span>
                    <span><?= formatPrice($order['total_amount']) ?></span>
                </div>
            </div>
        </div>

        <!-- Customer & Delivery Information -->
        <div class="admin-card">
            <h3 style="font-size:17px; margin-bottom:14px;">Customer & Delivery Address</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; font-size:14px; line-height:1.6;">
                <div>
                    <strong style="color:var(--admin-chocolate);">Shipping Address:</strong><br>
                    <?= htmlspecialchars($order['customer_name']) ?><br>
                    <?= nl2br(htmlspecialchars($order['shipping_address'])) ?><br>
                    <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['state']) ?> - <?= htmlspecialchars($order['postal_code']) ?><br>
                    India
                </div>
                <div>
                    <strong style="color:var(--admin-chocolate);">Contact Information:</strong><br>
                    Email: <a href="mailto:<?= htmlspecialchars($order['customer_email']) ?>"><?= htmlspecialchars($order['customer_email']) ?></a><br>
                    Phone: <?= htmlspecialchars($order['customer_phone']) ?><br>
                    <?php if ($order['notes']): ?>
                        <div style="margin-top:8px; background:#FAF5F6; padding:8px 12px; border-radius:6px; font-size:12.5px;">
                            <strong>Client Note:</strong> <?= htmlspecialchars($order['notes']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Update Status & Payment Info -->
    <div>
        <!-- Order Status Updater -->
        <div class="admin-card">
            <h3 style="font-size:17px; margin-bottom:16px;">Fulfillment Status</h3>

            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="update_order_status" value="1">

                <div class="form-group">
                    <label class="form-label">Order Progress *</label>
                    <select name="order_status" class="form-control" style="font-weight:600;">
                        <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                        <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing & Packaging</option>
                        <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped / In Transit</option>
                        <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Status *</label>
                    <select name="payment_status" class="form-control">
                        <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="completed" <?= $order['payment_status'] === 'completed' ? 'selected' : '' ?>>Completed / Paid</option>
                        <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                        <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Update Order Status &rarr;
                </button>
            </form>
        </div>

        <!-- Payment Meta Card -->
        <div class="admin-card">
            <h3 style="font-size:17px; margin-bottom:14px;">Payment Audit</h3>
            <div style="font-size:13px; line-height:1.7;">
                <strong>Method:</strong> <?= strtoupper($order['payment_method']) ?><br>
                <strong>Status:</strong> <?= ucfirst($order['payment_status']) ?><br>
                <?php if ($order['razorpay_order_id']): ?>
                    <strong>Razorpay Order:</strong> <code><?= htmlspecialchars($order['razorpay_order_id']) ?></code><br>
                <?php endif; ?>
                <?php if ($order['razorpay_payment_id']): ?>
                    <strong>Payment ID:</strong> <code><?= htmlspecialchars($order['razorpay_payment_id']) ?></code><br>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
