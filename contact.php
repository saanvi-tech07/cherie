<?php
/**
 * CHÉRIE — Contact & Concierge
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');

        if (!$name || !$email || !$subject || !$message) {
            $error = 'Please fill out all required fields.';
        } elseif (!Database::isConnected()) {
            $error = 'Database unavailable.';
        } else {
            Database::insert("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)", [
                $name, $email, $phone, $subject, $message
            ]);
            $success = true;
        }
    }
}

$pageTitle = 'Concierge & Client Care';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 80px;">
    
    <div style="text-align:center; max-width:650px; margin:0 auto 50px;">
        <span class="section-tagline">We Are Here For You</span>
        <h1 style="font-size:36px; margin:8px 0 14px;">Contact Our Concierge</h1>
        <p style="font-size:15px; color:#685559; line-height:1.7;">
            Whether you have a sizing question, need a gift styling recommendation, or wish to track an order, our client care team responds with warmth and devotion.
        </p>
    </div>

    <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:50px; align-items:start;">
        
        <!-- Contact Form -->
        <div class="card-box" style="padding:40px;">
            <h3 style="font-size:22px; margin-bottom:20px;">Send a Note</h3>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✨ Thank you, beautiful! Your note has reached our boutique team. We will respond within 24 business hours.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?= csrfField() ?>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Your Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Eleanor Vance" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="your.email@example.com" required>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Phone Number (Optional)</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject *</label>
                        <select name="subject" class="form-control" required>
                            <option value="Order Tracking">Order & Shipping Inquiry</option>
                            <option value="Sizing Advice">Sizing & Styling Advice</option>
                            <option value="Exchange Request">Exchange & Return Request</option>
                            <option value="Artisan Collaboration">Artisan & Press Collaboration</option>
                            <option value="General Note">General Inquiry</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Your Message *</label>
                    <textarea name="message" class="form-control" style="min-height:140px;" placeholder="Tell us how we can help you..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="padding:14px 34px;">
                    Send Message &rarr;
                </button>
            </form>
        </div>

        <!-- Contact Information Cards -->
        <div>
            <div class="card-box" style="margin-bottom:24px;">
                <h4 style="font-size:16px; font-weight:600; margin-bottom:12px; color:var(--chocolate);">
                    📍 Flagship Atelier
                </h4>
                <p style="font-size:14px; color:#6b575c; line-height:1.7;">
                    <?= htmlspecialchars(getSetting('site_address', 'Boutique Boulevard, Linking Road, Bandra West, Mumbai 400050')) ?>
                </p>
                <div style="font-size:13px; color:#8f7b7f; margin-top:8px;">
                    Hours: Monday – Saturday, 10:00 AM – 8:00 PM IST
                </div>
            </div>

            <div class="card-box" style="margin-bottom:24px;">
                <h4 style="font-size:16px; font-weight:600; margin-bottom:12px; color:var(--chocolate);">
                    💌 Direct Email
                </h4>
                <p style="font-size:14px; color:#6b575c; line-height:1.7;">
                    General & Orders: <a href="mailto:<?= htmlspecialchars(getSetting('site_email', 'hello@cherieboutique.com')) ?>" style="color:var(--dusty-rose); font-weight:500;"><?= htmlspecialchars(getSetting('site_email', 'hello@cherieboutique.com')) ?></a><br>
                    Press & Styling: press@cherieboutique.com
                </p>
            </div>

            <div class="card-box">
                <h4 style="font-size:16px; font-weight:600; margin-bottom:12px; color:var(--chocolate);">
                    📞 Concierge Helpline
                </h4>
                <p style="font-size:14px; color:#6b575c; line-height:1.7;">
                    Call / WhatsApp: <strong style="color:var(--chocolate);"><?= htmlspecialchars(getSetting('site_phone', '+91 98765 43210')) ?></strong><br>
                    <span style="font-size:12.5px; color:#8f7b7f;">Standard carrier rates apply. Quick response guaranteed.</span>
                </p>
            </div>
        </div>

    </div>

</div>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns: 1.2fr 1fr"],
    div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
