<?php
/**
 * CHÉRIE — Secure Checkout
 * Supports Cash on Delivery & Razorpay (Test Mode), DB Transactions, and Stock Validation.
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/xml_service.php';
require_once __DIR__ . '/includes/razorpay_service.php';
require_once __DIR__ . '/includes/mail_service.php';

$cartItems = getCartItems();
if (empty($cartItems)) {
    setFlash('warning', 'Your shopping bag is empty. Please add items before checking out.');
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

$totals = getCartTotals();
$discountAmount = 0.0;
$appliedCouponCode = null;

if (!empty($_SESSION['applied_coupon'])) {
    $couponCheck = XmlService::validateCoupon($_SESSION['applied_coupon']['code'], $totals['subtotal']);
    if ($couponCheck['valid']) {
        $discountAmount = $couponCheck['discount_amount'];
        $appliedCouponCode = $_SESSION['applied_coupon']['code'];
    }
}

$finalTotal = max(0, $totals['subtotal'] - $discountAmount + $totals['shipping']);

// Pre-fill user data if logged in
$user = null;
if (isLoggedIn() && Database::isConnected()) {
    $user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

$errors = [];

// Handle Order Placement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Session expired. Please try placing your order again.';
    }

    $customerName = sanitizeInput($_POST['customer_name'] ?? '');
    $customerEmail = filter_var(trim($_POST['customer_email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $customerPhone = sanitizeInput($_POST['customer_phone'] ?? '');
    $shippingAddress = sanitizeInput($_POST['shipping_address'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $state = sanitizeInput($_POST['state'] ?? '');
    $postalCode = sanitizeInput($_POST['postal_code'] ?? '');
    $paymentMethod = in_array($_POST['payment_method'] ?? '', ['cod', 'razorpay']) ? $_POST['payment_method'] : 'cod';
    $notes = sanitizeInput($_POST['order_notes'] ?? '');

    // Razorpay specific inputs if payment was completed via Razorpay
    $razorpayPaymentId = $_POST['razorpay_payment_id'] ?? null;
    $razorpayOrderId = $_POST['razorpay_order_id'] ?? null;
    $razorpaySignature = $_POST['razorpay_signature'] ?? null;

    if (!$customerName || !$customerEmail || !$customerPhone || !$shippingAddress || !$city || !$state || !$postalCode) {
        $errors[] = 'Please fill out all mandatory shipping details.';
    }

    // Verify Razorpay signature if method is razorpay
    if ($paymentMethod === 'razorpay') {
        if (!$razorpayPaymentId || !$razorpayOrderId || !$razorpaySignature) {
            $errors[] = 'Razorpay payment confirmation missing or interrupted. Please try again.';
        } elseif (!RazorpayService::verifySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)) {
            $errors[] = 'Security verification failed: Invalid Razorpay signature.';
        } elseif (RazorpayService::isPaymentAlreadyProcessed($razorpayPaymentId)) {
            $errors[] = 'Duplicate payment detected. This transaction has already been processed.';
        }
    }

    if (empty($errors)) {
        $pdo = Database::getConnection();
        if (!$pdo) {
            $errors[] = 'Database connection error. Please try again later.';
        } else {
            try {
                // Begin Database Transaction for Checkout Integrity
                $pdo->beginTransaction();

                // Re-verify Stock for all cart items
                foreach ($cartItems as $item) {
                    $prodStock = (int)$pdo->query("SELECT stock_quantity FROM products WHERE id = " . (int)$item['product_id'])->fetchColumn();
                    if ($item['quantity'] > $prodStock) {
                        throw new Exception("Product '" . $item['product_name'] . "' only has " . $prodStock . " units in stock.");
                    }
                }

                $orderNumber = generateOrderNumber();
                $userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
                $paymentStatus = ($paymentMethod === 'razorpay') ? 'completed' : 'pending';

                // Insert Order
                $orderStmt = $pdo->prepare("
                    INSERT INTO orders (
                        order_number, user_id, customer_name, customer_email, customer_phone,
                        shipping_address, city, state, postal_code, country,
                        payment_method, payment_status, order_status,
                        subtotal, shipping_fee, discount_amount, total_amount,
                        razorpay_order_id, razorpay_payment_id, notes
                    ) VALUES (
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, 'India',
                        ?, ?, 'processing',
                        ?, ?, ?, ?,
                        ?, ?, ?
                    )
                ");

                $orderStmt->execute([
                    $orderNumber, $userId, $customerName, $customerEmail, $customerPhone,
                    $shippingAddress, $city, $state, $postalCode,
                    $paymentMethod, $paymentStatus,
                    $totals['subtotal'], $totals['shipping'], $discountAmount, $finalTotal,
                    $razorpayOrderId, $razorpayPaymentId, $notes
                ]);

                $orderId = (int)$pdo->lastInsertId();

                // Insert Order Items & Deduct Stock
                $itemStmt = $pdo->prepare("
                    INSERT INTO order_items (
                        order_id, product_id, product_name, product_sku, variant_info, price, quantity, total_price
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $updateStockStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");

                foreach ($cartItems as $item) {
                    $variantStr = $item['variant_value'] ? ($item['variant_type'] . ': ' . $item['variant_value']) : null;
                    $itemTotal = $item['unit_price'] * $item['quantity'];

                    $itemStmt->execute([
                        $orderId, $item['product_id'], $item['product_name'], $item['slug'], $variantStr, $item['unit_price'], $item['quantity'], $itemTotal
                    ]);

                    // Deduct stock
                    $updateStockStmt->execute([$item['quantity'], $item['product_id']]);
                }

                // Insert Payment Record
                $payStmt = $pdo->prepare("
                    INSERT INTO payments (
                        order_id, payment_method, transaction_id, payment_gateway, amount, currency, status, gateway_response
                    ) VALUES (?, ?, ?, ?, ?, 'INR', ?, ?)
                ");
                $payStmt->execute([
                    $orderId, $paymentMethod, ($razorpayPaymentId ?: 'COD-' . $orderNumber),
                    ($paymentMethod === 'razorpay' ? 'razorpay' : 'cod'),
                    $finalTotal, $paymentStatus,
                    ($paymentMethod === 'razorpay' ? json_encode(['order_id' => $razorpayOrderId, 'payment_id' => $razorpayPaymentId]) : 'Cash on Delivery')
                ]);

                // Clear Cart
                $cartId = getOrCreateCartId();
                $pdo->exec("DELETE FROM cart_items WHERE cart_id = " . (int)$cartId);
                unset($_SESSION['applied_coupon']);

                // Commit Transaction
                $pdo->commit();

                // Fetch Order details for notification
                $orderRecord = Database::fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
                $orderItemsRecord = Database::fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$orderId]);

                // Send email notifications via PHPMailer transparent service
                MailService::sendOrderConfirmation($orderRecord, $orderItemsRecord);
                MailService::notifyAdminNewOrder($orderRecord);

                setFlash('success', 'Your order #' . $orderNumber . ' has been lovingly received!');
                header('Location: ' . BASE_URL . '/order-details.php?order_number=' . urlencode($orderNumber));
                exit;

            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Checkout failed: ' . $e->getMessage();
            }
        }
    }
}

// Prepare Razorpay Test Order if Razorpay is enabled
$razorpayOrder = null;
if (RAZORPAY_ENABLED) {
    $receiptId = 'rcpt_' . time();
    $razorpayOrder = RazorpayService::createOrder($finalTotal, $receiptId, ['app' => 'CHÉRIE']);
}

$pageTitle = 'Secure Checkout';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 35px; margin-bottom: 80px;">
    
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 32px;">Secure Checkout</h1>
        <p style="font-size: 14px; color: #766367;">Complete your boutique purchase with complete peace of mind.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom:24px;">
            <ul style="margin-left:20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" id="checkoutForm">
        <?= csrfField() ?>
        <input type="hidden" name="place_order" value="1">
        <input type="hidden" name="razorpay_payment_id" id="razorpayPaymentId" value="">
        <input type="hidden" name="razorpay_order_id" id="razorpayOrderId" value="<?= htmlspecialchars($razorpayOrder['order_id'] ?? '') ?>">
        <input type="hidden" name="razorpay_signature" id="razorpaySignature" value="">

        <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:40px; align-items:start;">
            
            <!-- Shipping Information & Payment Method -->
            <div>
                <!-- Shipping Form -->
                <div class="card-box">
                    <h3 style="font-size:18px; margin-bottom:18px; border-bottom:1px solid #f2e2e6; padding-bottom:10px;">
                        1. Delivery Address
                    </h3>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($_POST['customer_name'] ?? $user['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" name="customer_phone" class="form-control" placeholder="+91 98765 43210" value="<?= htmlspecialchars($_POST['customer_phone'] ?? $user['phone'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address (for order tracking) *</label>
                        <input type="email" name="customer_email" class="form-control" value="<?= htmlspecialchars($_POST['customer_email'] ?? $user['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Street Address & Apartment *</label>
                        <input type="text" name="shipping_address" class="form-control" placeholder="House/Flat No., Building, Street Name" value="<?= htmlspecialchars($_POST['shipping_address'] ?? $user['address'] ?? '') ?>" required>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($_POST['city'] ?? $user['city'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">State *</label>
                            <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($_POST['state'] ?? $user['state'] ?? 'Maharashtra') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">PIN Code *</label>
                            <input type="text" name="postal_code" class="form-control" placeholder="400050" value="<?= htmlspecialchars($_POST['postal_code'] ?? $user['postal_code'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Gift Message or Delivery Notes (Optional)</label>
                        <textarea name="order_notes" class="form-control" style="min-height:70px;" placeholder="Leave a handwritten note for the recipient or special courier instructions..."><?= htmlspecialchars($_POST['order_notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="card-box">
                    <h3 style="font-size:18px; margin-bottom:18px; border-bottom:1px solid #f2e2e6; padding-bottom:10px;">
                        2. Payment Option
                    </h3>

                    <!-- COD Option -->
                    <label style="display:block; background:#FFF9F5; padding:16px 20px; border-radius:var(--radius-sm); border:1px solid var(--border-color); margin-bottom:14px; cursor:pointer;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <input type="radio" name="payment_method" value="cod" id="payMethodCod" checked onchange="togglePaymentUi()">
                            <div>
                                <strong style="font-size:14.5px; color:var(--chocolate);">Cash on Delivery (COD)</strong>
                                <p style="font-size:12.5px; color:#7a686b; margin-top:2px;">Pay with cash or UPI at your doorstep upon arrival.</p>
                            </div>
                        </div>
                    </label>

                    <!-- Razorpay Option -->
                    <label style="display:block; background:#FFF9F5; padding:16px 20px; border-radius:var(--radius-sm); border:1px solid var(--border-color); cursor:pointer;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <input type="radio" name="payment_method" value="razorpay" id="payMethodRazorpay" onchange="togglePaymentUi()">
                            <div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <strong style="font-size:14.5px; color:var(--chocolate);">Razorpay (UPI, Cards, NetBanking)</strong>
                                    <span style="background:#EBF8EE; color:#1F7A38; font-size:10.5px; font-weight:600; padding:2px 6px; border-radius:4px;">TEST MODE</span>
                                </div>
                                <p style="font-size:12.5px; color:#7a686b; margin-top:2px;">Instant, automated verification via Razorpay Test Sandbox.</p>
                            </div>
                        </div>
                    </label>

                    <!-- Simulated test credentials alert -->
                    <div id="razorpayNotice" style="display:none; margin-top:16px; background:#FEF9E7; padding:12px 16px; border-radius:var(--radius-sm); font-size:12.5px; color:#B7950B; border:1px solid #F9E79F;">
                        ⚡ <strong>Razorpay Test Mode Active:</strong> Clicking "Place Order" will launch the secure test simulation. No actual money will be charged.
                    </div>
                </div>

            </div>

            <!-- Order Summary Column -->
            <div style="background:#FFF; padding:28px; border-radius:var(--radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-subtle);">
                <h3 style="font-size:20px; margin-bottom:18px; border-bottom:1px solid #f2e2e6; padding-bottom:12px;">Order Summary</h3>

                <!-- Items mini preview -->
                <div style="max-height:220px; overflow-y:auto; margin-bottom:20px; padding-right:5px;">
                    <?php foreach ($cartItems as $item): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; font-size:13.5px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($item['image_path'] ?? 'assets/images/products/prod_1_1.svg') ?>" style="width:42px; height:50px; border-radius:6px; object-fit:cover;">
                                <div>
                                    <div style="font-weight:600;"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <div style="font-size:11.5px; color:#8c767b;">Qty: <?= $item['quantity'] ?> <?= $item['variant_value'] ? '• ' . htmlspecialchars($item['variant_value']) : '' ?></div>
                                </div>
                            </div>
                            <span style="font-weight:600;"><?= formatPrice($item['unit_price'] * $item['quantity']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr style="border:none; border-top:1px solid #f2e2e6; margin:16px 0;">

                <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:14px;">
                    <span style="color:#6e5a5e;">Subtotal:</span>
                    <span style="font-weight:600;"><?= formatPrice($totals['subtotal']) ?></span>
                </div>

                <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:14px;">
                    <span style="color:#6e5a5e;">Boutique Shipping:</span>
                    <span style="font-weight:600;"><?= $totals['shipping'] == 0 ? '<span style="color:#1F7A38;">FREE</span>' : formatPrice($totals['shipping']) ?></span>
                </div>

                <?php if ($discountAmount > 0): ?>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:14px; color:#1F7A38;">
                        <span>Coupon Savings:</span>
                        <span style="font-weight:600;">-<?= formatPrice($discountAmount) ?></span>
                    </div>
                <?php endif; ?>

                <hr style="border:none; border-top:1px solid #f2e2e6; margin:16px 0;">

                <div style="display:flex; justify-content:space-between; margin-bottom:24px; font-size:18px; font-weight:600;">
                    <span>Grand Total:</span>
                    <span style="color:var(--chocolate);"><?= formatPrice($finalTotal) ?></span>
                </div>

                <button type="button" id="submitOrderBtn" onclick="handleOrderSubmit()" class="btn btn-primary btn-block" style="padding:15px; font-size:15px;">
                    Confirm & Place Order &rarr;
                </button>

                <div style="margin-top:16px; text-align:center; font-size:12px; color:#958386;">
                    🕊️ Carefully packed in sustainable silk paper and fragrant dried rose petals.
                </div>
            </div>

        </div>
    </form>

</div>

<script>
function togglePaymentUi() {
    const isRazorpay = document.getElementById('payMethodRazorpay').checked;
    document.getElementById('razorpayNotice').style.display = isRazorpay ? 'block' : 'none';
}

function handleOrderSubmit() {
    const form = document.getElementById('checkoutForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const isRazorpay = document.getElementById('payMethodRazorpay').checked;

    if (isRazorpay) {
        // Razorpay Test Mode Simulation or Standard Handler
        const simulatedPaymentId = 'pay_test_' + Math.random().toString(36).substring(2, 12);
        const orderId = document.getElementById('razorpayOrderId').value || 'order_test_simulated';
        
        // Provide visual simulation prompt
        const userConfirmed = confirm('Razorpay Test Mode:\n\nSimulating successful payment of <?= formatPrice($finalTotal) ?>?\n\nClick OK to simulate successful payment authorization, or Cancel to abort.');
        
        if (userConfirmed) {
            document.getElementById('razorpayPaymentId').value = simulatedPaymentId;
            document.getElementById('razorpaySignature').value = 'simulated_test_sig_' + simulatedPaymentId;
            form.submit();
        }
    } else {
        // COD submission
        form.submit();
    }
}
</script>

<style>
@media (max-width: 860px) {
    div[style*="grid-template-columns: 1.5fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
    div[style*="grid-template-columns: 1fr 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
