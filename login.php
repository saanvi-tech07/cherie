<?php
/**
 * CHÉRIE — Customer Sign In
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/account.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $error = 'Please provide both your email address and password.';
        } elseif (!Database::isConnected()) {
            $error = 'Database connection unavailable.';
        } else {
            $user = Database::fetchOne("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);

            if ($user && password_verify($password, $user['password_hash'])) {
                if (!$user['is_active']) {
                    $error = 'Your account has been deactivated. Please contact concierge support.';
                } else {
                    // Regenerate session id for security
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    // Merge guest cart into user cart
                    mergeGuestCartOnLogin((int)$user['id']);

                    setFlash('success', 'Welcome back, ' . htmlspecialchars($user['full_name']) . '! 💕');

                    $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/account.php';
                    unset($_SESSION['redirect_after_login']);
                    header('Location: ' . $redirect);
                    exit;
                }
            } else {
                $error = 'Invalid email address or password combination.';
            }
        }
    }
}

$pageTitle = 'Sign In to Your Boutique Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-narrow" style="margin-top: 50px; margin-bottom: 80px;">
    
    <div class="card-box" style="max-width:480px; margin:0 auto; padding:40px;">
        <div style="text-align:center; margin-bottom:28px;">
            <span class="section-tagline">Welcome Back</span>
            <h1 style="font-size:28px; margin:6px 0;">Sign In</h1>
            <p style="font-size:13.5px; color:#78666a;">Access your orders, saved bag, and wishlist.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="your.email@example.com" required autofocus>
            </div>

            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label class="form-label" style="margin-bottom:0;">Password *</label>
                    <a href="<?= BASE_URL ?>/forgot-password.php" style="font-size:12px; color:var(--dusty-rose);">Forgot password?</a>
                </div>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding:14px; font-size:14.5px; margin-top:10px;">
                Sign In &rarr;
            </button>
        </form>

        <div style="margin-top:24px; text-align:center; font-size:13.5px; color:#78666a; border-top:1px solid #f2e2e6; padding-top:20px;">
            Don't have an account yet? 
            <a href="<?= BASE_URL ?>/register.php" style="color:var(--dusty-rose); font-weight:600;">Join CHÉRIE &rarr;</a>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
