<?php
/**
 * CHÉRIE — Add Product & Multi-Image Upload
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';

$adminTitle = 'Add New Boutique Piece';
require_once dirname(__DIR__) . '/includes/header.php';

$categories = [];
if (Database::isConnected()) {
    $categories = Database::fetchAll("SELECT id, name FROM categories ORDER BY name ASC");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token validation failed.';
    }

    $categoryId = (int)($_POST['category_id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $sku = strtoupper(trim(sanitizeInput($_POST['sku'] ?? '')));
    $price = (float)($_POST['price'] ?? 0);
    $compareAtPrice = !empty($_POST['compare_at_price']) ? (float)$_POST['compare_at_price'] : null;
    $stockQuantity = max(0, (int)($_POST['stock_quantity'] ?? 0));
    $lowStockThreshold = max(1, (int)($_POST['low_stock_threshold'] ?? 5));
    $shortDesc = sanitizeInput($_POST['short_description'] ?? '');
    $description = $_POST['description'] ?? '';
    $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
    $isNew = !empty($_POST['is_new']) ? 1 : 0;
    $isActive = !empty($_POST['is_active']) ? 1 : 0;

    if (!$categoryId || !$name || !$sku || $price <= 0) {
        $errors[] = 'Please provide Category, Product Name, unique SKU, and valid Price.';
    }

    // Check SKU uniqueness
    if (Database::isConnected() && $sku) {
        $existingSku = Database::fetchOne("SELECT id FROM products WHERE sku = ? LIMIT 1", [$sku]);
        if ($existingSku) {
            $errors[] = "SKU '{$sku}' is already in use by another piece.";
        }
    }

    if (empty($errors) && Database::isConnected()) {
        $slug = slugify($name);
        // Ensure slug is unique
        $slugCheck = Database::fetchOne("SELECT id FROM products WHERE slug = ? LIMIT 1", [$slug]);
        if ($slugCheck) {
            $slug .= '-' . bin2hex(random_bytes(2));
        }

        $productId = Database::insert("
            INSERT INTO products (
                category_id, name, slug, sku, description, short_description,
                price, compare_at_price, stock_quantity, low_stock_threshold,
                is_featured, is_new, is_active
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?
            )
        ", [
            $categoryId, $name, $slug, $sku, $description, $shortDesc,
            $price, $compareAtPrice, $stockQuantity, $lowStockThreshold,
            $isFeatured, $isNew, $isActive
        ]);

        if ($productId) {
            // Process Multiple Image Uploads securely
            if (!empty($_FILES['product_images']['name'][0])) {
                $fileCount = count($_FILES['product_images']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    $singleFile = [
                        'name' => $_FILES['product_images']['name'][$i],
                        'type' => $_FILES['product_images']['type'][$i],
                        'tmp_name' => $_FILES['product_images']['tmp_name'][$i],
                        'error' => $_FILES['product_images']['error'][$i],
                        'size' => $_FILES['product_images']['size'][$i]
                    ];

                    $uploadResult = uploadProductImage($singleFile);
                    if ($uploadResult['success']) {
                        $isPrimary = ($i === 0) ? 1 : 0;
                        Database::insert("
                            INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
                            VALUES (?, ?, ?, ?)
                        ", [$productId, $uploadResult['relative_path'], $isPrimary, $i + 1]);
                    }
                }
            } else {
                // Assign a fallback beautiful SVG card
                Database::insert("
                    INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
                    VALUES (?, ?, 1, 1)
                ", [$productId, 'assets/images/products/prod_1_1.svg']);
            }

            setFlash('success', "Boutique piece '{$name}' created successfully!");
            header('Location: ' . ADMIN_URL . '/products/index.php');
            exit;
        } else {
            $errors[] = 'Database insertion error.';
        }
    }
}
?>

<div style="margin-bottom:24px;">
    <a href="<?= ADMIN_URL ?>/products/index.php" style="font-size:13px; color:var(--admin-primary);">&larr; Back to Products</a>
    <h1 style="font-size:24px; margin-top:6px;">Add New Boutique Piece</h1>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul style="margin-left:20px;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:30px; align-items:start;">
        
        <!-- Left: Product Core Data -->
        <div>
            <div class="admin-card">
                <h3 style="font-size:17px; margin-bottom:16px;">Core Information</h3>

                <div class="form-group">
                    <label class="form-label">Piece Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="e.g. Juliette Ruffled Organza Blouse" required autofocus>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unique SKU *</label>
                        <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>" placeholder="e.g. DRS-008" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Short Tagline Description</label>
                    <input type="text" name="short_description" class="form-control" value="<?= htmlspecialchars($_POST['short_description'] ?? '') ?>" placeholder="A dreamy summary for product cards...">
                </div>

                <div class="form-group">
                    <label class="form-label">Detailed Description (HTML Supported)</label>
                    <textarea name="description" class="form-control" style="min-height:160px;" placeholder="Describe the silhouette, fabric blend, artisan origin, and styling tips..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Image Upload Section -->
            <div class="admin-card">
                <h3 style="font-size:17px; margin-bottom:12px;">Product Photography & Images</h3>
                <p style="font-size:13px; color:#78666a; margin-bottom:16px;">
                    Upload high-resolution photography (JPG, PNG, WebP up to 4MB each). The first image becomes the primary storefront showcase.
                </p>

                <div class="form-group">
                    <input type="file" name="product_images[]" id="productImageInput" class="form-control" multiple accept="image/jpeg,image/png,image/webp">
                </div>

                <!-- Live Client-side Image Previewer -->
                <div id="imagePreviewContainer" class="preview-gallery"></div>
            </div>
        </div>

        <!-- Right: Pricing, Inventory & Visibility -->
        <div>
            <div class="admin-card">
                <h3 style="font-size:17px; margin-bottom:16px;">Pricing & Inventory</h3>

                <div class="form-group">
                    <label class="form-label">Retail Price (INR ₹) *</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" placeholder="2499.00" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Compare-at Price (Strike-through Discount)</label>
                    <input type="number" step="0.01" name="compare_at_price" class="form-control" value="<?= htmlspecialchars($_POST['compare_at_price'] ?? '') ?>" placeholder="2999.00">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Stock Units *</label>
                        <input type="number" name="stock_quantity" class="form-control" value="<?= htmlspecialchars($_POST['stock_quantity'] ?? '25') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Low Stock Alert *</label>
                        <input type="number" name="low_stock_threshold" class="form-control" value="<?= htmlspecialchars($_POST['low_stock_threshold'] ?? '5') ?>" required>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <h3 style="font-size:17px; margin-bottom:16px;">Visibility Flags</h3>

                <label style="display:flex; align-items:center; gap:10px; margin-bottom:14px; cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span style="font-size:13.5px; font-weight:500;">Active in Boutique Storefront</span>
                </label>

                <label style="display:flex; align-items:center; gap:10px; margin-bottom:14px; cursor:pointer;">
                    <input type="checkbox" name="is_featured" value="1">
                    <span style="font-size:13.5px; font-weight:500;">Showcase on Homepage (Featured)</span>
                </label>

                <label style="display:flex; align-items:center; gap:10px; margin-bottom:20px; cursor:pointer;">
                    <input type="checkbox" name="is_new" value="1" checked>
                    <span style="font-size:13.5px; font-weight:500;">Display 'NEW' Badge</span>
                </label>

                <button type="submit" class="btn btn-primary btn-block" style="padding:14px;">
                    Save & Publish Piece &rarr;
                </button>
            </div>
        </div>

    </div>
</form>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
