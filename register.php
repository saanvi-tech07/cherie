<?php
/**
 * CHÉRIE — Customer Registration
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/mail_service.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/account.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!$fullName || !$email || !$password) {
            $error = 'Please fill out all required fields.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'The password confirmations do not match.';
        } elseif (!Database::isConnected()) {
            $error = 'Database connection is currently unavailable.';
        } else {
            // Check if email already registered
            $existing = Database::fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($existing) {
                $error = 'An account with this email address already exists. Please sign in instead.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $userId = Database::insert("INSERT INTO users (full_name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'customer')", [
                    $fullName, $email, $phone, $passwordHash
                ]);

                if ($userId) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_name'] = $fullName;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = 'customer';

                    // Merge guest cart
                    mergeGuestCartOnLogin((int)$userId);

                    // Send Welcome Email
                    MailService::sendRegistrationConfirmation($email, $fullName);

                    setFlash('success', 'Welcome to the CHÉRIE family, ' . htmlspecialchars($fullName) . '! ✨ Enjoy 15% off with code CHERIE15.');
                    header('Location: ' . BASE_URL . '/account.php');
                    exit;
                } else {
                    $error = 'Registration failed due to a database error. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Create Your CHÉRIE Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-narrow" style="margin-top: 50px; margin-bottom: 80px;">
    
    <div class="card-box" style="max-width:520px; margin:0 auto; padding:40px;">
        <div style="text-align:center; margin-bottom:28px;">
            <span class="section-tagline">Join Our Circle</span>
            <h1 style="font-size:28px; margin:6px 0;">Create Account</h1>
            <p style="font-size:13.5px; color:#78666a;">Unlock curated wishlists, faster checkout, and exclusive member perks.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="e.g. Mademoiselle Camille" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="your.email@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number (Optional)</label>
                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="+91 98765 43210">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 chars" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding:14px; font-size:14.5px; margin-top:10px;">
                Complete Registration &rarr;
            </button>
        </form>

        <div style="margin-top:24px; text-align:center; font-size:13.5px; color:#78666a; border-top:1px solid #f2e2e6; padding-top:20px;">
            Already a member of CHÉRIE? 
            <a href="<?= BASE_URL ?>/login.php" style="color:var(--dusty-rose); font-weight:600;">Sign in here &rarr;</a>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
