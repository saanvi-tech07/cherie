<?php
/**
 * CHÉRIE — Privacy Policy
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Privacy Policy';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-narrow" style="margin-top: 40px; margin-bottom: 80px;">
    <div class="card-box" style="padding:50px;">
        <span class="section-tagline">Client Trust & Security</span>
        <h1 style="font-size:32px; margin:8px 0 24px;">Privacy Policy</h1>
        <div style="font-size:14px; color:#5b484c; line-height:1.8;">
            <p><strong>Effective Date:</strong> January 1, 2026</p>
            <p>At <strong>CHÉRIE</strong> ("we", "our", or "us"), we are committed to safeguarding your personal information and treating your privacy with the utmost respect and transparency.</p>
            
            <h3 style="font-size:18px; margin:24px 0 8px; color:var(--chocolate);">1. Information We Collect</h3>
            <p>When you create an account, purchase pieces, or contact concierge support, we collect information including your name, email address, phone number, shipping address, and order history.</p>
            <p><strong>Payment Security:</strong> We never collect, process, or store credit card numbers, CVVs, or bank credentials on our servers. All digital payments are handled through PCI-DSS compliant payment gateways (Razorpay).</p>

            <h3 style="font-size:18px; margin:24px 0 8px; color:var(--chocolate);">2. How We Use Your Data</h3>
            <ul style="margin-left:20px; margin-bottom:16px;">
                <li>To safely pack and dispatch your orders.</li>
                <li>To send electronic receipts, delivery status tracking, and account notifications.</li>
                <li>To provide dedicated styling recommendations and concierge assistance.</li>
                <li>To deliver our boutique newsletter only when you explicitly opt-in.</li>
            </ul>

            <h3 style="font-size:18px; margin:24px 0 8px; color:var(--chocolate);">3. Cookies & Session Storage</h3>
            <p>We use essential cookies strictly to remember your persistent shopping bag items, maintain secure login sessions, and prevent cross-site request forgery (CSRF).</p>

            <h3 style="font-size:18px; margin:24px 0 8px; color:var(--chocolate);">4. Contacting Our Data Concierge</h3>
            <p>If you wish to update, review, or delete your personal details, please reach out to us at <a href="mailto:privacy@cherieboutique.com" style="color:var(--dusty-rose);">privacy@cherieboutique.com</a>.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
