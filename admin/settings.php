<?php
/**
 * CHÉRIE — Admin Store Settings Management
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';

$adminTitle = 'Boutique Store Settings';
require_once __DIR__ . '/includes/header.php';

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Security validation failed.');
    } else {
        $fields = [
            'site_name', 'site_tagline', 'site_email', 'site_phone', 'site_address',
            'free_shipping_threshold', 'flat_shipping_rate', 'enable_cod', 'enable_razorpay',
            'instagram_url', 'pinterest_url'
        ];

        foreach ($fields as $key) {
            $val = sanitizeInput($_POST[$key] ?? '');
            Database::execute("
                INSERT INTO website_settings (setting_key, setting_value, setting_group)
                VALUES (?, ?, 'general')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ", [$key, $val]);
        }

        setFlash('success', 'Store settings updated successfully! ✨');
        header('Location: ' . ADMIN_URL . '/settings.php');
        exit;
    }
}
?>

<div style="margin-bottom:24px;">
    <h1 style="font-size:24px; margin-bottom:4px;">Website Settings</h1>
    <p style="font-size:13px; color:#78666a;">Configure boutique branding, contact helplines, shipping parameters, and social links.</p>
</div>

<div class="admin-card" style="max-width:750px;">
    <form method="POST">
        <?= csrfField() ?>

        <h3 style="font-size:17px; margin-bottom:16px; border-bottom:1px solid #f2e2e6; padding-bottom:10px;">
            Branding & Concierge
        </h3>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label class="form-label">Store Name</label>
                <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars(getSetting('site_name', 'CHÉRIE')) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Brand Tagline</label>
                <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars(getSetting('site_tagline', 'A Little Love in Every Detail.')) ?>" required>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label class="form-label">Customer Support Email</label>
                <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars(getSetting('site_email', 'hello@cherieboutique.com')) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Customer Support Phone</label>
                <input type="text" name="site_phone" class="form-control" value="<?= htmlspecialchars(getSetting('site_phone', '+91 98765 43210')) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Flagship Boutique Address</label>
            <textarea name="site_address" class="form-control" style="min-height:75px;"><?= htmlspecialchars(getSetting('site_address', 'Boutique Boulevard, Linking Road, Bandra West, Mumbai 400050')) ?></textarea>
        </div>

        <h3 style="font-size:17px; margin:28px 0 16px; border-bottom:1px solid #f2e2e6; padding-bottom:10px;">
            Shipping & Payment Rules
        </h3>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label class="form-label">Free Express Shipping Threshold (INR ₹)</label>
                <input type="number" step="0.01" name="free_shipping_threshold" class="form-control" value="<?= htmlspecialchars(getSetting('free_shipping_threshold', '1499')) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Standard Flat Shipping Fee (INR ₹)</label>
                <input type="number" step="0.01" name="flat_shipping_rate" class="form-control" value="<?= htmlspecialchars(getSetting('flat_shipping_rate', '99')) ?>" required>
            </div>
        </div>

        <div style="display:flex; gap:24px; margin:16px 0;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="enable_cod" value="1" <?= getSetting('enable_cod', '1') == '1' ? 'checked' : '' ?>>
                <span style="font-size:13.5px; font-weight:500;">Enable Cash on Delivery</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="enable_razorpay" value="1" <?= getSetting('enable_razorpay', '1') == '1' ? 'checked' : '' ?>>
                <span style="font-size:13.5px; font-weight:500;">Enable Razorpay Test Mode</span>
            </label>
        </div>

        <h3 style="font-size:17px; margin:28px 0 16px; border-bottom:1px solid #f2e2e6; padding-bottom:10px;">
            Social Handles
        </h3>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label class="form-label">Instagram URL</label>
                <input type="url" name="instagram_url" class="form-control" value="<?= htmlspecialchars(getSetting('instagram_url', 'https://instagram.com/cherieboutique')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Pinterest URL</label>
                <input type="url" name="pinterest_url" class="form-control" value="<?= htmlspecialchars(getSetting('pinterest_url', 'https://pinterest.com/cherieboutique')) ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="padding:13px 32px; margin-top:10px;">
            Save Website Settings &rarr;
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
