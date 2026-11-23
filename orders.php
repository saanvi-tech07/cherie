<?php
/**
 * CHÉRIE — Customer Order History & Tracking
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

$orders = [];
$guestLookedUpOrder = null;

if (isLoggedIn() && Database::isConnected()) {
    $userId = (int)$_SESSION['user_id'];
    $orders = Database::fetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC", [$userId]);
}

// Guest lookup form handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['track_order'])) {
    $trackNumber = trim($_POST['order_number'] ?? '');
    $trackEmail = trim($_POST['customer_email'] ?? '');

    if ($trackNumber && $trackEmail && Database::isConnected()) {
        $found = Database::fetchOne("SELECT * FROM orders WHERE order_number = ? AND customer_email = ? LIMIT 1", [$trackNumber, $trackEmail]);
        if ($found) {
            header('Location: ' . BASE_URL . '/order-details.php?order_number=' . urlencode($found['order_number']));
            exit;
        } else {
            setFlash('danger', 'No order found matching the provided Order Number and Email.');
        }
    }
}

$pageTitle = 'My Orders & Parcel Tracking';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 35px; margin-bottom: 80px;">
    
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 32px;">My Orders</h1>
        <p style="font-size: 14px; color: #766367;">Track active dispatches and view past boutique invoices.</p>
    </div>

    <?php if (isLoggedIn()): ?>
        <?php if (!empty($orders)): ?>
            <div style="display:flex; flex-direction:column; gap:20px;">
                <?php foreach ($orders as $ord): ?>
                    <div class="card-box" style="margin-bottom:0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                        <div>
                            <span class="product-cat">#<?= htmlspecialchars($ord['order_number']) ?></span>
                            <h3 style="font-size:18px; margin:4px 0;">Order of <?= formatPrice($ord['total_amount']) ?></h3>
                            <div style="font-size:13px; color:#8c767b;">
                                Placed on <?= date('d M Y', strtotime($ord['created_at'])) ?> &bull; Payment: <?= strtoupper($ord['payment_method']) ?>
                            </div>
                        </div>

                        <div style="display:flex; align-items:center; gap:16px;">
                            <span style="display:inline-block; padding:6px 14px; border-radius:var(--radius-full); font-size:12px; font-weight:600; text-transform:uppercase; background:#EBF8EE; color:#1F7A38;">
                                ● <?= ucfirst($ord['order_status']) ?>
                            </span>
                            <a href="<?= BASE_URL ?>/order-details.php?order_number=<?= urlencode($ord['order_number']) ?>" class="btn btn-outline btn-sm">
                                View Details &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card-box" style="text-align:center; padding:50px 20px;">
                <p style="color:#78666a; margin-bottom:16px;">You haven't placed any orders yet.</p>
                <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary">Start Shopping &rarr;</a>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Guest Tracking Form + Login Prompt -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:40px; align-items:start;">
            
            <div class="card-box">
                <h3 style="font-size:20px; margin-bottom:14px;">Track Guest Order</h3>
                <p style="font-size:13.5px; color:#78666a; margin-bottom:20px;">
                    Enter your order number and the email address you used at checkout.
                </p>

                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="track_order" value="1">
                    <div class="form-group">
                        <label class="form-label">Order Number *</label>
                        <input type="text" name="order_number" class="form-control" placeholder="e.g. CHR-20261123-A8F2" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Billing Email Address *</label>
                        <input type="email" name="customer_email" class="form-control" placeholder="your.email@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Track Order &rarr;</button>
                </form>
            </div>

            <div class="card-box" style="text-align:center; padding:45px 30px;">
                <div style="font-size:36px; margin-bottom:12px;">✨</div>
                <h3 style="font-size:20px; margin-bottom:10px;">Have an Account?</h3>
                <p style="font-size:14px; color:#78666a; margin-bottom:24px;">
                    Sign in to your CHÉRIE account to automatically view your entire order history, saved addresses, and active wishlists.
                </p>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-dark btn-block" style="margin-bottom:10px;">Sign In to Account</a>
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline btn-block">Create New Account</a>
            </div>

        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
