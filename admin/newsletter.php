<?php
/**
 * CHÉRIE — Admin Newsletter Subscribers Management
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';

// Handle Unsubscribe / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $subId = (int)($_POST['id'] ?? 0);
        Database::execute("DELETE FROM newsletter_subscribers WHERE id = ?", [$subId]);
        setFlash('info', 'Subscriber removed.');
    }
    header('Location: ' . ADMIN_URL . '/newsletter.php');
    exit;
}

$adminTitle = 'Newsletter Subscribers';
require_once __DIR__ . '/includes/header.php';

$subscribers = [];
if (Database::isConnected()) {
    $subscribers = Database::fetchAll("SELECT * FROM newsletter_subscribers ORDER BY id DESC");
}
?>

<div style="margin-bottom:24px;">
    <h1 style="font-size:24px; margin-bottom:4px;">Newsletter Circle (<?= count($subscribers) ?>)</h1>
    <p style="font-size:13px; color:#78666a;">Clients subscribed to CHÉRIE editorial collections and seasonal releases.</p>
</div>

<div class="admin-card" style="padding:0; max-width:700px; overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Subscriber Email</th>
                <th>Joined On</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($subscribers)): ?>
                <?php foreach ($subscribers as $s): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['email']) ?></strong></td>
                        <td><?= date('d M Y, h:i A', strtotime($s['subscribed_at'])) ?></td>
                        <td>
                            <span class="status-pill status-delivered">Active</span>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Remove subscriber?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-outline btn-sm" style="padding:2px 8px; font-size:11px; color:#C0392B;">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding:30px; color:#8c767b;">No subscribers yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
