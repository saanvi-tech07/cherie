<?php
/**
 * CHÉRIE — Reset Password Execution
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

$rawToken = trim($_GET['token'] ?? '');
$email = filter_var(trim($_GET['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$tokenValid = false;
$error = '';

if ($rawToken && $email && Database::isConnected()) {
    $tokenHash = hash('sha256', $rawToken);
    $resetRecord = Database::fetchOne("
        SELECT * FROM password_resets 
        WHERE email = ? AND token = ? AND expires_at > NOW() 
        LIMIT 1
    ", [$email, $tokenHash]);

    if ($resetRecord) {
        $tokenValid = true;
    } else {
        $error = 'This password reset link is invalid or has expired. Please request a new one.';
    }
} else {
    $error = 'Missing reset parameters.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed.';
    } else {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'The passwords do not match.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            Database::execute("UPDATE users SET password_hash = ? WHERE email = ?", [$newHash, $email]);
            Database::execute("DELETE FROM password_resets WHERE email = ?", [$email]);

            setFlash('success', 'Your password has been successfully updated! Please sign in with your new password.');
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    }
}

$pageTitle = 'Choose a New Password';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-narrow" style="margin-top: 50px; margin-bottom: 80px;">
    <div class="card-box" style="max-width:480px; margin:0 auto; padding:40px;">
        <div style="text-align:center; margin-bottom:24px;">
            <span class="section-tagline">New Password</span>
            <h1 style="font-size:26px; margin:6px 0;">Reset Password</h1>
            <p style="font-size:13.5px; color:#78666a;">Set a secure new password for your account.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($tokenValid): ?>
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label">New Password *</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password *</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block" style="padding:14px;">
                    Update Password &rarr;
                </button>
            </form>
        <?php else: ?>
            <div style="text-align:center; margin-top:20px;">
                <a href="<?= BASE_URL ?>/forgot-password.php" class="btn btn-outline">Request New Reset Link</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
