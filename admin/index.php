<?php
/**
 * CHÉRIE — Admin Dashboard
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__) . '/config/config.php';

$adminTitle = 'Boutique Dashboard';
require_once __DIR__ . '/includes/header.php';

// Calculate Analytics Metrics
$totalSales = 0.0;
$totalOrders = 0;
$totalProducts = 0;
$totalCustomers = 0;
$lowStockCount = 0;
$recentOrders = [];
$lowStockProducts = [];

if (Database::isConnected()) {
    try {
        $salesRow = Database::fetchOne("SELECT SUM(total_amount) AS total FROM orders WHERE payment_status = 'completed' OR order_status = 'delivered'");
        $totalSales = (float)($salesRow['total'] ?? 0.0);

        $totalOrders = (int)Database::fetchOne("SELECT COUNT(*) AS total FROM orders")['total'];
        $totalProducts = (int)Database::fetchOne("SELECT COUNT(*) AS total FROM products WHERE is_active = 1")['total'];
        $totalCustomers = (int)Database::fetchOne("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'")['total'];
        $lowStockCount = (int)Database::fetchOne("SELECT COUNT(*) AS total FROM products WHERE stock_quantity <= low_stock_threshold AND is_active = 1")['total'];

        $recentOrders = Database::fetchAll("SELECT * FROM orders ORDER BY id DESC LIMIT 6");
        $lowStockProducts = Database::fetchAll("
            SELECT p.*, c.name AS category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.stock_quantity <= p.low_stock_threshold AND p.is_active = 1
            ORDER BY p.stock_quantity ASC
            LIMIT 5
        ");
    } catch (Exception $e) {
        // Fallback
    }
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <div>
        <h1 style="font-size:26px; margin-bottom:4px;">Dashboard Overview</h1>
        <p style="font-size:13.5px; color:#78666a;">Welcome to the CHÉRIE flagship store management console.</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="<?= ADMIN_URL ?>/products/add.php" class="btn btn-primary btn-sm">+ Add New Piece</a>
        <a href="<?= BASE_URL ?>/xml/catalog_export.php" target="_blank" class="btn btn-outline btn-sm">Export XML Feed 📄</a>
    </div>
</div>

<!-- Metrics Stats Grid -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-icon" style="background:#EBF8EE; color:#1F7A38;">💰</div>
        <div>
            <div class="metric-label">Gross Boutique Revenue</div>
            <div class="metric-value"><?= formatPrice($totalSales) ?></div>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon" style="background:#EBF5FB; color:#2874A6;">📦</div>
        <div>
            <div class="metric-label">Total Orders</div>
            <div class="metric-value"><?= number_format($totalOrders) ?></div>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon" style="background:#F7DCE5; color:#C58A9C;">👗</div>
        <div>
            <div class="metric-label">Curated Pieces</div>
            <div class="metric-value"><?= number_format($totalProducts) ?></div>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon" style="background:#FEF9E7; color:#B7950B;">⚠️</div>
        <div>
            <div class="metric-label">Low Stock Alerts</div>
            <div class="metric-value" style="color:<?= $lowStockCount > 0 ? '#C0392B' : 'inherit' ?>;"><?= $lowStockCount ?></div>
        </div>
    </div>
</div>

<!-- Recent Orders Section -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="font-size:18px;">Recent Customer Orders</h3>
        <a href="<?= ADMIN_URL ?>/orders/index.php" style="font-size:13px; color:var(--admin-primary); font-weight:500;">View All Orders &rarr;</a>
    </div>

    <?php if (!empty($recentOrders)): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $ord): 
                    $statusClass = match($ord['order_status']) {
                        'pending' => 'status-pending',
                        'processing' => 'status-processing',
                        'shipped' => 'status-shipped',
                        'delivered' => 'status-delivered',
                        'cancelled' => 'status-cancelled',
                        default => 'status-pending'
                    };
                ?>
                    <tr>
                        <td>
                            <strong style="color:var(--admin-chocolate);">#<?= htmlspecialchars($ord['order_number']) ?></strong>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($ord['customer_name']) ?></div>
                            <small style="color:#8c767b;"><?= htmlspecialchars($ord['customer_email']) ?></small>
                        </td>
                        <td><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></td>
                        <td><strong><?= formatPrice($ord['total_amount']) ?></strong></td>
                        <td>
                            <span style="font-size:12px; font-weight:600; text-transform:uppercase;">
                                <?= strtoupper($ord['payment_method']) ?>
                            </span>
                            <br><small style="color:#8c767b;"><?= ucfirst($ord['payment_status']) ?></small>
                        </td>
                        <td>
                            <span class="status-pill <?= $statusClass ?>">
                                <?= ucfirst($ord['order_status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= ADMIN_URL ?>/orders/view.php?id=<?= $ord['id'] ?>" class="btn btn-outline btn-sm" style="padding:4px 12px; font-size:12px;">
                                View Details &rarr;
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:center; padding:30px; color:#8c767b;">No orders placed yet.</p>
    <?php endif; ?>
</div>

<!-- Low Stock Warnings -->
<?php if (!empty($lowStockProducts)): ?>
    <div class="admin-card" style="border-left:4px solid #C0392B;">
        <h3 style="font-size:18px; margin-bottom:16px; color:#C0392B;">⚠️ Low Inventory Warnings</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Piece</th>
                    <th>Category</th>
                    <th>SKU</th>
                    <th>Current Stock</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStockProducts as $lp): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($lp['name']) ?></strong></td>
                        <td><?= htmlspecialchars($lp['category_name']) ?></td>
                        <td><code><?= htmlspecialchars($lp['sku']) ?></code></td>
                        <td>
                            <span style="color:#C0392B; font-weight:600;"><?= $lp['stock_quantity'] ?> units</span>
                        </td>
                        <td><?= formatPrice($lp['price']) ?></td>
                        <td>
                            <a href="<?= ADMIN_URL ?>/products/edit.php?id=<?= $lp['id'] ?>" class="btn btn-outline btn-sm" style="padding:4px 10px; font-size:12px;">
                                Restock Piece &rarr;
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
