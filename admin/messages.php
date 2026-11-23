<?php
/**
 * CHÉRIE — Admin Client Messages Management
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__) . '/config/config.php';

// Handle Mark as Read / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $msgId = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if ($action === 'mark_read') {
            Database::execute("UPDATE contact_messages SET is_read = 1 WHERE id = ?", [$msgId]);
            setFlash('success', 'Marked as read.');
        } elseif ($action === 'delete') {
            Database::execute("DELETE FROM contact_messages WHERE id = ?", [$msgId]);
            setFlash('info', 'Message deleted.');
        }
    }
    header('Location: ' . ADMIN_URL . '/messages.php');
    exit;
}

$adminTitle = 'Client Messages & Concierge Notes';
require_once __DIR__ . '/includes/header.php';

$messages = [];
if (Database::isConnected()) {
    $messages = Database::fetchAll("SELECT * FROM contact_messages ORDER BY id DESC");
}
?>

<div style="margin-bottom:24px;">
    <h1 style="font-size:24px; margin-bottom:4px;">Concierge Client Messages (<?= count($messages) ?>)</h1>
    <p style="font-size:13px; color:#78666a;">Inquiries submitted via the storefront Contact form.</p>
</div>

<div class="admin-card" style="padding:0; overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Sender</th>
                <th>Subject</th>
                <th>Message Content</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $m): ?>
                    <tr style="background:<?= $m['is_read'] ? 'inherit' : '#FAF2F4' ?>;">
                        <td>
                            <strong><?= htmlspecialchars($m['name']) ?></strong><br>
                            <small style="color:#8c767b;"><a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a></small>
                            <?php if ($m['phone']): ?><br><small style="color:#8c767b;">📞 <?= htmlspecialchars($m['phone']) ?></small><?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($m['subject']) ?></strong></td>
                        <td style="max-width:340px; font-size:13px; color:#5c494d;">
                            <?= nl2br(htmlspecialchars($m['message'])) ?>
                        </td>
                        <td><?= date('d M Y, h:i A', strtotime($m['created_at'])) ?></td>
                        <td>
                            <span class="status-pill <?= $m['is_read'] ? 'status-delivered' : 'status-pending' ?>">
                                <?= $m['is_read'] ? 'Read' : 'New Note' ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; gap:6px;">
                                <?php if (!$m['is_read']): ?>
                                    <form method="POST">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <input type="hidden" name="action" value="mark_read">
                                        <button type="submit" class="btn btn-outline btn-sm" style="padding:2px 8px; font-size:11px;">Mark Read</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" onsubmit="return confirm('Delete message?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-outline btn-sm" style="padding:2px 8px; font-size:11px; color:#C0392B;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px; color:#8c767b;">No inquiries received yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
