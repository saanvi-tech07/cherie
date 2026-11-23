<?php
/**
 * CHÉRIE — Global Header Component
 */
if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/xml_service.php';

$cartCount = getCartCount();
$wishlistCount = getWishlistCount();
$currentUser = getCurrentUser();

// Load active promotional banner from XML
$activePromotions = XmlService::getActivePromotions();
$topPromo = !empty($activePromotions) ? $activePromotions[0] : null;

// Page Title fallback
$pageTitle = isset($pageTitle) ? $pageTitle . ' — ' . APP_NAME : APP_NAME . ' — ' . APP_TAGLINE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="CHÉRIE — A Little Love in Every Detail. Discover romantic dresses, luxury co-ords, skincare, dainty jewellery and lifestyle boutique treasures.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/logo.svg">
</head>
<body>

<!-- 1. Promotional Announcement Bar (Driven by XML via SimpleXML) -->
<div class="announcement-bar">
    <?php if ($topPromo): ?>
        <span>✨ <strong><?= htmlspecialchars($topPromo['badge_text']) ?>:</strong> <?= htmlspecialchars($topPromo['description']) ?> | Use Code: <strong><?= htmlspecialchars($topPromo['code']) ?></strong> ✨</span>
    <?php else: ?>
        <span>✨ Compliment from CHÉRIE: Free Express Delivery on all orders above ₹1,499 ✨</span>
    <?php endif; ?>
</div>

<!-- 2. Sticky Header -->
<header class="site-header">
    <div class="container">
        <div class="header-inner">
            
            <!-- Mobile Menu Button -->
            <button class="mobile-nav-toggle" aria-label="Open Navigation Menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <!-- Brand Logo -->
            <a href="<?= BASE_URL ?>/index.php" class="site-logo">
                <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="CHÉRIE Boutique" width="180" height="45">
            </a>

            <!-- Desktop Navigation -->
            <nav>
                <ul class="nav-desktop">
                    <li><a href="<?= BASE_URL ?>/index.php" class="nav-link">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/shop.php" class="nav-link">Shop All</a></li>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=dresses" class="nav-link">Dresses</a></li>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=ethnic-wear" class="nav-link">Ethnic</a></li>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=makeup" class="nav-link">Beauty</a></li>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=jewellery" class="nav-link">Jewellery</a></li>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=handbags" class="nav-link">Handbags</a></li>
                    <li><a href="<?= BASE_URL ?>/about.php" class="nav-link">Story</a></li>
                    <li><a href="<?= BASE_URL ?>/contact.php" class="nav-link">Contact</a></li>
                </ul>
            </nav>

            <!-- Header Actions -->
            <div class="header-actions">
                <!-- Search Button -->
                <a href="<?= BASE_URL ?>/search.php" class="header-icon-btn search-trigger" title="Search catalog">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </a>

                <!-- Account / Login -->
                <?php if ($currentUser): ?>
                    <a href="<?= BASE_URL ?>/account.php" class="header-icon-btn" title="My Account (<?= htmlspecialchars($currentUser['name']) ?>)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="header-icon-btn" title="Sign In">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    </a>
                <?php endif; ?>

                <!-- Wishlist -->
                <a href="<?= BASE_URL ?>/wishlist.php" class="header-icon-btn" title="Wishlist">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    <span class="badge-count wishlist-badge" style="<?= $wishlistCount > 0 ? '' : 'display:none;' ?>"><?= $wishlistCount ?></span>
                </a>

                <!-- Shopping Bag -->
                <a href="<?= BASE_URL ?>/cart.php" class="header-icon-btn" title="Shopping Bag">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <span class="badge-count cart-badge"><?= $cartCount ?></span>
                </a>
            </div>

        </div>
    </div>
</header>

<!-- 3. Mobile Navigation Drawer -->
<div id="mobileDrawer" style="position:fixed; top:0; left:-100%; width:82%; max-width:340px; height:100%; background:#FFF; z-index:10000; box-shadow:5px 0 30px rgba(0,0,0,0.15); transition:left 0.35s ease; padding:30px 24px; overflow-y:auto;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px;">
        <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="CHÉRIE" style="height:38px;">
        <button id="closeDrawer" style="background:none; border:none; font-size:24px; cursor:pointer; color:#49383C;">&times;</button>
    </div>
    <ul style="list-style:none; display:flex; flex-direction:column; gap:16px;">
        <li><a href="<?= BASE_URL ?>/index.php" style="font-size:16px; font-weight:500;">Home</a></li>
        <li><a href="<?= BASE_URL ?>/shop.php" style="font-size:16px; font-weight:500;">Shop All Collection</a></li>
        <li><a href="<?= BASE_URL ?>/category.php?slug=dresses" style="font-size:15px; color:#665257;">Dresses & Midis</a></li>
        <li><a href="<?= BASE_URL ?>/category.php?slug=tops" style="font-size:15px; color:#665257;">Tops & Blouses</a></li>
        <li><a href="<?= BASE_URL ?>/category.php?slug=coord-sets" style="font-size:15px; color:#665257;">Co-ord Sets</a></li>
        <li><a href="<?= BASE_URL ?>/category.php?slug=ethnic-wear" style="font-size:15px; color:#665257;">Ethnic & Chikankari</a></li>
        <li><a href="<?= BASE_URL ?>/category.php?slug=makeup" style="font-size:15px; color:#665257;">Makeup & Tints</a></li>
        <li><a href="<?= BASE_URL ?>/category.php?slug=skincare" style="font-size:15px; color:#665257;">Clean Skincare</a></li>
        <li><a href="<?= BASE_URL ?>/category.php?slug=jewellery" style="font-size:15px; color:#665257;">Dainty Jewellery</a></li>
        <li><a href="<?= BASE_URL ?>/category.php?slug=handbags" style="font-size:15px; color:#665257;">Handbags & Totes</a></li>
        <li><a href="<?= BASE_URL ?>/category.php?slug=room-decor" style="font-size:15px; color:#665257;">Room Decor & Candles</a></li>
        <hr style="border:none; border-top:1px solid #F7DCE5; margin:10px 0;">
        <?php if ($currentUser): ?>
            <li><a href="<?= BASE_URL ?>/account.php" style="font-size:15px; font-weight:500;">My Account</a></li>
            <li><a href="<?= BASE_URL ?>/orders.php" style="font-size:15px; color:#665257;">My Orders</a></li>
            <li><a href="<?= BASE_URL ?>/logout.php" style="font-size:15px; color:#C0392B;">Sign Out</a></li>
        <?php else: ?>
            <li><a href="<?= BASE_URL ?>/login.php" style="font-size:15px; font-weight:500;">Sign In</a></li>
            <li><a href="<?= BASE_URL ?>/register.php" style="font-size:15px; color:#C58A9C;">Create Account</a></li>
        <?php endif; ?>
    </ul>
</div>
<div id="drawerOverlay" onclick="document.getElementById('mobileDrawer').classList.remove('open'); this.style.display='none';" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999;"></div>

<script>
document.querySelector('.mobile-nav-toggle')?.addEventListener('click', function() {
    document.getElementById('mobileDrawer').style.left = '0';
    document.getElementById('drawerOverlay').style.display = 'block';
});
document.getElementById('closeDrawer')?.addEventListener('click', function() {
    document.getElementById('mobileDrawer').style.left = '-100%';
    document.getElementById('drawerOverlay').style.display = 'none';
});
</script>

<?php require_once __DIR__ . '/alerts.php'; ?>
