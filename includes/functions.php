<?php
/**
 * CHÉRIE — Global Helper & Security Functions
 */

if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}

// ----------------------------------------------------
// 1. Security & Sanitization
// ----------------------------------------------------

/**
 * Escape HTML output to prevent XSS
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Sanitize string input
 */
function sanitizeInput(?string $data): string {
    if ($data === null) return '';
    $data = trim($data);
    $data = stripslashes($data);
    return strip_tags($data);
}

/**
 * Generate or retrieve CSRF token
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render hidden CSRF token input field
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(getCsrfToken()) . '">';
}

/**
 * Verify CSRF token from POST request
 */
function verifyCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ----------------------------------------------------
// 2. Authentication & Session Helpers
// ----------------------------------------------------

/**
 * Check if customer is logged in
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_email']);
}

/**
 * Get current logged in user details from session or database
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'Guest',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'customer'
    ];
}

/**
 * Require customer login, redirect if not logged in
 */
function requireAuth(string $redirectTo = 'login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        setFlash('warning', 'Please sign in to access your account.');
        header('Location: ' . BASE_URL . '/' . $redirectTo);
        exit;
    }
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_role']);
}

/**
 * Get current admin user details
 */
function getCurrentAdmin(): ?array {
    if (!isAdminLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'] ?? '',
        'email' => $_SESSION['admin_email'] ?? '',
        'full_name' => $_SESSION['admin_name'] ?? 'Administrator',
        'role' => $_SESSION['admin_role'] ?? 'superadmin'
    ];
}

/**
 * Require admin authentication
 */
function requireAdmin(string $redirectTo = 'admin/login.php'): void {
    if (!isAdminLoggedIn()) {
        $_SESSION['admin_redirect'] = $_SERVER['REQUEST_URI'];
        setFlash('danger', 'Administrative access required. Please log in.');
        header('Location: ' . BASE_URL . '/' . $redirectTo);
        exit;
    }
}

// ----------------------------------------------------
// 3. Flash Messages (Alerts)
// ----------------------------------------------------

/**
 * Set flash alert message
 * Types: success, danger, warning, info
 */
