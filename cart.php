<?php
/**
 * CHÉRIE — Shopping Bag & Cart Management
 * Supports item updates, coupon calculation via SimpleXML, and free shipping threshold meter.
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/xml_service.php';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Verify CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Session token expired. Please try again.');
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $variantId = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;

        $res = addToCart($productId, $quantity, $variantId);
        setFlash($res['success'] ? 'success' : 'danger', $res['message']);
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    if ($action === 'update') {
        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        $res = updateCartQuantity($cartItemId, $quantity);
        setFlash($res['success'] ? 'success' : 'danger', $res['message']);
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    if ($action === 'remove') {
        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        $res = removeCartItem($cartItemId);
        setFlash('info', 'Item removed from your bag.');
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    if ($action === 'apply_coupon') {
        $couponCode = trim($_POST['coupon_code'] ?? '');
        $totals = getCartTotals();
        $couponResult = XmlService::validateCoupon($couponCode, $totals['subtotal']);

        if ($couponResult['valid']) {
            $_SESSION['applied_coupon'] = [
                'code' => $couponResult['code'],
                'discount' => $couponResult['discount_amount'],
                'title' => $couponResult['title']
            ];
            setFlash('success', $couponResult['message']);
        } else {
            unset($_SESSION['applied_coupon']);
            setFlash('danger', $couponResult['message']);
        }
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    if ($action === 'remove_coupon') {
        unset($_SESSION['applied_coupon']);
        setFlash('info', 'Coupon removed.');
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }
}

$cartItems = getCartItems();
$totals = getCartTotals();

// Apply session coupon if present
$discountAmount = 0.0;
if (!empty($_SESSION['applied_coupon'])) {
    // Re-verify against current subtotal
    $couponCheck = XmlService::validateCoupon($_SESSION['applied_coupon']['code'], $totals['subtotal']);
    if ($couponCheck['valid']) {
        $discountAmount = $couponCheck['discount_amount'];
    } else {
        unset($_SESSION['applied_coupon']);
    }
}

$finalTotal = max(0, $totals['subtotal'] - $discountAmount + $totals['shipping']);

$pageTitle = 'My Shopping Bag (' . count($cartItems) . ')';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 35px; margin-bottom: 80px;">
    
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 32px;">Shopping Bag</h1>
        <p style="font-size: 14px; color: #766367;">Review your chosen pieces before stepping into checkout.</p>
    </div>

    <?php if (!empty($cartItems)): ?>
        
        <!-- Free Shipping Meter -->
        <div style="background:#FFF; padding:18px 24px; border-radius:var(--radius-md); border:1px solid var(--border-color); margin-bottom:30px; box-shadow:var(--shadow-subtle);">
            <?php if ($totals['subtotal'] >= $totals['free_shipping_min']): ?>
                <div style="color:#1F7A38; font-weight:600; font-size:14px; display:flex; align-items:center; gap:8px;">
                    <span>✨</span>
                    <span>Congratulations! You have unlocked <strong>Free Express Delivery</strong> with rose ribbon wrap.</span>
                </div>
            <?php else: 
                $percent = min(100, round(($totals['subtotal'] / $totals['free_shipping_min']) * 100));
            ?>
                <div style="font-size:13.5px; margin-bottom:8px;">
                    Add <strong><?= formatPrice($totals['needed_for_free_shipping']) ?></strong> more to your bag to enjoy <strong>Free Express Shipping</strong>!
                </div>
                <div style="background:#f2e2e6; height:8px; border-radius:4px; overflow:hidden;">
                    <div style="background:var(--dusty-rose); height:100%; width:<?= $percent ?>%; transition:width 0.4s ease;"></div>
                </div>
            <?php endif; ?>
        </div>

        <div style="display:grid; grid-template-columns: 1.8fr 1fr; gap:40px; align-items:start;">
            
            <!-- Items Table -->
            <div style="background:#FFF; padding:24px; border-radius:var(--radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-subtle); overflow-x:auto;">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="cart-item-desc">
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($item['image_path'] ?? 'assets/images/products/prod_1_1.svg') ?>" class="cart-item-img" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                        <div>
                                            <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($item['product_slug']) ?>" style="font-weight:600; font-size:14.5px; color:var(--chocolate);">
                                                <?= htmlspecialchars($item['product_name']) ?>
                                            </a>
                                            <?php if ($item['variant_value']): ?>
                                                <div style="font-size:12.5px; color:#8c767b; margin-top:2px;">
                                                    <?= htmlspecialchars($item['variant_type']) ?>: <?= htmlspecialchars($item['variant_value']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td style="font-weight:500; font-size:14px;">
                                    <?= formatPrice($item['unit_price']) ?>
                                </td>

                                <td>
                                    <form method="POST" class="auto-submit" style="display:inline-block;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                        <div class="qty-control">
                                            <button type="button" class="qty-btn qty-minus">-</button>
                                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= max(1, $item['max_stock']) ?>" class="qty-input">
                                            <button type="button" class="qty-btn qty-plus">+</button>
                                        </div>
                                    </form>
                                </td>

                                <td style="font-weight:600; font-size:15px; color:var(--chocolate);">
                                    <?= formatPrice($item['unit_price'] * $item['quantity']) ?>
                                </td>

                                <td>
                                    <form method="POST">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                        <button type="submit" style="background:none; border:none; color:#a38f93; cursor:pointer; font-size:18px;" title="Remove Item">&times;</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="margin-top:20px; display:flex; justify-content:space-between; align-items:center;">
                    <a href="<?= BASE_URL ?>/shop.php" style="font-size:13.5px; color:var(--dusty-rose); font-weight:500;">&larr; Continue Exploring</a>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div style="background:#FFF; padding:28px; border-radius:var(--radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-subtle);">
                <h3 style="font-size:20px; margin-bottom:20px; border-bottom:1px solid #f2e2e6; padding-bottom:12px;">Order Summary</h3>

                <!-- Subtotal -->
                <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px;">
                    <span style="color:#6d595d;">Bag Subtotal:</span>
                    <span style="font-weight:600; color:var(--chocolate);"><?= formatPrice($totals['subtotal']) ?></span>
                </div>

                <!-- Shipping -->
                <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px;">
                    <span style="color:#6d595d;">Boutique Shipping:</span>
                    <span>
                        <?php if ($totals['shipping'] == 0): ?>
                            <span style="color:#1F7A38; font-weight:600;">FREE</span>
                        <?php else: ?>
                            <?= formatPrice($totals['shipping']) ?>
                        <?php endif; ?>
                    </span>
                </div>

                <!-- Discount from XML Coupon -->
                <?php if ($discountAmount > 0): ?>
                    <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px; color:#1F7A38;">
                        <span>Coupon (<?= htmlspecialchars($_SESSION['applied_coupon']['code']) ?>):</span>
                        <span style="font-weight:600;">-<?= formatPrice($discountAmount) ?></span>
                    </div>
                <?php endif; ?>

                <hr style="border:none; border-top:1px solid #f2e2e6; margin:16px 0;">

                <!-- Grand Total -->
                <div style="display:flex; justify-content:space-between; margin-bottom:24px; font-size:18px; font-weight:600;">
                    <span>Grand Total:</span>
                    <span style="color:var(--chocolate);"><?= formatPrice($finalTotal) ?></span>
                </div>

                <!-- Coupon Form (Validated with SimpleXML) -->
                <form method="POST" style="margin-bottom:24px;">
                    <?= csrfField() ?>
                    <?php if (empty($_SESSION['applied_coupon'])): ?>
                        <input type="hidden" name="action" value="apply_coupon">
                        <label class="form-label" style="font-size:12.5px;">Have a Promo Code?</label>
                        <div style="display:flex; gap:8px;">
                            <input type="text" name="coupon_code" placeholder="Try CHERIE15 or PARISIAN20" class="form-control" style="text-transform:uppercase; font-size:13px; padding:10px 14px;" required>
                            <button type="submit" class="btn btn-dark btn-sm" style="padding:10px 16px;">Apply</button>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="action" value="remove_coupon">
                        <div style="display:flex; justify-content:space-between; align-items:center; background:#EBF8EE; padding:10px 14px; border-radius:var(--radius-sm); font-size:13px;">
                            <span style="color:#1F7A38; font-weight:500;">✓ Code <?= htmlspecialchars($_SESSION['applied_coupon']['code']) ?> applied</span>
                            <button type="submit" style="background:none; border:none; color:#C0392B; cursor:pointer; font-size:12px; text-decoration:underline;">Remove</button>
                        </div>
                    <?php endif; ?>
                </form>

                <!-- Proceed to Checkout -->
                <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-block" style="padding:15px; font-size:15px;">
                    Proceed to Checkout &rarr;
                </a>

                <div style="margin-top:16px; text-align:center; font-size:12px; color:#958386;">
                    🔒 256-Bit SSL Encrypted Checkout &bull; Easy Doorstep Returns
                </div>
            </div>

        </div>

    <?php else: ?>
        <div class="card-box" style="text-align:center; padding:70px 20px;">
            <div style="font-size:48px; margin-bottom:14px;">🛍️</div>
            <h2 style="font-size:24px; margin-bottom:8px;">Your Shopping Bag is Empty</h2>
            <p style="font-size:14.5px; color:#78666a; margin-bottom:24px;">Your wardrobe is waiting for a little romance. Explore our newest arrivals.</p>
            <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary" style="padding:14px 36px;">Start Shopping &rarr;</a>
        </div>
    <?php endif; ?>

</div>

<style>
@media (max-width: 860px) {
    div[style*="grid-template-columns: 1.8fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
