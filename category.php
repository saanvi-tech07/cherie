<?php
/**
 * CHÉRIE — Category Landing Page
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$category = null;
$products = [];

if ($slug && Database::isConnected()) {
    $category = Database::fetchOne("SELECT * FROM categories WHERE slug = ? AND is_active = 1 LIMIT 1", [$slug]);
    if ($category) {
        $products = Database::fetchAll("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.category_id = ? AND p.is_active = 1
            ORDER BY p.id ASC
        ", [$category['id']]);
    }
}

if (!$category) {
    header('Location: ' . BASE_URL . '/shop.php');
    exit;
}

$pageTitle = $category['name'] . ' Collection';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 30px; margin-bottom: 70px;">
    
    <!-- Breadcrumb -->
    <nav style="font-size: 13px; color: #8c767b; margin-bottom: 24px;">
        <a href="<?= BASE_URL ?>/index.php" style="color: inherit;">Home</a> &nbsp;/&nbsp;
        <a href="<?= BASE_URL ?>/shop.php" style="color: inherit;">Shop</a> &nbsp;/&nbsp;
        <span style="color: var(--chocolate); font-weight: 500;"><?= htmlspecialchars($category['name']) ?></span>
    </nav>

    <!-- Category Editorial Banner -->
    <div style="background:linear-gradient(135deg, var(--blush), var(--lavender)); border-radius:var(--radius-lg); padding:50px 30px; margin-bottom:40px; text-align:center; border:1px solid var(--border-color);">
        <span class="section-tagline">Boutique Edit</span>
        <h1 style="font-size:38px; margin-bottom:12px;"><?= htmlspecialchars($category['name']) ?></h1>
        <p style="font-size:15px; color:#5b484c; max-width:650px; margin:0 auto; line-height:1.6;">
            <?= htmlspecialchars($category['description']) ?>
        </p>
    </div>

    <!-- Product Grid -->
    <?php if (!empty($products)): ?>
        <div class="product-grid">
            <?php foreach ($products as $prod): ?>
                <div class="product-card">
                    <div class="product-thumb-wrap">
                        <?php if (!empty($prod['compare_at_price']) && $prod['compare_at_price'] > $prod['price']): ?>
                            <span class="product-badge">SALE</span>
                        <?php endif; ?>

                        <button class="product-wishlist-btn <?= isInWishlist((int)$prod['id']) ? 'active' : '' ?>" data-product-id="<?= $prod['id'] ?>" title="Save to Wishlist">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        </button>

                        <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>">
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($prod['image_path'] ?? 'assets/images/products/prod_1_1.svg') ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy">
                        </a>
                    </div>

                    <div class="product-info">
                        <span class="product-cat"><?= htmlspecialchars($prod['category_name']) ?></span>
                        <h3 class="product-name">
                            <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>"><?= htmlspecialchars($prod['name']) ?></a>
                        </h3>
                        <div class="product-rating">
                            <span>★ <?= number_format($prod['rating_avg'], 1) ?></span>
                            <span class="product-reviews-count">(<?= $prod['reviews_count'] ?>)</span>
                        </div>
                        <div class="product-pricing">
                            <span class="current-price"><?= formatPrice($prod['price']) ?></span>
                            <?php if (!empty($prod['compare_at_price']) && $prod['compare_at_price'] > $prod['price']): ?>
                                <span class="old-price"><?= formatPrice($prod['compare_at_price']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top:14px;">
                            <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>" class="btn btn-outline btn-sm btn-block">
                                View Details &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card-box" style="text-align:center; padding:60px 20px;">
            <p>New pieces are arriving soon for this collection!</p>
            <br>
            <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary">Browse All Pieces</a>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
