<?php
/**
 * CHÉRIE — Homepage
 * High-Aesthetic Pinterest-Inspired Boutique Storefront
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

// Handle AJAX Newsletter subscription if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $email = filter_var(trim($_POST['newsletter_email']), FILTER_VALIDATE_EMAIL);
    $response = ['success' => false, 'message' => 'Please enter a valid email address.'];

    if ($email && Database::isConnected()) {
        try {
            $stmt = Database::query("INSERT INTO newsletter_subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE is_active = 1", [$email]);
            $response = ['success' => true, 'message' => 'Welcome to the CHÉRIE Circle! Check your inbox for sweet surprises.'];
        } catch (Exception $e) {
            $response = ['success' => true, 'message' => 'You are already in our circle!'];
        }
    }
    
    if (isset($_POST['ajax_newsletter'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        setFlash($response['success'] ? 'success' : 'danger', $response['message']);
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

// Fetch Featured Categories
$featuredCategories = [];
$trendingProducts = [];
$newArrivals = [];
$customerReviews = [];

if (Database::isConnected()) {
    try {
        $featuredCategories = Database::fetchAll("SELECT * FROM categories WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order ASC LIMIT 8");
        $trendingProducts = Database::fetchAll("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.is_featured = 1
            ORDER BY p.rating_avg DESC, p.id ASC
            LIMIT 8
        ");
        $newArrivals = Database::fetchAll("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                   (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.is_new = 1
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT 8
        ");
        $customerReviews = Database::fetchAll("
            SELECT r.*, p.name AS product_name, p.slug AS product_slug
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            WHERE r.is_approved = 1
            ORDER BY r.rating DESC, r.id DESC
            LIMIT 3
        ");
    } catch (Exception $e) {
        // Fallback for fresh installation
    }
}

$pageTitle = 'Romantic Girls’ Fashion, Beauty & Lifestyle Boutique';
require_once __DIR__ . '/includes/header.php';
?>

<main>
    <div class="container">
        
        <!-- 1. Hero Editorial Banner -->
        <section class="hero-section">
            <a href="<?= BASE_URL ?>/shop.php">
                <img src="<?= BASE_URL ?>/assets/images/hero-banner.svg" alt="CHÉRIE New Season Collection" class="hero-img">
            </a>
        </section>

        <!-- 2. Boutique Values / Service Highlights -->
        <section style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:50px; text-align:center;">
            <div style="background:#FFF; padding:22px; border-radius:var(--radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-subtle);">
                <div style="font-size:26px; margin-bottom:8px;">🎀</div>
                <h4 style="font-size:14px; font-weight:600; margin-bottom:4px;">Artisanal Craftsmanship</h4>
                <p style="font-size:12px; color:#78666a;">Ethically made with pure breathable linens & georgettes.</p>
            </div>
            <div style="background:#FFF; padding:22px; border-radius:var(--radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-subtle);">
                <div style="font-size:26px; margin-bottom:8px;">📦</div>
                <h4 style="font-size:14px; font-weight:600; margin-bottom:4px;">Rose Ribbon Packaging</h4>
                <p style="font-size:12px; color:#78666a;">Every parcel unwraps like a personal Parisienne gift.</p>
            </div>
            <div style="background:#FFF; padding:22px; border-radius:var(--radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-subtle);">
                <div style="font-size:26px; margin-bottom:8px;">✨</div>
                <h4 style="font-size:14px; font-weight:600; margin-bottom:4px;">Complimentary Delivery</h4>
                <p style="font-size:12px; color:#78666a;">Free express shipping across India on orders above ₹1,499.</p>
            </div>
            <div style="background:#FFF; padding:22px; border-radius:var(--radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-subtle);">
                <div style="font-size:26px; margin-bottom:8px;">💌</div>
                <h4 style="font-size:14px; font-weight:600; margin-bottom:4px;">Concierge Support</h4>
                <p style="font-size:12px; color:#78666a;">Styling recommendations & friendly WhatsApp care.</p>
            </div>
        </section>

        <!-- 3. Curated Categories -->
        <section>
            <div class="section-header">
                <span class="section-tagline">Curated Aesthetics</span>
                <h2 class="section-title">Shop by Category</h2>
            </div>

            <div class="category-grid">
                <?php if (!empty($featuredCategories)): ?>
                    <?php foreach ($featuredCategories as $cat): ?>
                        <a href="<?= BASE_URL ?>/category.php?slug=<?= urlencode($cat['slug']) ?>" class="category-card">
                            <div class="category-card-img-wrap">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($cat['image_url'] ?? 'assets/images/categories/cat_dresses.svg') ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy">
                            </div>
                            <div class="category-card-title"><?= htmlspecialchars($cat['name']) ?> &rarr;</div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align:center; padding:30px; color:#888;">
                        Database is currently being initialized. Please run <a href="install.php" style="color:var(--dusty-rose); font-weight:600;">install.php</a> to seed all categories.
                    </p>
                <?php endif; ?>
            </div>
        </section>

        <!-- 4. Trending Pieces / Bestsellers -->
        <section>
            <div class="section-header">
                <span class="section-tagline">Loved by Thousands</span>
                <h2 class="section-title">Most Coveted Pieces</h2>
            </div>

            <div class="product-grid">
                <?php foreach ($trendingProducts as $prod): ?>
                    <div class="product-card">
                        <div class="product-thumb-wrap">
                            <span class="product-badge">BESTSELLER</span>
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
                                <span class="product-reviews-count">(<?= $prod['reviews_count'] ?> reviews)</span>
                            </div>
                            <div class="product-pricing">
                                <span class="current-price"><?= formatPrice($prod['price']) ?></span>
                                <?php if (!empty($prod['compare_at_price']) && $prod['compare_at_price'] > $prod['price']): ?>
                                    <span class="old-price"><?= formatPrice($prod['compare_at_price']) ?></span>
                                    <span class="discount-tag">Save <?= round((($prod['compare_at_price'] - $prod['price']) / $prod['compare_at_price']) * 100) ?>%</span>
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
        </section>

        <!-- 5. Editorial Story Banner -->
        <section class="story-banner">
            <div class="story-content">
                <span class="section-tagline">Artisan Philosophy</span>
                <h2 class="story-title">A Little Love in Every Single Detail.</h2>
                <p class="story-text">
                    CHÉRIE was born from an enduring adoration for French romanticism, vintage silhouettes, and mindful craftsmanship. Each dress, dainty necklace, and velvet lip tint is created to make your everyday moments feel like poetry.
                </p>
                <div style="display:flex; gap:14px; flex-wrap:wrap;">
                    <a href="<?= BASE_URL ?>/about.php" class="btn btn-dark">Read Our Story</a>
                    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-outline">Explore Boutique</a>
                </div>
            </div>
            <div style="flex-shrink:0; text-align:center;">
                <div style="background:#FFF; padding:24px 30px; border-radius:var(--radius-lg); border:1px solid var(--border-color); box-shadow:var(--shadow-medium); max-width:320px;">
                    <div style="font-size:48px; margin-bottom:10px;">💌</div>
                    <h4 style="font-family:'Playfair Display',serif; font-size:20px; color:var(--chocolate); margin-bottom:6px;">Boutique Quality</h4>
                    <p style="font-size:13px; color:#685458;">Certified hypoallergenic metals, 100% natural rose botanicals, and sustainably sourced textiles.</p>
                </div>
            </div>
        </section>

        <!-- 6. New Arrivals -->
        <section>
            <div class="section-header">
                <span class="section-tagline">Fresh from the Atelier</span>
                <h2 class="section-title">New Arrivals</h2>
            </div>

            <div class="product-grid">
                <?php foreach ($newArrivals as $prod): ?>
                    <div class="product-card">
                        <div class="product-thumb-wrap">
                            <span class="product-badge" style="background-color:var(--dusty-rose);">NEW</span>
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

            <div style="text-align:center; margin-top:20px; margin-bottom:60px;">
                <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary" style="padding:14px 40px;">
                    View Complete Catalog (42 Items) &rarr;
                </a>
            </div>
        </section>

        <!-- 7. Customer Love / Testimonials -->
        <?php if (!empty($customerReviews)): ?>
        <section style="margin-bottom:70px;">
            <div class="section-header">
                <span class="section-tagline">Client Notes</span>
                <h2 class="section-title">Letters of Love</h2>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:24px;">
                <?php foreach ($customerReviews as $rev): ?>
                    <div class="card-box" style="margin-bottom:0; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <div style="color:#E6A23C; font-size:16px; margin-bottom:8px;">
                                <?= str_repeat('★', (int)$rev['rating']) ?>
                            </div>
                            <h4 style="font-size:15px; font-weight:600; margin-bottom:8px; color:var(--chocolate);">
                                &ldquo;<?= htmlspecialchars($rev['title']) ?>&rdquo;
                            </h4>
                            <p style="font-size:13.5px; color:#625054; line-height:1.6; font-style:italic;">
                                <?= htmlspecialchars($rev['comment']) ?>
                            </p>
                        </div>
                        <div style="margin-top:20px; border-top:1px solid #f2e2e6; padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-weight:600; font-size:13px; color:var(--chocolate);">
                                <?= htmlspecialchars($rev['customer_name']) ?>
                            </span>
                            <span style="font-size:11.5px; color:var(--dusty-rose);">Verified Buyer</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
