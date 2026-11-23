<?php
/**
 * CHÉRIE — Global Footer Component
 */
if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}
?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            
            <!-- Brand Column -->
            <div>
                <div class="footer-logo">CHÉRIE</div>
                <div class="footer-tagline">A Little Love in Every Detail.</div>
                <p class="footer-desc">
                    A thoughtfully curated sanctuary for girls' fashion, mindful skincare, artisanal jewellery, and Parisian lifestyle pieces. Designed to inspire everyday romance and effortless confidence.
                </p>
                <div style="margin-top:20px; display:flex; gap:14px;">
                    <a href="<?= htmlspecialchars(getSetting('instagram_url', 'https://instagram.com/cherieboutique')) ?>" target="_blank" style="color:#FFF; background:#3d2f33; padding:8px 14px; border-radius:50px; font-size:12px; display:inline-flex; align-items:center; gap:6px;">
                        <span>📷 Instagram</span>
                    </a>
                    <a href="<?= htmlspecialchars(getSetting('pinterest_url', 'https://pinterest.com/cherieboutique')) ?>" target="_blank" style="color:#FFF; background:#3d2f33; padding:8px 14px; border-radius:50px; font-size:12px; display:inline-flex; align-items:center; gap:6px;">
                        <span>📌 Pinterest</span>
                    </a>
                </div>
            </div>

            <!-- Boutique Categories -->
            <div>
                <h4 class="footer-heading">Boutique</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>/category.php?slug=dresses">Dresses & Midis</a></li>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=coord-sets">Co-ord Sets</a></li>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=ethnic-wear">Handcrafted Ethnic</a></li>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=makeup">Lip & Cheek Tints</a></li>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=jewellery">Dainty Pearl Jewellery</a></li>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=handbags">Quilted Crossbodies</a></li>
                    <li><a href="<?= BASE_URL ?>/category.php?slug=room-decor">Aesthetic Room Decor</a></li>
                </ul>
            </div>

            <!-- Customer Care -->
            <div>
                <h4 class="footer-heading">Client Services</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>/orders.php">Order Tracking</a></li>
                    <li><a href="<?= BASE_URL ?>/about.php">Our Artisan Story</a></li>
                    <li><a href="<?= BASE_URL ?>/contact.php">Contact & Concierge</a></li>
                    <li><a href="<?= BASE_URL ?>/privacy-policy.php">Privacy Policy</a></li>
                    <li><a href="<?= BASE_URL ?>/terms.php">Terms of Service</a></li>
                    <li><a href="<?= BASE_URL ?>/xml/catalog_export.php" target="_blank" title="Live SimpleXML Catalog Feed">XML Product Feed</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/login.php" style="color:#d99aa9;">Admin Portal</a></li>
                </ul>
            </div>

            <!-- Newsletter Subscription -->
            <div>
                <h4 class="footer-heading">Join The Circle</h4>
                <p class="footer-desc" style="font-size:13px;">
                    Receive secret editorial previews, floral surprises, and 15% off your opening purchase.
                </p>
                <form action="<?= BASE_URL ?>/index.php" method="POST" class="newsletter-ajax-form newsletter-form">
                    <?= csrfField() ?>
                    <input type="email" name="newsletter_email" class="newsletter-input" placeholder="Your sweet email address..." required>
                    <button type="submit" class="btn btn-primary btn-sm" style="padding:10px 18px;">Join</button>
                </form>
                <div style="margin-top:18px; font-size:12px; color:#a49194;">
                    📍 <?= htmlspecialchars(getSetting('site_address', 'Boutique Boulevard, Linking Road, Bandra West, Mumbai 400050')) ?><br>
                    📞 Helpline: <?= htmlspecialchars(getSetting('site_phone', '+91 98765 43210')) ?>
                </div>
            </div>

        </div>

        <div class="footer-bottom">
            <div>
                &copy; <?= date('Y') ?> <strong><?= htmlspecialchars(getSetting('site_name', 'CHÉRIE')) ?></strong>. All rights reserved. Handcrafted with love.
            </div>
            <div>
                <span>Secure Payments: Razorpay (Test Mode) & Cash on Delivery</span>
            </div>
        </div>
    </div>
</footer>

<!-- Interactive Scripts -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/wishlist.js"></script>
<script src="<?= BASE_URL ?>/assets/js/cart.js"></script>
</body>
</html>
