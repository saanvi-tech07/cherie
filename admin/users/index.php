<?php
/**
 * CHÉRIE — Admin Customers Management
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';

$adminTitle = 'Boutique Client List';
require_once dirname(__DIR__) . '/includes/header.php';

$users = [];
if (Database::isConnected()) {
    $users = Database::fetchAll("
        SELECT u.*, 
               COUNT(o.id) AS total_orders, 
               COALESCE(SUM(o.total_amount), 0) AS total_spent
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        WHERE u.role = 'customer'
        GROUP BY u.id
        ORDER BY u.id DESC
    ");
}
?>

<div style="margin-bottom:24px;">
    <h1 style="font-size:24px; margin-bottom:4px;">Registered Clients (<?= count($users) ?>)</h1>
    <p style="font-size:13px; color:#78666a;">View registered customer accounts, lifetime order history, and contact details.</p>
</div>

<div class="admin-card" style="padding:0; overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>City / State</th>
                <th>Orders</th>
                <th>Total Spent</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($u['full_name']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
                        <td><?= htmlspecialchars(($u['city'] ? $u['city'] . ', ' : '') . ($u['state'] ?: '—')) ?></td>
                        <td>
                            <strong><?= $u['total_orders'] ?></strong>
                        </td>
                        <td>
                            <strong><?= formatPrice($u['total_spent']) ?></strong>
                        </td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:40px; color:#8c767b;">No registered customers yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
