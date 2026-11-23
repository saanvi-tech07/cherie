<?php
/**
 * CHÉRIE — Terms of Service
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Terms of Service';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-narrow" style="margin-top: 40px; margin-bottom: 80px;">
    <div class="card-box" style="padding:50px;">
        <span class="section-tagline">Terms of Engagement</span>
        <h1 style="font-size:32px; margin:8px 0 24px;">Terms of Service</h1>
        <div style="font-size:14px; color:#5b484c; line-height:1.8;">
            <p><strong>Effective Date:</strong> January 1, 2026</p>
            <p>Welcome to <strong>CHÉRIE</strong>. By browsing, placing orders, or using our boutique services, you agree to comply with and be bound by the following terms.</p>
            
            <h3 style="font-size:18px; margin:24px 0 8px; color:var(--chocolate);">1. Products & Pricing</h3>
            <p>All prices displayed are in Indian Rupees (INR ₹) inclusive of applicable taxes. We reserve the right to correct any typographical pricing errors or discontinue pieces without prior notice.</p>

            <h3 style="font-size:18px; margin:24px 0 8px; color:var(--chocolate);">2. Orders & Stock Availability</h3>
            <p>Placement of an order constitutes an offer to purchase. While we endeavor to maintain accurate inventory counters, items in limited production may sell out concurrently. If an ordered item is unavailable, a full immediate refund will be issued.</p>

            <h3 style="font-size:18px; margin:24px 0 8px; color:var(--chocolate);">3. Shipping & Delivery</h3>
            <p>Standard delivery across Indian pin codes takes between 3 to 5 business days. Express shipping delivers in 1 to 2 business days. Delivery times are estimates and may vary during national holidays or weather delays.</p>

            <h3 style="font-size:18px; margin:24px 0 8px; color:var(--chocolate);">4. 7-Day Exchange Guarantee</h3>
            <p>Unworn, unwashed garments with intact original tags and boutique ribbon packaging may be exchanged for alternative sizes within 7 days of delivery.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
