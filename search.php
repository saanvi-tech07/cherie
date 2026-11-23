<?php
/**
 * CHÉRIE — Search Catalog
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

$query = trim($_GET['q'] ?? '');
$products = [];
$totalResults = 0;

if ($query && Database::isConnected()) {
    $searchTerm = '%' . $query . '%';
    $sql = "
        SELECT p.*, c.name AS category_name, c.slug AS category_slug,
               (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1 AND (
            p.name LIKE ? OR 
            p.sku LIKE ? OR 
            p.short_description LIKE ? OR 
            p.description LIKE ? OR
            c.name LIKE ?
        )
        ORDER BY p.id DESC
    ";
    $products = Database::fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $totalResults = count($products);
}

$pageTitle = $query ? 'Search results for "' . $query . '"' : 'Search Our Collection';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 35px; margin-bottom: 70px;">
    
    <!-- Search Bar Banner -->
    <div style="background:var(--white); border-radius:var(--radius-lg); padding:40px; border:1px solid var(--border-color); box-shadow:var(--shadow-subtle); max-width:800px; margin:0 auto 40px; text-align:center;">
        <span class="section-tagline">Find Your Favorite Piece</span>
        <h1 style="font-size:32px; margin-bottom:20px;">Search CHÉRIE</h1>
        <form method="GET" action="<?= BASE_URL ?>/search.php" style="display:flex; gap:10px; max-width:600px; margin:0 auto;">
            <input type="search" name="q" value="<?= htmlspecialchars($query) ?>" class="form-control" placeholder="Search for dresses, satin skirts, jewellery, lip tints..." required style="padding:14px 20px; font-size:15px; border-radius:var(--radius-full);">
            <button type="submit" class="btn btn-primary" style="padding:14px 28px;">Search</button>
        </form>
    </div>

    <!-- Search Results -->
    <?php if ($query): ?>
        <div style="margin-bottom:30px; display:flex; justify-content:space-between; align-items:center;">
            <h2 style="font-size:22px;">Results for &ldquo;<?= htmlspecialchars($query) ?>&rdquo;</h2>
            <span style="font-size:13.5px; color:#78666a;"><?= $totalResults ?> items found</span>
        </div>

        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <?php foreach ($products as $prod): ?>
                    <div class="product-card">
                        <div class="product-thumb-wrap">
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
                            <div class="product-pricing">
                                <span class="current-price"><?= formatPrice($prod['price']) ?></span>
                            </div>
                            <div style="margin-top:14px;">
                                <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>" class="btn btn-outline btn-sm btn-block">View Details &rarr;</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card-box" style="text-align:center; padding:50px 20px;">
                <p style="color:#78666a; font-size:15px; margin-bottom:20px;">No products found matching &ldquo;<?= htmlspecialchars($query) ?>&rdquo;. Try searching for &ldquo;Dress&rdquo;, &ldquo;Satin&rdquo;, &ldquo;Rose&rdquo;, or &ldquo;Jewellery&rdquo;.</p>
                <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary">Browse All Pieces</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
