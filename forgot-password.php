<?php
/**
 * CHÉRIE — Forgot Password Request
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/mail_service.php';

$message = '';
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security validation failed. Please try again.';
    } else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $message = 'Please enter a valid email address.';
        } elseif (!Database::isConnected()) {
            $message = 'Database unavailable.';
        } else {
            $user = Database::fetchOne("SELECT id, full_name FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($user) {
                $rawToken = bin2hex(random_bytes(24));
                $tokenHash = hash('sha256', $rawToken);
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Delete old tokens for this email
                Database::execute("DELETE FROM password_resets WHERE email = ?", [$email]);
                Database::insert("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)", [$email, $tokenHash, $expiresAt]);

                // Send email
                $mailResult = MailService::sendPasswordReset($email, $rawToken);
                
                $isSuccess = true;
                $message = 'Password reset instructions have been generated. If SMTP is active, check your inbox. (Direct reset link: ' . BASE_URL . '/reset-password.php?token=' . urlencode($rawToken) . '&email=' . urlencode($email) . ')';
            } else {
                // Obscure whether email exists for privacy
                $isSuccess = true;
                $message = 'If an account exists with that email, reset instructions have been generated.';
            }
        }
    }
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-narrow" style="margin-top: 50px; margin-bottom: 80px;">
    <div class="card-box" style="max-width:480px; margin:0 auto; padding:40px;">
        <div style="text-align:center; margin-bottom:24px;">
            <span class="section-tagline">Account Recovery</span>
            <h1 style="font-size:26px; margin:6px 0;">Forgot Password</h1>
            <p style="font-size:13.5px; color:#78666a;">Enter your registered email to receive a password reset link.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?= $isSuccess ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!$isSuccess): ?>
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" placeholder="your.email@example.com" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary btn-block" style="padding:14px;">
                    Send Reset Link &rarr;
                </button>
            </form>
        <?php endif; ?>

        <div style="margin-top:24px; text-align:center; font-size:13.5px; color:#78666a; border-top:1px solid #f2e2e6; padding-top:20px;">
            Remember your password? <a href="<?= BASE_URL ?>/login.php" style="color:var(--dusty-rose); font-weight:600;">Back to Sign In</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
