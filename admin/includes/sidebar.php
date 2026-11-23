<?php
/**
 * CHÉRIE — Admin Navigation Sidebar
 */
if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}

$currentScript = basename($_SERVER['SCRIPT_NAME']);
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
?>

<aside class="admin-sidebar">
    <div class="admin-sidebar-header">
        <h2 class="admin-logo-text">CHÉRIE</h2>
        <div style="font-size:11px; color:#C58A9C; letter-spacing:1px; margin-top:4px; text-transform:uppercase;">Admin Portal</div>
    </div>

    <ul class="admin-nav">
        <li class="<?= ($currentDir === 'admin' && $currentScript === 'index.php') ? 'active' : '' ?>">
            <a href="<?= ADMIN_URL ?>/index.php">
                <span>📊</span> <span>Dashboard</span>
            </a>
        </li>
        <li class="<?= $currentDir === 'products' ? 'active' : '' ?>">
            <a href="<?= ADMIN_URL ?>/products/index.php">
                <span>👗</span> <span>Products</span>
            </a>
        </li>
        <li class="<?= $currentDir === 'categories' ? 'active' : '' ?>">
            <a href="<?= ADMIN_URL ?>/categories/index.php">
                <span>🏷️</span> <span>Categories</span>
            </a>
        </li>
        <li class="<?= $currentDir === 'orders' ? 'active' : '' ?>">
            <a href="<?= ADMIN_URL ?>/orders/index.php">
                <span>📦</span> <span>Orders</span>
            </a>
        </li>
        <li class="<?= $currentDir === 'users' ? 'active' : '' ?>">
            <a href="<?= ADMIN_URL ?>/users/index.php">
                <span>👥</span> <span>Customers</span>
            </a>
        </li>
        <li class="<?= ($currentDir === 'admin' && $currentScript === 'messages.php') ? 'active' : '' ?>">
            <a href="<?= ADMIN_URL ?>/messages.php">
                <span>💌</span> <span>Messages</span>
            </a>
        </li>
        <li class="<?= ($currentDir === 'admin' && $currentScript === 'newsletter.php') ? 'active' : '' ?>">
            <a href="<?= ADMIN_URL ?>/newsletter.php">
                <span>📬</span> <span>Newsletter</span>
            </a>
        </li>
        <li class="<?= ($currentDir === 'admin' && $currentScript === 'settings.php') ? 'active' : '' ?>">
            <a href="<?= ADMIN_URL ?>/settings.php">
                <span>⚙️</span> <span>Store Settings</span>
            </a>
        </li>
        <hr style="border:none; border-top:1px solid rgba(255,255,255,0.08); margin:15px 0;">
        <li>
            <a href="<?= BASE_URL ?>/index.php" target="_blank">
                <span>🌐</span> <span>View Storefront</span>
            </a>
        </li>
        <li>
            <a href="<?= ADMIN_URL ?>/logout.php" style="color:#f28b82;">
                <span>🚪</span> <span>Sign Out</span>
            </a>
        </li>
    </ul>
</aside>
