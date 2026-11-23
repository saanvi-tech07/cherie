<?php
/**
 * CHÉRIE — Admin Layout Header
 */
if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}

require_once __DIR__ . '/auth.php';
$adminTitle = isset($adminTitle) ? $adminTitle . ' — CHÉRIE Administration' : 'CHÉRIE Administration';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">

<?php require_once __DIR__ . '/sidebar.php'; ?>

<div class="admin-main">
    <header class="admin-topbar">
        <div>
            <span style="font-size:13px; color:#8c767b;">Administration Panel</span>
        </div>
        <div style="display:flex; align-items:center; gap:16px;">
            <div style="font-size:13.5px; text-align:right;">
                <strong><?= htmlspecialchars($currentAdmin['full_name']) ?></strong><br>
                <span style="font-size:11.5px; color:#a38f93; text-transform:uppercase;"><?= htmlspecialchars($currentAdmin['role']) ?></span>
            </div>
            <div style="width:38px; height:38px; border-radius:50%; background:var(--admin-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600;">
                <?= strtoupper(substr($currentAdmin['username'], 0, 1)) ?>
            </div>
        </div>
    </header>

    <div class="admin-content">
        <?php require_once dirname(__DIR__, 2) . '/includes/alerts.php'; ?>
