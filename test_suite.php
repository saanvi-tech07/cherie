<?php
/**
 * CHÉRIE — Automated Testing & Verification Suite
 */

define('CHERIE_APP', true);

echo "========================================================\n";
echo "  CHÉRIE BOUTIQUE — AUTOMATED SYSTEM VERIFICATION SUITE\n";
echo "========================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $description, bool $condition, string $detail = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo "[PASSED] " . $description . "\n";
        $passCount++;
    } else {
        echo "[FAILED] " . $description . ($detail ? " — " . $detail : "") . "\n";
        $failCount++;
    }
}

// 1. Check Configuration & Helpers
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/xml_service.php';
require_once __DIR__ . '/includes/razorpay_service.php';
require_once __DIR__ . '/includes/mail_service.php';

assertTest("App Name constant defined", defined('APP_NAME') && APP_NAME === 'CHÉRIE');
assertTest("Currency is INR (₹)", APP_CURRENCY === 'INR' && APP_CURRENCY_SYMBOL === '₹');
assertTest("Brand Colors defined", defined('COLOR_BLUSH_PINK') && COLOR_BLUSH_PINK === '#F7DCE5');

// 2. Helper & Security Functions
$cleanInput = sanitizeInput("  <script>alert('xss')</script>Hello World  ");
assertTest("Sanitize input removes tags", strpos($cleanInput, '<script>') === false);

$token = getCsrfToken();
assertTest("CSRF token generated (length 64)", strlen($token) === 64);
assertTest("CSRF verification valid token", verifyCsrfToken($token) === true);
assertTest("CSRF verification invalid token", verifyCsrfToken('fake_token_123') === false);

$formatted = formatPrice(2499.50);
assertTest("Price formatting INR: " . $formatted, $formatted === '₹2,499.50');

$orderNum = generateOrderNumber();
assertTest("Order number pattern generated: " . $orderNum, str_starts_with($orderNum, 'CHR-'));

$pwd = 'BoutiquePassword2026';
$hash = password_hash($pwd, PASSWORD_BCRYPT);
assertTest("Password hash and verify", password_verify($pwd, $hash) === true && password_verify('wrong', $hash) === false);

// 3. XML SimpleXML Service Layer
$promos = XmlService::getActivePromotions();
assertTest("XML promotions loaded from xml/promotions.xml via SimpleXML", count($promos) >= 2);

$couponCheck1 = XmlService::validateCoupon('CHERIE15', 2000.0);
assertTest("XML coupon CHERIE15 valid for ₹2,000 cart (15% discount)", $couponCheck1['valid'] && $couponCheck1['discount_amount'] == 300.0);

$couponCheckUnderMin = XmlService::validateCoupon('CHERIE15', 500.0);
assertTest("XML coupon minimum threshold enforced", $couponCheckUnderMin['valid'] === false);

$couponInvalid = XmlService::validateCoupon('NONEXISTENT', 2000.0);
assertTest("XML invalid coupon rejected", $couponInvalid['valid'] === false);

$shippingOverFree = XmlService::getShippingMethods(2000.0);
assertTest("XML Shipping tier free above ₹1,499 threshold", $shippingOverFree[0]['cost'] == 0.0);

$shippingUnderFree = XmlService::getShippingMethods(800.0);
assertTest("XML Shipping tier flat rate below threshold", $shippingUnderFree[0]['cost'] == 99.0);

// 4. Razorpay Test Mode Verification
$simulatedOrder = RazorpayService::createOrder(2499.00, 'test_rcpt_1');
assertTest("Razorpay order creation returns valid order structure", $simulatedOrder['success'] && !empty($simulatedOrder['order_id']));

$validSig = RazorpayService::verifySignature($simulatedOrder['order_id'], 'test_pay_99', 'simulated_test_sig_test_pay_99');
assertTest("Razorpay test signature verification", $validSig === true);

// 5. Mail Service Transparent Reporting
$mailResult = MailService::sendRegistrationConfirmation('client@example.com', 'Aanya');
assertTest("Mail service reports transparent SMTP status without faking delivery", $mailResult['sent'] === false && $mailResult['status'] === 'NOT_CONFIGURED');

// 6. File Structure Completeness
$requiredFiles = [
    'index.php', 'shop.php', 'category.php', 'product.php', 'search.php',
    'cart.php', 'wishlist.php', 'checkout.php', 'login.php', 'register.php',
    'logout.php', 'account.php', 'orders.php', 'order-details.php',
    'forgot-password.php', 'reset-password.php', 'about.php', 'contact.php',
    'privacy-policy.php', 'terms.php', 'install.php',
    'config/config.php', 'config/database.php', 'config/mail.php', 'config/razorpay.php',
    'database/schema.sql', 'database/seed_data.sql', 'database/install.sql',
    'xml/promotions.xml', 'xml/shipping_rates.xml', 'xml/catalog_export.php',
    'assets/css/style.css', 'assets/css/admin.css',
    'assets/js/main.js', 'assets/js/admin.js', 'assets/js/cart.js', 'assets/js/wishlist.js',
    'assets/images/logo.svg', 'assets/images/hero-banner.svg',
    'admin/setup.php', 'admin/login.php', 'admin/logout.php', 'admin/index.php',
    'admin/products/index.php', 'admin/products/add.php', 'admin/products/edit.php', 'admin/products/delete.php',
    'admin/categories/index.php', 'admin/categories/add.php', 'admin/categories/edit.php', 'admin/categories/delete.php',
    'admin/orders/index.php', 'admin/orders/view.php',
    'admin/users/index.php', 'admin/messages.php', 'admin/newsletter.php', 'admin/settings.php',
    'uploads/.htaccess'
];

$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        $missingFiles[] = $file;
    }
}
assertTest("All core project files present (" . count($requiredFiles) . " files)", empty($missingFiles), implode(', ', $missingFiles));

// 7. Check All 42 Product Images & 21 Category Images
$missingAssets = [];
for ($i = 1; $i <= 42; $i++) {
    if (!file_exists(__DIR__ . "/assets/images/products/prod_{$i}_1.svg")) {
        $missingAssets[] = "prod_{$i}_1.svg";
    }
}
$catImages = [
    'cat_dresses', 'cat_tops', 'cat_coord_sets', 'cat_jeans', 'cat_skirts',
    'cat_ethnic', 'cat_makeup', 'cat_skincare', 'cat_jewellery', 'cat_sunglasses',
    'cat_hair', 'cat_watches', 'cat_handbags', 'cat_totes', 'cat_backpacks',
    'cat_flats', 'cat_heels', 'cat_sneakers', 'cat_sandals', 'cat_stationery', 'cat_decor'
];
foreach ($catImages as $ci) {
    if (!file_exists(__DIR__ . "/assets/images/categories/{$ci}.svg")) {
        $missingAssets[] = "{$ci}.svg";
    }
}
assertTest("All 42 product SVG artworks and 21 category vector covers present", empty($missingAssets), implode(', ', $missingAssets));

echo "\n--------------------------------------------------------\n";
echo "SUMMARY: {$passCount} PASSED, {$failCount} FAILED\n";
echo "--------------------------------------------------------\n";
