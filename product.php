<?php
/**
 * CHÉRIE — Product Detail Page
 * Gallery, Variant Selectors, Stock Status, Cart Action & Customer Reviews
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$product = null;
$images = [];
$variants = [];
$reviews = [];
$relatedProducts = [];

if ($slug && Database::isConnected()) {
    $product = Database::fetchOne("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ? AND p.is_active = 1
        LIMIT 1
    ", [$slug]);

    if ($product) {
        $productId = (int)$product['id'];

        // Images
        $images = Database::fetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC", [$productId]);
        if (empty($images)) {
            $images = [['image_path' => 'assets/images/products/prod_' . $productId . '_1.svg']];
        }

        // Variants
        $variants = Database::fetchAll("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC", [$productId]);

        // Reviews
        $reviews = Database::fetchAll("SELECT * FROM reviews WHERE product_id = ? AND is_approved = 1 ORDER BY id DESC", [$productId]);

        // Related Products from same category
        $relatedProducts = Database::fetchAll("
            SELECT p.*, c.name AS category_name,
                   (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
            LIMIT 4
        ", [$product['category_id'], $productId]);
    }
}

if (!$product) {
    header('Location: ' . BASE_URL . '/shop.php');
    exit;
}

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Security validation failed. Please try again.');
    } else {
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $name = sanitizeInput($_POST['customer_name'] ?? '');
        $email = filter_var(trim($_POST['customer_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $title = sanitizeInput($_POST['review_title'] ?? '');
        $comment = sanitizeInput($_POST['review_comment'] ?? '');
        $userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;

        if ($name && $email && $comment) {
            Database::insert("INSERT INTO reviews (product_id, user_id, customer_name, customer_email, rating, title, comment, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, 1)", [
                $product['id'], $userId, $name, $email, $rating, $title, $comment
            ]);

            // Recalculate average rating
            $stats = Database::fetchOne("SELECT AVG(rating) AS avg_rate, COUNT(*) AS total_count FROM reviews WHERE product_id = ? AND is_approved = 1", [$product['id']]);
            Database::execute("UPDATE products SET rating_avg = ?, reviews_count = ? WHERE id = ?", [
                round($stats['avg_rate'], 2), (int)$stats['total_count'], $product['id']
            ]);

            setFlash('success', 'Thank you for your beautiful review! ✨');
            header('Location: ' . BASE_URL . '/product.php?slug=' . urlencode($product['slug']) . '#reviews');
            exit;
        } else {
            setFlash('danger', 'Please fill out all review fields.');
        }
    }
}

$pageTitle = $product['name'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 30px; margin-bottom: 80px;">
    
    <!-- Breadcrumb -->
    <nav style="font-size: 13px; color: #8c767b; margin-bottom: 24px;">
        <a href="<?= BASE_URL ?>/index.php" style="color: inherit;">Home</a> &nbsp;/&nbsp;
        <a href="<?= BASE_URL ?>/shop.php" style="color: inherit;">Shop</a> &nbsp;/&nbsp;
        <a href="<?= BASE_URL ?>/category.php?slug=<?= urlencode($product['category_slug']) ?>" style="color: inherit;"><?= htmlspecialchars($product['category_name']) ?></a> &nbsp;/&nbsp;
        <span style="color: var(--chocolate); font-weight: 500;"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- Product Detail Layout: Gallery + Info -->
    <div style="display:grid; grid-template-columns: 1.1fr 1fr; gap:50px; align-items:start; margin-bottom:60px;">
        
        <!-- Gallery Section -->
        <div>
            <!-- Main Featured Image -->
            <div style="background:#fbf5f7; border-radius:var(--radius-lg); overflow:hidden; border:1px solid var(--border-color); box-shadow:var(--shadow-subtle); aspect-ratio:4/5; margin-bottom:16px;">
                <img id="mainProductImg" src="<?= BASE_URL ?>/<?= htmlspecialchars($images[0]['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
            </div>

            <!-- Thumbnails if multiple -->
            <?php if (count($images) > 1): ?>
                <div style="display:flex; gap:12px;">
                    <?php foreach ($images as $img): ?>
                        <div onclick="document.getElementById('mainProductImg').src = '<?= BASE_URL ?>/<?= htmlspecialchars($img['image_path']) ?>'" style="width:80px; height:95px; border-radius:var(--radius-sm); overflow:hidden; border:2px solid var(--border-color); cursor:pointer;">
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($img['image_path']) ?>" style="width:100%; height:100%; object-fit:cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Actions & Buying Column -->
        <div>
            <span class="product-cat"><?= htmlspecialchars($product['category_name']) ?></span>
            <h1 style="font-size:32px; margin-bottom:12px;"><?= htmlspecialchars($product['name']) ?></h1>
            
            <!-- Rating & SKU -->
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:16px; font-size:13px;">
                <div style="color:#E6A23C;">
                    <span>★ <?= number_format($product['rating_avg'], 1) ?></span>
                    <a href="#reviews" style="color:#8c767b; text-decoration:underline; margin-left:4px;">(<?= $product['reviews_count'] ?> client reviews)</a>
                </div>
                <span style="color:#a8979a;">|</span>
                <span style="color:#8c767b;">SKU: <?= htmlspecialchars($product['sku']) ?></span>
            </div>

            <!-- Pricing -->
            <div style="display:flex; align-items:baseline; gap:14px; margin-bottom:24px;">
                <span style="font-size:28px; font-weight:600; color:var(--chocolate);"><?= formatPrice($product['price']) ?></span>
                <?php if (!empty($product['compare_at_price']) && $product['compare_at_price'] > $product['price']): ?>
                    <span style="font-size:18px; color:#a49195; text-decoration:line-through;"><?= formatPrice($product['compare_at_price']) ?></span>
                    <span class="discount-tag">Save <?= round((($product['compare_at_price'] - $product['price']) / $product['compare_at_price']) * 100) ?>%</span>
                <?php endif; ?>
            </div>

            <!-- Short description -->
            <p style="font-size:14.5px; color:#635054; line-height:1.7; margin-bottom:24px;">
                <?= htmlspecialchars($product['short_description'] ?? '') ?>
            </p>

            <form action="<?= BASE_URL ?>/cart.php" method="POST" style="margin-bottom:30px;">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                <!-- Variants Selection -->
                <?php if (!empty($variants)): ?>
                    <div style="margin-bottom:22px;">
                        <label style="display:block; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-bottom:10px;">
                            Select <?= htmlspecialchars($variants[0]['variant_type']) ?>:
                        </label>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <?php foreach ($variants as $idx => $v): ?>
                                <label style="cursor:pointer;">
                                    <input type="radio" name="variant_id" value="<?= $v['id'] ?>" <?= $idx === 0 ? 'checked' : '' ?> style="display:none;" onchange="document.querySelectorAll('.variant-pill').forEach(el => el.classList.remove('selected')); this.parentElement.querySelector('.variant-pill').classList.add('selected');">
                                    <div class="variant-pill <?= $idx === 0 ? 'selected' : '' ?>" style="padding:8px 18px; border:1px solid #d9c4ca; border-radius:var(--radius-full); font-size:13px; font-weight:500; transition:var(--transition-smooth);">
                                        <?= htmlspecialchars($v['variant_value']) ?>
                                        <?php if ($v['additional_price'] > 0): ?>
                                            (+<?= formatPrice($v['additional_price']) ?>)
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Quantity & Add to Bag -->
                <div style="display:flex; gap:16px; align-items:center; margin-bottom:20px; flex-wrap:wrap;">
                    <div class="qty-control">
                        <button type="button" class="qty-btn qty-minus">-</button>
                        <input type="number" name="quantity" value="1" min="1" max="<?= max(1, $product['stock_quantity']) ?>" class="qty-input">
                        <button type="button" class="qty-btn qty-plus">+</button>
                    </div>

                    <button type="submit" class="btn btn-primary" style="flex-grow:1; padding:14px 28px;" <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                        <?= $product['stock_quantity'] > 0 ? 'Add to Shopping Bag &rarr;' : 'Currently Out of Stock' ?>
                    </button>

                    <button type="button" class="btn btn-outline product-wishlist-btn <?= isInWishlist((int)$product['id']) ? 'active' : '' ?>" data-product-id="<?= $product['id'] ?>" style="width:48px; height:48px; padding:0; border-radius:var(--radius-full);" title="Wishlist">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    </button>
                </div>

                <!-- Stock availability message -->
                <div style="font-size:13px; color:#1F7A38; display:flex; align-items:center; gap:6px;">
                    <span>●</span>
                    <?php if ($product['stock_quantity'] > 10): ?>
                        <span>In Stock and ready to dispatch within 24 hours</span>
                    <?php elseif ($product['stock_quantity'] > 0): ?>
                        <span style="color:#B7950B;">Hurry! Only <?= $product['stock_quantity'] ?> pieces remaining in stock</span>
                    <?php else: ?>
                        <span style="color:#C0392B;">Sold out. Join the waitlist below.</span>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Guarantees & Accordion Info -->
            <div style="border-top:1px solid #f2e2e6; padding-top:20px;">
                <details open style="margin-bottom:12px; cursor:pointer;">
                    <summary style="font-size:14px; font-weight:600; color:var(--chocolate); padding:8px 0;">Description & Fit</summary>
                    <div style="font-size:13.5px; color:#685458; line-height:1.7; padding:8px 0;">
                        <?= $product['description'] ?>
                    </div>
                </details>

                <details style="margin-bottom:12px; cursor:pointer;">
                    <summary style="font-size:14px; font-weight:600; color:var(--chocolate); padding:8px 0;">Artisan Materials & Care</summary>
                    <div style="font-size:13.5px; color:#685458; line-height:1.7; padding:8px 0;">
                        <p>Gentle cold hand wash or delicate cycle with mild silk detergent. Dry flat away from direct sunlight to preserve pastel pigments. Steam iron on reverse if necessary.</p>
                    </div>
                </details>

                <details style="margin-bottom:12px; cursor:pointer;">
                    <summary style="font-size:14px; font-weight:600; color:var(--chocolate); padding:8px 0;">Shipping & Returns</summary>
                    <div style="font-size:13.5px; color:#685458; line-height:1.7; padding:8px 0;">
                        <p>Complimentary express shipping on orders over ₹1,499. Easy 7-day doorstep size exchange available across all Indian postal pin codes.</p>
                    </div>
                </details>
            </div>

        </div>

    </div>

    <!-- Customer Reviews Section -->
    <section id="reviews" style="border-top:1px solid var(--border-color); padding-top:50px; margin-top:40px;">
        <div class="section-header">
            <span class="section-tagline">Client Experiences</span>
            <h2 class="section-title">Reviews & Ratings</h2>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1.2fr; gap:40px; margin-bottom:50px;">
            
            <!-- Write a Review Form -->
            <div class="card-box">
                <h3 style="font-size:20px; margin-bottom:16px;">Write a Review</h3>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label class="form-label">Your Rating (Stars):</label>
                        <select name="rating" class="form-control" required>
                            <option value="5">★★★★★ (5/5) — Absolutely Divine</option>
                            <option value="4">★★★★☆ (4/5) — Loved It</option>
                            <option value="3">★★★☆☆ (3/5) — Average</option>
                            <option value="2">★★☆☆☆ (2/5) — Below Expectations</option>
                            <option value="1">★☆☆☆☆ (1/5) — Disappointed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Review Headline:</label>
                        <input type="text" name="review_title" class="form-control" placeholder="e.g. Dreamy fabric & perfect fit!" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Your Name:</label>
                        <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" placeholder="e.g. Sophie M." required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Your Email:</label>
                        <input type="email" name="customer_email" class="form-control" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" placeholder="your.email@example.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Your Thoughts & Experience:</label>
                        <textarea name="review_comment" class="form-control" placeholder="Share your thoughts on the quality, sizing, and aesthetic..." required></textarea>
                    </div>
                    <button type="submit" name="submit_review" value="1" class="btn btn-primary btn-block">
                        Post Your Review &rarr;
                    </button>
                </form>
            </div>

            <!-- Existing Reviews List -->
            <div>
                <?php if (!empty($reviews)): ?>
                    <div style="display:flex; flex-direction:column; gap:20px;">
                        <?php foreach ($reviews as $rev): ?>
                            <div style="background:#FFF; padding:20px; border-radius:var(--radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-subtle);">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                    <div style="color:#E6A23C; font-size:14px;">
                                        <?= str_repeat('★', (int)$rev['rating']) ?>
                                    </div>
                                    <span style="font-size:12px; color:#a28f93;"><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
                                </div>
                                <h4 style="font-size:15px; font-weight:600; color:var(--chocolate); margin-bottom:4px;">
                                    <?= htmlspecialchars($rev['title']) ?>
                                </h4>
                                <p style="font-size:13.5px; color:#604e52; line-height:1.6; margin-bottom:10px;">
                                    <?= htmlspecialchars($rev['comment']) ?>
                                </p>
                                <span style="font-size:12px; font-weight:600; color:var(--dusty-rose);">
                                    <?= htmlspecialchars($rev['customer_name']) ?> &bull; Verified Client
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card-box" style="text-align:center; padding:40px 20px;">
                        <p style="color:#78666a;">Be the first to share your thoughts on this boutique design!</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <section style="margin-top:60px;">
            <div class="section-header">
                <span class="section-tagline">Pair With</span>
                <h2 class="section-title">You May Also Adore</h2>
            </div>
            <div class="product-grid">
                <?php foreach ($relatedProducts as $rel): ?>
                    <div class="product-card">
                        <div class="product-thumb-wrap">
                            <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($rel['slug']) ?>">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($rel['image_path'] ?? 'assets/images/products/prod_1_1.svg') ?>" alt="<?= htmlspecialchars($rel['name']) ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="product-info">
                            <span class="product-cat"><?= htmlspecialchars($rel['category_name']) ?></span>
                            <h3 class="product-name">
                                <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($rel['slug']) ?>"><?= htmlspecialchars($rel['name']) ?></a>
                            </h3>
                            <div class="product-pricing">
                                <span class="current-price"><?= formatPrice($rel['price']) ?></span>
                            </div>
                            <div style="margin-top:14px;">
                                <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($rel['slug']) ?>" class="btn btn-outline btn-sm btn-block">View Piece &rarr;</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<style>
.variant-pill.selected {
    background-color: var(--chocolate);
    color: var(--white);
    border-color: var(--chocolate);
}
@media (max-width: 860px) {
    div[style*="grid-template-columns: 1.1fr 1fr"],
    div[style*="grid-template-columns:1fr 1.2fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
