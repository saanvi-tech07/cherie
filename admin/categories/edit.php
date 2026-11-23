<?php
/**
 * CHÉRIE — Edit Category
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';

$categoryId = (int)($_GET['id'] ?? 0);
$category = null;

if (Database::isConnected() && $categoryId) {
    $category = Database::fetchOne("SELECT * FROM categories WHERE id = ?", [$categoryId]);
}

if (!$category) {
    setFlash('danger', 'Category not found.');
    header('Location: ' . ADMIN_URL . '/categories/index.php');
    exit;
}

$adminTitle = 'Edit Category: ' . $category['name'];
require_once dirname(__DIR__) . '/includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token validation failed.';
    }

    $name = sanitizeInput($_POST['name'] ?? '');
    $slug = slugify($_POST['slug'] ?: $name);
    $description = sanitizeInput($_POST['description'] ?? '');
    $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
    $isActive = !empty($_POST['is_active']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if (!$name) {
        $errors[] = 'Name is required.';
    } else {
        $existing = Database::fetchOne("SELECT id FROM categories WHERE slug = ? AND id != ? LIMIT 1", [$slug, $categoryId]);
        if ($existing) {
            $errors[] = "Slug '{$slug}' is used by another category.";
        } else {
            $imageUrl = $category['image_url'];

            if (!empty($_FILES['category_image']['name'])) {
                $upload = uploadProductImage($_FILES['category_image']);
                if ($upload['success']) {
                    $imageUrl = $upload['relative_path'];
                }
            }

            Database::execute("
                UPDATE categories 
                SET name = ?, slug = ?, description = ?, image_url = ?, is_featured = ?, is_active = ?, sort_order = ?
                WHERE id = ?
            ", [$name, $slug, $description, $imageUrl, $isFeatured, $isActive, $sortOrder, $categoryId]);

            setFlash('success', 'Category updated successfully!');
            header('Location: ' . ADMIN_URL . '/categories/index.php');
            exit;
        }
    }
}
?>

<div style="margin-bottom:24px;">
    <a href="<?= ADMIN_URL ?>/categories/index.php" style="font-size:13px; color:var(--admin-primary);">&larr; Back to Categories</a>
    <h1 style="font-size:24px; margin-top:6px;">Edit Category: <?= htmlspecialchars($category['name']) ?></h1>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<div class="admin-card" style="max-width:650px;">
    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        
        <div class="form-group">
            <label class="form-label">Category Name *</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label">URL Slug</label>
            <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($category['slug']) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" style="min-height:90px;"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Update Cover Image</label>
            <?php if ($category['image_url']): ?>
                <div style="margin-bottom:8px;">
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($category['image_url']) ?>" style="height:60px; border-radius:6px;">
                </div>
            <?php endif; ?>
            <input type="file" name="category_image" class="form-control" accept="image/jpeg,image/png,image/webp">
        </div>

        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="<?= $category['sort_order'] ?>" style="width:120px;">
        </div>

        <div style="display:flex; gap:20px; margin-bottom:24px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="is_featured" value="1" <?= $category['is_featured'] ? 'checked' : '' ?>>
                <span style="font-size:13.5px;">Showcase on Homepage</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" <?= $category['is_active'] ? 'checked' : '' ?>>
                <span style="font-size:13.5px;">Active</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes &rarr;</button>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
