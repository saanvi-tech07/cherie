<?php
/**
 * CHÉRIE — Admin Orders Management
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';

$adminTitle = 'Manage Client Orders';
require_once dirname(__DIR__) . '/includes/header.php';

$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['search'] ?? '');

$whereClauses = ["1=1"];
$params = [];

if ($statusFilter) {
    $whereClauses[] = "order_status = ?";
    $params[] = $statusFilter;
}

if ($search) {
    $whereClauses[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = implode(' AND ', $whereClauses);

$orders = [];
if (Database::isConnected()) {
    $orders = Database::fetchAll("SELECT * FROM orders WHERE $whereSql ORDER BY id DESC", $params);
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
    <div>
        <h1 style="font-size:24px; margin-bottom:4px;">Customer Orders (<?= count($orders) ?>)</h1>
        <p style="font-size:13px; color:#78666a;">Track shipments, update statuses, and view transaction records.</p>
    </div>
</div>

<!-- Filter Bar -->
<div class="admin-card" style="padding:16px 20px; margin-bottom:24px;">
    <form method="GET" style="display:flex; gap:14px; flex-wrap:wrap; align-items:center;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search Order # or Client..." class="form-control" style="width:260px; font-size:13px; padding:8px 12px;">

        <select name="status" class="form-control" style="width:180px; font-size:13px; padding:8px 12px;">
            <option value="">All Statuses</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="processing" <?= $statusFilter === 'processing' ? 'selected' : '' ?>>Processing</option>
            <option value="shipped" <?= $statusFilter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
            <option value="delivered" <?= $statusFilter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
            <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <button type="submit" class="btn btn-dark btn-sm">Filter</button>
        <?php if ($search || $statusFilter): ?>
            <a href="<?= ADMIN_URL ?>/orders/index.php" style="font-size:13px; color:var(--admin-primary); margin-left:8px;">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Orders Table -->
<div class="admin-card" style="padding:0; overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Client</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Delivery Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $ord): 
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
                            <strong>#<?= htmlspecialchars($ord['order_number']) ?></strong>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($ord['customer_name']) ?></div>
                            <small style="color:#8c767b;"><?= htmlspecialchars($ord['customer_email']) ?> &bull; <?= htmlspecialchars($ord['city']) ?></small>
                        </td>
                        <td><?= date('d M Y', strtotime($ord['created_at'])) ?></td>
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
                                Manage Order &rarr;
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:40px; color:#8c767b;">No orders match criteria.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
