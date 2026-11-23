<?php
/**
 * CHÉRIE — Edit Product & Manage Images
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';

$productId = (int)($_GET['id'] ?? 0);
$product = null;

if (Database::isConnected() && $productId) {
    $product = Database::fetchOne("SELECT * FROM products WHERE id = ?", [$productId]);
}

if (!$product) {
    setFlash('danger', 'Product not found.');
    header('Location: ' . ADMIN_URL . '/products/index.php');
    exit;
}

$adminTitle = 'Edit Piece: ' . $product['name'];
require_once dirname(__DIR__) . '/includes/header.php';

$categories = Database::fetchAll("SELECT id, name FROM categories ORDER BY name ASC");
$images = Database::fetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC", [$productId]);
$variants = Database::fetchAll("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC", [$productId]);

$errors = [];

// Handle Image Set Primary / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_image'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $imageId = (int)($_POST['image_id'] ?? 0);
        if ($_POST['action_image'] === 'set_primary') {
            Database::execute("UPDATE product_images SET is_primary = 0 WHERE product_id = ?", [$productId]);
            Database::execute("UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?", [$imageId, $productId]);
            setFlash('success', 'Primary showcase image updated.');
        } elseif ($_POST['action_image'] === 'delete') {
            Database::execute("DELETE FROM product_images WHERE id = ? AND product_id = ?", [$imageId, $productId]);
            setFlash('info', 'Image removed.');
        }
    }
    header('Location: ' . ADMIN_URL . '/products/edit.php?id=' . $productId);
    exit;
}

// Handle Add Variant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_variant'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $type = sanitizeInput($_POST['variant_type'] ?? 'Size');
        $value = sanitizeInput($_POST['variant_value'] ?? '');
        $addPrice = (float)($_POST['additional_price'] ?? 0.0);
        $vStock = (int)($_POST['variant_stock'] ?? 10);
        $vSku = sanitizeInput($_POST['variant_sku'] ?? '');

        if ($value) {
            Database::insert("INSERT INTO product_variants (product_id, variant_type, variant_value, additional_price, stock_quantity, sku) VALUES (?, ?, ?, ?, ?, ?)", [
                $productId, $type, $value, $addPrice, $vStock, $vSku
            ]);
            setFlash('success', 'Variant added.');
        }
    }
    header('Location: ' . ADMIN_URL . '/products/edit.php?id=' . $productId);
    exit;
}

// Handle Delete Variant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_variant'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $vId = (int)($_POST['variant_id'] ?? 0);
        Database::execute("DELETE FROM product_variants WHERE id = ? AND product_id = ?", [$vId, $productId]);
        setFlash('info', 'Variant removed.');
    }
    header('Location: ' . ADMIN_URL . '/products/edit.php?id=' . $productId);
    exit;
}

// Handle Core Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
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

    // Check SKU collision with other products
    $skuCheck = Database::fetchOne("SELECT id FROM products WHERE sku = ? AND id != ? LIMIT 1", [$sku, $productId]);
    if ($skuCheck) {
        $errors[] = "SKU '{$sku}' is already used by another piece.";
    }

    if (empty($errors)) {
        Database::execute("
            UPDATE products SET
                category_id = ?, name = ?, sku = ?, description = ?, short_description = ?,
                price = ?, compare_at_price = ?, stock_quantity = ?, low_stock_threshold = ?,
                is_featured = ?, is_new = ?, is_active = ?
            WHERE id = ?
        ", [
            $categoryId, $name, $sku, $description, $shortDesc,
            $price, $compareAtPrice, $stockQuantity, $lowStockThreshold,
            $isFeatured, $isNew, $isActive, $productId
        ]);

        // Process Additional Images Uploads
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
                    Database::insert("
                        INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
                        VALUES (?, ?, 0, 99)
                    ", [$productId, $uploadResult['relative_path']]);
                }
            }
        }

        setFlash('success', 'Piece details updated successfully!');
        header('Location: ' . ADMIN_URL . '/products/edit.php?id=' . $productId);
        exit;
    }
}
?>

<div style="margin-bottom:24px;">
    <a href="<?= ADMIN_URL ?>/products/index.php" style="font-size:13px; color:var(--admin-primary);">&larr; Back to Products</a>
    <h1 style="font-size:24px; margin-top:6px;">Edit Piece: <?= htmlspecialchars($product['name']) ?></h1>
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

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:30px; align-items:start;">
    
    <!-- Main Form -->
    <div>
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="update_product" value="1">

            <div class="admin-card">
                <h3 style="font-size:17px; margin-bottom:16px;">Core Details</h3>

                <div class="form-group">
                    <label class="form-label">Piece Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unique SKU *</label>
                        <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Short Tagline Description</label>
                    <input type="text" name="short_description" class="form-control" value="<?= htmlspecialchars($product['short_description'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Detailed Description (HTML Supported)</label>
                    <textarea name="description" class="form-control" style="min-height:160px;"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:14px; margin-top:20px;">
                    <div class="form-group">
                        <label class="form-label">Price (INR) *</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Compare-at Price</label>
                        <input type="number" step="0.01" name="compare_at_price" class="form-control" value="<?= $product['compare_at_price'] ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Units *</label>
                        <input type="number" name="stock_quantity" class="form-control" value="<?= $product['stock_quantity'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Low Stock Alert *</label>
                        <input type="number" name="low_stock_threshold" class="form-control" value="<?= $product['low_stock_threshold'] ?>" required>
                    </div>
                </div>

                <div style="display:flex; gap:20px; margin-top:14px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" <?= $product['is_active'] ? 'checked' : '' ?>>
                        <span style="font-size:13px;">Active</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>>
                        <span style="font-size:13px;">Featured</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_new" value="1" <?= $product['is_new'] ? 'checked' : '' ?>>
                        <span style="font-size:13px;">New Arrival</span>
                    </label>
                </div>

                <div style="margin-top:24px;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>

            <!-- Upload More Images -->
            <div class="admin-card">
                <h3 style="font-size:17px; margin-bottom:12px;">Upload Additional Images</h3>
                <div class="form-group">
                    <input type="file" name="product_images[]" id="productImageInput" class="form-control" multiple accept="image/jpeg,image/png,image/webp">
                </div>
                <div id="imagePreviewContainer" class="preview-gallery"></div>
                <button type="submit" class="btn btn-dark btn-sm" style="margin-top:12px;">Upload Selected Files</button>
            </div>
        </form>

        <!-- Variants Management -->
        <div class="admin-card">
            <h3 style="font-size:17px; margin-bottom:14px;">Product Variants (Sizes / Shades)</h3>
            
            <?php if (!empty($variants)): ?>
                <table class="admin-table" style="margin-bottom:20px;">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Additional Price</th>
                            <th>Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($variants as $v): ?>
                            <tr>
                                <td><?= htmlspecialchars($v['variant_type']) ?></td>
                                <td><strong><?= htmlspecialchars($v['variant_value']) ?></strong></td>
                                <td>+<?= formatPrice($v['additional_price']) ?></td>
                                <td><?= $v['stock_quantity'] ?> units</td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Remove variant?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="delete_variant" value="1">
                                        <input type="hidden" name="variant_id" value="<?= $v['id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm" style="padding:2px 8px; font-size:11px; color:#C0392B;">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Add Variant Form -->
            <form method="POST" style="background:#FAF5F6; padding:16px; border-radius:8px; display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
                <?= csrfField() ?>
                <input type="hidden" name="add_variant" value="1">

                <div style="flex:1; min-width:110px;">
                    <label class="form-label" style="font-size:11px;">Type</label>
                    <select name="variant_type" class="form-control" style="padding:6px; font-size:12px;">
                        <option value="Size">Size</option>
                        <option value="Shade">Shade</option>
                        <option value="Color">Color</option>
                    </select>
                </div>

                <div style="flex:1.5; min-width:120px;">
                    <label class="form-label" style="font-size:11px;">Value</label>
                    <input type="text" name="variant_value" class="form-control" placeholder="e.g. XL or Blush Pink" style="padding:6px; font-size:12px;" required>
                </div>

                <div style="flex:1; min-width:90px;">
                    <label class="form-label" style="font-size:11px;">Price (+₹)</label>
                    <input type="number" step="0.01" name="additional_price" value="0.00" class="form-control" style="padding:6px; font-size:12px;">
                </div>

                <div style="flex:1; min-width:80px;">
                    <label class="form-label" style="font-size:11px;">Stock</label>
                    <input type="number" name="variant_stock" value="15" class="form-control" style="padding:6px; font-size:12px;">
                </div>

                <button type="submit" class="btn btn-dark btn-sm" style="padding:8px 14px; font-size:12px;">+ Add Variant</button>
            </form>
        </div>
    </div>

    <!-- Right: Gallery Images Control -->
    <div>
        <div class="admin-card">
            <h3 style="font-size:17px; margin-bottom:14px;">Existing Images (<?= count($images) ?>)</h3>
            
            <div style="display:flex; flex-direction:column; gap:16px;">
                <?php foreach ($images as $img): ?>
                    <div style="display:flex; gap:14px; align-items:center; border:1px solid var(--admin-border); padding:10px; border-radius:8px; background:<?= $img['is_primary'] ? '#FBF2F4' : '#FFF' ?>;">
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($img['image_path']) ?>" style="width:65px; height:80px; object-fit:cover; border-radius:6px;">
                        
                        <div style="flex-grow:1;">
                            <?php if ($img['is_primary']): ?>
                                <span style="background:var(--admin-primary); color:#FFF; font-size:10px; font-weight:600; padding:2px 6px; border-radius:4px; display:inline-block; margin-bottom:6px;">PRIMARY</span>
                            <?php else: ?>
                                <form method="POST" style="margin-bottom:6px;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action_image" value="set_primary">
                                    <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm" style="padding:2px 8px; font-size:11px;">Set as Primary</button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" onsubmit="return confirm('Delete this image?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action_image" value="delete">
                                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                <button type="submit" style="background:none; border:none; color:#C0392B; font-size:11px; cursor:pointer; text-decoration:underline;">Delete Image</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
