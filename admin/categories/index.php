<?php
/**
 * CHÉRIE — Category Management
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';

$adminTitle = 'Manage Categories';
require_once dirname(__DIR__) . '/includes/header.php';

$categories = [];
if (Database::isConnected()) {
    $categories = Database::fetchAll("
        SELECT c.*, COUNT(p.id) AS product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        GROUP BY c.id
        ORDER BY c.sort_order ASC, c.name ASC
    ");
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h1 style="font-size:24px; margin-bottom:4px;">Product Categories (<?= count($categories) ?>)</h1>
        <p style="font-size:13px; color:#78666a;">Organize your boutique aesthetics and storefront navigation.</p>
    </div>
    <div>
        <a href="<?= ADMIN_URL ?>/categories/add.php" class="btn btn-primary btn-sm">+ Add New Category</a>
    </div>
</div>

<div class="admin-card" style="padding:0; overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width:60px;">Icon</th>
                <th>Category Name</th>
                <th>Slug</th>
                <th>Products</th>
                <th>Featured</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($cat['image_url'] ?? 'assets/images/categories/cat_dresses.svg') ?>" style="width:36px; height:44px; object-fit:cover; border-radius:4px; background:#f5e8ec;">
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($cat['name']) ?></strong>
                        <div style="font-size:12px; color:#8c767b;"><?= htmlspecialchars(substr($cat['description'] ?? '', 0, 60)) ?>...</div>
                    </td>
                    <td><code><?= htmlspecialchars($cat['slug']) ?></code></td>
                    <td>
                        <a href="<?= ADMIN_URL ?>/products/index.php?category=<?= $cat['id'] ?>" style="color:var(--admin-primary); font-weight:600;">
                            <?= $cat['product_count'] ?> pieces &rarr;
                        </a>
                    </td>
                    <td>
                        <span class="status-pill <?= $cat['is_featured'] ? 'status-delivered' : 'status-pending' ?>">
                            <?= $cat['is_featured'] ? 'Yes' : 'No' ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-pill <?= $cat['is_active'] ? 'status-delivered' : 'status-cancelled' ?>">
                            <?= $cat['is_active'] ? 'Active' : 'Disabled' ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:8px;">
                            <a href="<?= ADMIN_URL ?>/categories/edit.php?id=<?= $cat['id'] ?>" class="btn btn-outline btn-sm" style="padding:4px 10px; font-size:12px;">Edit</a>
                            <?php if ($cat['product_count'] == 0): ?>
                                <form method="POST" action="<?= ADMIN_URL ?>/categories/delete.php" style="display:inline;" onsubmit="return confirm('Delete this empty category?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm" style="padding:4px 10px; font-size:12px; color:#C0392B;">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
