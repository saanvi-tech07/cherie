<?php
/**
 * CHÉRIE — Customer Wishlist
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

// Handle AJAX toggle request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_wishlist'])) {
    $productId = (int)($_POST['product_id'] ?? 0);
    $res = toggleWishlist($productId);
    $res['wishlist_count'] = getWishlistCount();

    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}

// Require login to view full wishlist page
requireAuth('login.php');

$userId = (int)$_SESSION['user_id'];

// Handle Move to Bag action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['move_to_bag'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $productId = (int)($_POST['product_id'] ?? 0);
        $res = addToCart($productId, 1);
        if ($res['success']) {
            Database::execute("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
            setFlash('success', 'Moved to your shopping bag! ✨');
        } else {
            setFlash('danger', $res['message']);
        }
    }
    header('Location: ' . BASE_URL . '/wishlist.php');
    exit;
}

// Handle Remove action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_wishlist'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $productId = (int)($_POST['product_id'] ?? 0);
        Database::execute("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
        setFlash('info', 'Item removed from wishlist.');
    }
    header('Location: ' . BASE_URL . '/wishlist.php');
    exit;
}

// Fetch Wishlist Items
$items = Database::fetchAll("
    SELECT p.*, c.name AS category_name,
           (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY w.id DESC
", [$userId]);

$pageTitle = 'My Wishlist (' . count($items) . ')';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 35px; margin-bottom: 70px;">
    
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 32px;">My Wishlist</h1>
        <p style="font-size: 14px; color: #766367;">Your curated collection of dream pieces, saved for later.</p>
    </div>

    <?php if (!empty($items)): ?>
        <div class="product-grid">
            <?php foreach ($items as $prod): ?>
                <div class="product-card">
                    <div class="product-thumb-wrap">
                        <form method="POST" style="position:absolute; top:12px; right:12px; z-index:2;">
                            <?= csrfField() ?>
                            <input type="hidden" name="remove_wishlist" value="1">
                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                            <button type="submit" class="product-wishlist-btn active" title="Remove from Wishlist">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                            </button>
                        </form>

                        <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>">
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($prod['image_path'] ?? 'assets/images/products/prod_1_1.svg') ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy">
                        </a>
                    </div>

                    <div class="product-info">
                        <span class="product-cat"><?= htmlspecialchars($prod['category_name']) ?></span>
                        <h3 class="product-name">
                            <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>"><?= htmlspecialchars($prod['name']) ?></a>
                        </h3>
                        <div class="product-pricing">
                            <span class="current-price"><?= formatPrice($prod['price']) ?></span>
                        </div>
                        <div style="margin-top:14px; display:flex; gap:8px;">
                            <form method="POST" style="flex-grow:1;">
                                <?= csrfField() ?>
                                <input type="hidden" name="move_to_bag" value="1">
                                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm btn-block" <?= $prod['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                                    <?= $prod['stock_quantity'] > 0 ? 'Move to Bag &rarr;' : 'Out of Stock' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card-box" style="text-align:center; padding:70px 20px;">
            <div style="font-size:48px; margin-bottom:14px;">🤍</div>
            <h2 style="font-size:24px; margin-bottom:8px;">Your Wishlist is Empty</h2>
            <p style="font-size:14.5px; color:#78666a; margin-bottom:24px;">Explore our dreamy silhouettes and tap the heart icon to save items here.</p>
            <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary" style="padding:14px 36px;">Explore Boutique &rarr;</a>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