function setFlash(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash alert messages
 */
function getFlashMessages(): array {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

// ----------------------------------------------------
// 4. Formatting & Utilities
// ----------------------------------------------------

/**
 * Format currency amount (INR ₹)
 */
function formatPrice(float|int|string $amount): string {
    $val = (float)$amount;
    return '₹' . number_format($val, 2, '.', ',');
}

/**
 * Generate unique order number (e.g. CHR-20261123-A8F2)
 */
function generateOrderNumber(): string {
    return 'CHR-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/**
 * Generate SEO-friendly slug
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'n-a');
}

/**
 * Get website setting from database with fallback
 */
function getSetting(string $key, string $default = ''): string {
    static $settingsCache = null;
    if ($settingsCache === null) {
        $settingsCache = [];
        try {
            $rows = Database::fetchAll("SELECT setting_key, setting_value FROM website_settings");
            foreach ($rows as $r) {
                $settingsCache[$r['setting_key']] = $r['setting_value'];
            }
        } catch (Exception $e) {
            // DB might not be ready yet
        }
    }
    return $settingsCache[$key] ?? $default;
}

// ----------------------------------------------------
// 5. Shopping Cart Core Functions
// ----------------------------------------------------

/**
 * Get or initialize persistent Cart Session ID
 */
function getCartSessionId(): string {
    if (empty($_SESSION['cart_session_id'])) {
        $_SESSION['cart_session_id'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['cart_session_id'];
}

/**
 * Get or create Cart record in DB (or memory fallback)
 */
function getOrCreateCartId(): int {
    $sessionId = getCartSessionId();
    $userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;

    if (!Database::isConnected()) {
        return 0;
    }

    try {
        if ($userId) {
            // Search by user first
            $cart = Database::fetchOne("SELECT id FROM cart WHERE user_id = ? LIMIT 1", [$userId]);
            if ($cart) {
                return (int)$cart['id'];
            }
            // Update session cart with user_id if exists
            $sessionCart = Database::fetchOne("SELECT id FROM cart WHERE session_id = ? LIMIT 1", [$sessionId]);
            if ($sessionCart) {
                Database::execute("UPDATE cart SET user_id = ? WHERE id = ?", [$userId, $sessionCart['id']]);
                return (int)$sessionCart['id'];
            }
            // Create user cart
            $cartId = Database::insert("INSERT INTO cart (user_id, session_id) VALUES (?, ?)", [$userId, $sessionId]);
            return (int)$cartId;
        } else {
            // Guest cart by session
            $cart = Database::fetchOne("SELECT id FROM cart WHERE session_id = ? LIMIT 1", [$sessionId]);
            if ($cart) {
                return (int)$cart['id'];
            }
            $cartId = Database::insert("INSERT INTO cart (session_id) VALUES (?)", [$sessionId]);
            return (int)$cartId;
        }
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Add product to cart with stock validation
 */
function addToCart(int $productId, int $quantity = 1, ?int $variantId = null): array {
    if (!Database::isConnected()) {
        return ['success' => false, 'message' => 'Database connection unavailable'];
    }

    $product = Database::fetchOne("SELECT * FROM products WHERE id = ? AND is_active = 1", [$productId]);
    if (!$product) {
        return ['success' => false, 'message' => 'Product not found or inactive.'];
    }

    // Check available stock
    $availableStock = (int)$product['stock_quantity'];
    $unitPrice = (float)$product['price'];

    if ($variantId) {
        $variant = Database::fetchOne("SELECT * FROM product_variants WHERE id = ? AND product_id = ?", [$variantId, $productId]);
        if ($variant) {
            $availableStock = (int)$variant['stock_quantity'];
            $unitPrice += (float)$variant['additional_price'];
        }
    }

    if ($availableStock < $quantity) {
        return ['success' => false, 'message' => 'Requested quantity exceeds available stock (' . $availableStock . ' available).'];
    }

    $cartId = getOrCreateCartId();
    if (!$cartId) {
        return ['success' => false, 'message' => 'Unable to initialize cart.'];
    }

    // Check if item already exists in cart
    $sql = "SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND " . 
           ($variantId ? "variant_id = ?" : "variant_id IS NULL");
    $params = $variantId ? [$cartId, $productId, $variantId] : [$cartId, $productId];
    $existing = Database::fetchOne($sql, $params);

    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        if ($newQty > $availableStock) {
            return ['success' => false, 'message' => 'Total quantity in cart would exceed stock limit (' . $availableStock . ').'];
        }
        Database::execute("UPDATE cart_items SET quantity = ?, unit_price = ? WHERE id = ?", [$newQty, $unitPrice, $existing['id']]);
    } else {
        Database::insert("INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, unit_price) VALUES (?, ?, ?, ?, ?)", [
            $cartId, $productId, $variantId, $quantity, $unitPrice
        ]);
    }

    return ['success' => true, 'message' => 'Added to your bag with love!'];
}

/**
 * Get all items in current cart
 */
function getCartItems(): array {
    if (!Database::isConnected()) return [];
    $cartId = getOrCreateCartId();
    if (!$cartId) return [];

    $sql = "SELECT ci.*, p.name AS product_name, p.slug AS product_slug, p.stock_quantity AS max_stock,
                   pv.variant_type, pv.variant_value,
                   (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN product_variants pv ON ci.variant_id = pv.id
            WHERE ci.cart_id = ?
            ORDER BY ci.id DESC";

    return Database::fetchAll($sql, [$cartId]);
}

/**
 * Get total number of items in cart
 */
function getCartCount(): int {
    if (!Database::isConnected()) return 0;
    $cartId = getOrCreateCartId();
    if (!$cartId) return 0;

    $res = Database::fetchOne("SELECT SUM(quantity) AS total_qty FROM cart_items WHERE cart_id = ?", [$cartId]);
    return (int)($res['total_qty'] ?? 0);
}

/**
 * Calculate cart subtotal, shipping, and total
 */
function getCartTotals(): array {
    $items = getCartItems();
    $subtotal = 0.0;
    foreach ($items as $item) {
        $subtotal += ($item['unit_price'] * $item['quantity']);
    }

    $freeShippingMin = (float)getSetting('free_shipping_threshold', '1499');
    $flatShipping = (float)getSetting('flat_shipping_rate', '99');

    $shipping = ($subtotal >= $freeShippingMin || $subtotal == 0) ? 0.0 : $flatShipping;
    $total = $subtotal + $shipping;

    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'free_shipping_min' => $freeShippingMin,
        'needed_for_free_shipping' => max(0, $freeShippingMin - $subtotal),
        'total' => $total,
        'item_count' => count($items)
    ];
}

/**
 * Update quantity of item in cart
 */
function updateCartQuantity(int $cartItemId, int $newQuantity): array {
    if (!Database::isConnected()) return ['success' => false, 'message' => 'Database offline.'];
    if ($newQuantity <= 0) {
        return removeCartItem($cartItemId);
    }

    $item = Database::fetchOne("SELECT ci.*, p.stock_quantity, pv.stock_quantity AS variant_stock
                                FROM cart_items ci
                                JOIN products p ON ci.product_id = p.id
                                LEFT JOIN product_variants pv ON ci.variant_id = pv.id
                                WHERE ci.id = ?", [$cartItemId]);
    if (!$item) {
        return ['success' => false, 'message' => 'Cart item not found.'];
    }

    $maxStock = ($item['variant_stock'] !== null) ? (int)$item['variant_stock'] : (int)$item['stock_quantity'];
    if ($newQuantity > $maxStock) {
        return ['success' => false, 'message' => 'Only ' . $maxStock . ' pieces available in stock.'];
    }

    Database::execute("UPDATE cart_items SET quantity = ? WHERE id = ?", [$newQuantity, $cartItemId]);
    return ['success' => true, 'message' => 'Bag updated.'];
}

/**
 * Remove an item from cart
 */
function removeCartItem(int $cartItemId): array {
    if (!Database::isConnected()) return ['success' => false, 'message' => 'Database offline.'];
    $cartId = getOrCreateCartId();
    Database::execute("DELETE FROM cart_items WHERE id = ? AND cart_id = ?", [$cartItemId, $cartId]);
    return ['success' => true, 'message' => 'Item removed from bag.'];
}

/**
 * Merge guest cart into user cart after login
 */
function mergeGuestCartOnLogin(int $userId): void {
    if (!Database::isConnected()) return;
    $sessionId = getCartSessionId();

    $guestCart = Database::fetchOne("SELECT id FROM cart WHERE session_id = ? AND (user_id IS NULL OR user_id != ?) LIMIT 1", [$sessionId, $userId]);
    if (!$guestCart) return;

    $userCart = Database::fetchOne("SELECT id FROM cart WHERE user_id = ? LIMIT 1", [$userId]);
    if (!$userCart) {
        // Simply assign guest cart to user
        Database::execute("UPDATE cart SET user_id = ? WHERE id = ?", [$userId, $guestCart['id']]);
        return;
    }

    // Merge items from guest cart into user cart
    $guestItems = Database::fetchAll("SELECT * FROM cart_items WHERE cart_id = ?", [$guestCart['id']]);
    foreach ($guestItems as $gItem) {
        $existing = Database::fetchOne("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND " . 
            ($gItem['variant_id'] ? "variant_id = ?" : "variant_id IS NULL"),
            $gItem['variant_id'] ? [$userCart['id'], $gItem['product_id'], $gItem['variant_id']] : [$userCart['id'], $gItem['product_id']]
        );

        if ($existing) {
            Database::execute("UPDATE cart_items SET quantity = quantity + ? WHERE id = ?", [$gItem['quantity'], $existing['id']]);
        } else {
            Database::insert("INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, unit_price) VALUES (?, ?, ?, ?, ?)", [
                $userCart['id'], $gItem['product_id'], $gItem['variant_id'], $gItem['quantity'], $gItem['unit_price']
            ]);
        }
    }

    // Delete empty guest cart
    Database::execute("DELETE FROM cart WHERE id = ?", [$guestCart['id']]);
}

// ----------------------------------------------------
// 6. Wishlist Helpers
// ----------------------------------------------------

function getWishlistCount(): int {
    if (!isLoggedIn() || !Database::isConnected()) return 0;
    $userId = (int)$_SESSION['user_id'];
    $res = Database::fetchOne("SELECT COUNT(*) AS total FROM wishlist WHERE user_id = ?", [$userId]);
    return (int)($res['total'] ?? 0);
}

function isInWishlist(int $productId): bool {
    if (!isLoggedIn() || !Database::isConnected()) return false;
    $userId = (int)$_SESSION['user_id'];
    $res = Database::fetchOne("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
    return !empty($res);
}

function toggleWishlist(int $productId): array {
    if (!isLoggedIn()) {
        return ['success' => false, 'need_login' => true, 'message' => 'Please sign in to save items to your wishlist.'];
    }
    if (!Database::isConnected()) {
        return ['success' => false, 'message' => 'Database unavailable.'];
    }

    $userId = (int)$_SESSION['user_id'];
    if (isInWishlist($productId)) {
        Database::execute("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
        return ['success' => true, 'action' => 'removed', 'message' => 'Item removed from your wishlist.'];
    } else {
        Database::insert("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)", [$userId, $productId]);
        return ['success' => true, 'action' => 'added', 'message' => 'Saved to your wishlist!'];
    }
}

// ----------------------------------------------------
// 7. Secure Product Image Upload Handler
// ----------------------------------------------------

/**
 * Validates and securely uploads a product image
 * Checks extension, MIME type, file size, safe random name, and directory containment
 */
function uploadProductImage(array $file): array {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxFileSize = 4 * 1024 * 1024; // 4 MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error code: ' . $file['error']];
    }

    if ($file['size'] > $maxFileSize) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed limit (4MB).'];
    }

    // Validate extension
    $originalName = basename($file['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return ['success' => false, 'message' => 'Invalid file format. Only JPG, PNG, and WebP images are allowed.'];
    }

    // Validate MIME type with finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes, true)) {
        return ['success' => false, 'message' => 'Security alert: Disallowed MIME type (' . $mimeType . ').'];
    }

    // Generate cryptographically safe filename
    $newFilename = 'prod_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $targetDir = PRODUCT_UPLOADS_PATH;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $destination = $targetDir . DIRECTORY_SEPARATOR . $newFilename;

    // Move file securely
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Failed to save uploaded file to destination.'];
    }

    // Return web-accessible path
    return [
        'success' => true,
        'filename' => $newFilename,
        'relative_path' => 'uploads/products/' . $newFilename
    ];
}
