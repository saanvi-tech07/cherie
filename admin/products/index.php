<?php
/**
 * CHÉRIE — Admin Product Catalog Management
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';

$adminTitle = 'Manage Boutique Pieces';
require_once dirname(__DIR__) . '/includes/header.php';

$search = trim($_GET['search'] ?? '');
$categoryFilter = !empty($_GET['category']) ? (int)$_GET['category'] : 0;

$whereClauses = ["1=1"];
$params = [];

if ($search) {
    $whereClauses[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryFilter > 0) {
    $whereClauses[] = "p.category_id = ?";
    $params[] = $categoryFilter;
}

$whereSql = implode(' AND ', $whereClauses);

$products = [];
$categories = [];

if (Database::isConnected()) {
    $categories = Database::fetchAll("SELECT id, name FROM categories ORDER BY name ASC");
    $products = Database::fetchAll("
        SELECT p.*, c.name AS category_name,
               (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image_path
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE $whereSql
        ORDER BY p.id DESC
    ", $params);
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
    <div>
        <h1 style="font-size:24px; margin-bottom:4px;">Boutique Pieces (<?= count($products) ?>)</h1>
        <p style="font-size:13px; color:#78666a;">Manage inventory, pricing, multiple images, and descriptions.</p>
    </div>
    <div>
        <a href="<?= ADMIN_URL ?>/products/add.php" class="btn btn-primary btn-sm">+ Add New Piece</a>
    </div>
</div>

<!-- Search & Filters -->
<div class="admin-card" style="padding:16px 20px; margin-bottom:24px;">
    <form method="GET" style="display:flex; gap:14px; flex-wrap:wrap; align-items:center;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or SKU..." class="form-control" style="width:260px; font-size:13px; padding:8px 12px;">

        <select name="category" class="form-control" style="width:200px; font-size:13px; padding:8px 12px;">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoryFilter === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-dark btn-sm">Filter</button>
        <?php if ($search || $categoryFilter): ?>
            <a href="<?= ADMIN_URL ?>/products/index.php" style="font-size:13px; color:var(--admin-primary); margin-left:8px;">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Product Table -->
<div class="admin-card" style="padding:0; overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width:70px;">Image</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($p['image_path'] ?? 'assets/images/products/prod_1_1.svg') ?>" style="width:48px; height:60px; object-fit:cover; border-radius:6px; background:#f5e8ec;">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($p['name']) ?></strong>
                            <?php if ($p['is_featured']): ?>
                                <span style="background:#F7DCE5; color:#49383C; font-size:10px; padding:2px 6px; border-radius:4px; margin-left:6px; font-weight:600;">FEATURED</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['category_name']) ?></td>
                        <td><code><?= htmlspecialchars($p['sku']) ?></code></td>
                        <td><strong><?= formatPrice($p['price']) ?></strong></td>
                        <td>
                            <span style="font-weight:600; color:<?= $p['stock_quantity'] <= $p['low_stock_threshold'] ? '#C0392B' : '#1F7A38' ?>;">
                                <?= $p['stock_quantity'] ?> in stock
                            </span>
                        </td>
                        <td>
                            <span class="status-pill <?= $p['is_active'] ? 'status-delivered' : 'status-cancelled' ?>">
                                <?= $p['is_active'] ? 'Active' : 'Draft' ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; gap:8px;">
                                <a href="<?= ADMIN_URL ?>/products/edit.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="padding:4px 10px; font-size:12px;">Edit</a>
                                <form method="POST" action="<?= ADMIN_URL ?>/products/delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this piece?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm" style="padding:4px 10px; font-size:12px; color:#C0392B; border-color:#F5B7B1;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:40px; color:#8c767b;">No pieces found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
