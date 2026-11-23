<?php
/**
 * CHÉRIE — Add Category
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';

$adminTitle = 'Add Category';
require_once dirname(__DIR__) . '/includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token expired.';
    }

    $name = sanitizeInput($_POST['name'] ?? '');
    $slug = slugify($_POST['slug'] ?: $name);
    $description = sanitizeInput($_POST['description'] ?? '');
    $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
    $isActive = !empty($_POST['is_active']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if (!$name) {
        $errors[] = 'Category name is required.';
    } elseif (Database::isConnected()) {
        $existing = Database::fetchOne("SELECT id FROM categories WHERE slug = ? LIMIT 1", [$slug]);
        if ($existing) {
            $errors[] = "A category with slug '{$slug}' already exists.";
        } else {
            $imageUrl = 'assets/images/categories/cat_dresses.svg'; // default fallback

            // Process image upload if provided
            if (!empty($_FILES['category_image']['name'])) {
                $upload = uploadProductImage($_FILES['category_image']);
                if ($upload['success']) {
                    $imageUrl = $upload['relative_path'];
                }
            }

            Database::insert("
                INSERT INTO categories (name, slug, description, image_url, is_featured, is_active, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [$name, $slug, $description, $imageUrl, $isFeatured, $isActive, $sortOrder]);

            setFlash('success', "Category '{$name}' created successfully!");
            header('Location: ' . ADMIN_URL . '/categories/index.php');
            exit;
        }
    }
}
?>

<div style="margin-bottom:24px;">
    <a href="<?= ADMIN_URL ?>/categories/index.php" style="font-size:13px; color:var(--admin-primary);">&larr; Back to Categories</a>
    <h1 style="font-size:24px; margin-top:6px;">Add New Category</h1>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="admin-card" style="max-width:650px;">
    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        
        <div class="form-group">
            <label class="form-label">Category Name *</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="e.g. Silk Loungewear" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label">URL Slug (leave blank to auto-generate)</label>
            <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" placeholder="e.g. silk-loungewear">
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" style="min-height:90px;" placeholder="Describe the aesthetic and pieces included in this collection..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Category Cover Image (Optional)</label>
            <input type="file" name="category_image" class="form-control" accept="image/jpeg,image/png,image/webp">
        </div>

        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($_POST['sort_order'] ?? '10') ?>" style="width:120px;">
        </div>

        <div style="display:flex; gap:20px; margin-bottom:24px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="is_featured" value="1" checked>
                <span style="font-size:13.5px;">Showcase on Homepage</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" checked>
                <span style="font-size:13.5px;">Active in Navigation</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Create Category &rarr;</button>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
