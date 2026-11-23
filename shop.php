<?php
/**
 * CHÉRIE — Complete Boutique Catalog & Shop Page
 * Filterable by category, price, stock, and sortable by price, newest, and popularity.
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

// Filter & Sort Parameters
$categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;
$priceMin = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0.0;
$priceMax = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 10000.0;
$inStockOnly = !empty($_GET['in_stock']) ? 1 : 0;
$sortBy = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$whereClauses = ["p.is_active = 1"];
$params = [];

if ($categoryId) {
    $whereClauses[] = "p.category_id = ?";
    $params[] = $categoryId;
}

if ($priceMin > 0) {
    $whereClauses[] = "p.price >= ?";
    $params[] = $priceMin;
}

if ($priceMax < 10000) {
    $whereClauses[] = "p.price <= ?";
    $params[] = $priceMax;
}

if ($inStockOnly) {
    $whereClauses[] = "p.stock_quantity > 0";
}

$orderBy = match($sortBy) {
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'rating' => 'p.rating_avg DESC, p.reviews_count DESC',
    'popular' => 'p.reviews_count DESC',
    default => 'p.id DESC' // newest
};

$products = [];
$totalProducts = 0;
$categories = [];

if (Database::isConnected()) {
    try {
        // Fetch all categories with product counts
        $categories = Database::fetchAll("
            SELECT c.*, COUNT(p.id) AS product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ");

        $whereSql = implode(' AND ', $whereClauses);

        // Count total matching products
        $countRow = Database::fetchOne("SELECT COUNT(*) AS total FROM products p WHERE $whereSql", $params);
        $totalProducts = (int)($countRow['total'] ?? 0);

        // Fetch products
        $sql = "
            SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE $whereSql
            ORDER BY $orderBy
            LIMIT $perPage OFFSET $offset
        ";
        $products = Database::fetchAll($sql, $params);
    } catch (Exception $e) {
        // Fallback
    }
}

$totalPages = ceil($totalProducts / $perPage);
$pageTitle = 'Shop All Boutique Collections';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 30px; margin-bottom: 70px;">
    
    <!-- Breadcrumb -->
    <nav style="font-size: 13px; color: #8c767b; margin-bottom: 24px;">
        <a href="<?= BASE_URL ?>/index.php" style="color: inherit;">Home</a> &nbsp;/&nbsp;
        <span style="color: var(--chocolate); font-weight: 500;">Boutique Catalog</span>
    </nav>

    <!-- Header Title & Controls -->
    <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px; margin-bottom:30px; border-bottom:1px solid var(--border-color); padding-bottom:18px;">
        <div>
            <h1 style="font-size: 32px;">Boutique Collection</h1>
            <p style="font-size: 13.5px; color: #735f63; margin-top: 4px;">Showing <?= count($products) ?> of <?= $totalProducts ?> curated designs</p>
        </div>

        <!-- Sort Control -->
        <form method="GET" style="display:flex; align-items:center; gap:10px;">
            <?php if ($categoryId): ?><input type="hidden" name="category" value="<?= $categoryId ?>"><?php endif; ?>
            <?php if ($priceMin > 0): ?><input type="hidden" name="min_price" value="<?= $priceMin ?>"><?php endif; ?>
            <?php if ($priceMax < 10000): ?><input type="hidden" name="max_price" value="<?= $priceMax ?>"><?php endif; ?>
            <?php if ($inStockOnly): ?><input type="hidden" name="in_stock" value="1"><?php endif; ?>

            <label for="sort" style="font-size:13px; font-weight:500; color:var(--chocolate);">Sort By:</label>
            <select name="sort" id="sort" class="form-control" style="width:auto; padding:8px 14px; font-size:13px;" onchange="this.form.submit()">
                <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
                <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="rating" <?= $sortBy === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                <option value="popular" <?= $sortBy === 'popular' ? 'selected' : '' ?>>Most Popular</option>
            </select>
        </form>
    </div>

    <!-- Main Layout: Sidebar Filters + Products Grid -->
    <div style="display:grid; grid-template-columns: 260px 1fr; gap:36px; align-items:start;">
        
        <!-- Sidebar Filters -->
        <aside style="background:#FFF; padding:24px; border-radius:var(--radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-subtle);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                <h3 style="font-size:17px; font-weight:600;">Filters</h3>
                <?php if ($categoryId || $priceMin > 0 || $priceMax < 10000 || $inStockOnly): ?>
                    <a href="<?= BASE_URL ?>/shop.php" style="font-size:12px; color:var(--dusty-rose); font-weight:500;">Reset All</a>
                <?php endif; ?>
            </div>

            <form method="GET" action="<?= BASE_URL ?>/shop.php">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sortBy) ?>">

                <!-- Categories List -->
                <div style="margin-bottom:24px;">
                    <h4 style="font-size:13.5px; font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px; color:var(--chocolate);">
                        Categories
                    </h4>
                    <ul style="list-style:none; max-height:260px; overflow-y:auto; padding-right:5px; font-size:13px;">
                        <li style="margin-bottom:8px;">
                            <a href="<?= BASE_URL ?>/shop.php" style="color: <?= !$categoryId ? 'var(--dusty-rose); font-weight:600;' : '#5a464a;' ?>">
                                All Categories (<?= $totalProducts ?>)
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li style="margin-bottom:8px; display:flex; justify-content:space-between;">
                                <a href="<?= BASE_URL ?>/shop.php?category=<?= $cat['id'] ?>&sort=<?= urlencode($sortBy) ?>" style="color: <?= $categoryId === (int)$cat['id'] ? 'var(--dusty-rose); font-weight:600;' : '#5a464a;' ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                                <span style="font-size:11px; color:#a28e93;">(<?= $cat['product_count'] ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <hr style="border:none; border-top:1px solid #f2e2e6; margin:20px 0;">

                <!-- Price Range Presets -->
                <div style="margin-bottom:24px;">
                    <h4 style="font-size:13.5px; font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px; color:var(--chocolate);">
                        Price Range (INR)
                    </h4>
                    <div style="display:flex; gap:8px; margin-bottom:12px;">
                        <input type="number" name="min_price" value="<?= $priceMin ?>" class="form-control" placeholder="Min" style="padding:6px 10px; font-size:13px;">
                        <span style="align-self:center; color:#999;">-</span>
                        <input type="number" name="max_price" value="<?= $priceMax ?>" class="form-control" placeholder="Max" style="padding:6px 10px; font-size:13px;">
                    </div>
                </div>

                <hr style="border:none; border-top:1px solid #f2e2e6; margin:20px 0;">

                <!-- Stock Filter -->
                <div style="margin-bottom:24px;">
                    <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                        <input type="checkbox" name="in_stock" value="1" <?= $inStockOnly ? 'checked' : '' ?>>
                        <span>In Stock Only</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-sm">Apply Filters</button>
            </form>
        </aside>

        <!-- Product Listing -->
        <main>
            <?php if (!empty($products)): ?>
                <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
                    <?php foreach ($products as $prod): ?>
                        <div class="product-card">
                            <div class="product-thumb-wrap">
                                <?php if (!empty($prod['compare_at_price']) && $prod['compare_at_price'] > $prod['price']): ?>
                                    <span class="product-badge">SALE</span>
                                <?php elseif ($prod['is_new']): ?>
                                    <span class="product-badge" style="background:var(--dusty-rose);">NEW</span>
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

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div style="display:flex; justify-content:center; gap:8px; margin-top:40px;">
                        <?php for ($p = 1; $p <= $totalPages; $p++): 
                            $queryParams = $_GET;
                            $queryParams['page'] = $p;
                            $link = BASE_URL . '/shop.php?' . http_build_query($queryParams);
                        ?>
                            <a href="<?= $link ?>" class="btn <?= $p === $page ? 'btn-primary' : 'btn-outline' ?>" style="width:40px; height:40px; padding:0; border-radius:var(--radius-full);">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="card-box" style="text-align:center; padding:60px 20px;">
                    <div style="font-size:42px; margin-bottom:12px;">🌸</div>
                    <h3 style="font-size:20px; margin-bottom:8px;">No Pieces Found Matching Filters</h3>
                    <p style="font-size:14px; color:#78666a; margin-bottom:24px;">Try relaxing your filter parameters or exploring all boutique categories.</p>
                    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary">Clear All Filters</a>
                </div>
            <?php endif; ?>
        </main>

    </div>
</div>

<style>
@media (max-width: 860px) {
    div[style*="grid-template-columns: 260px 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
