<?php
/**
 * CHÉRIE — One-Time Secure Admin Setup
 * Allows administrator to configure their personal credentials using a single-use cryptographically generated token.
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__) . '/config/config.php';

$rawToken = trim($_GET['token'] ?? '');
$tokenValid = false;
$error = '';
$setupTokenId = 0;

if ($rawToken && Database::isConnected()) {
    $tokenHash = hash('sha256', $rawToken);
    $tokenRecord = Database::fetchOne("
        SELECT * FROM admin_setup_tokens 
        WHERE token_hash = ? AND is_used = 0 AND expires_at > NOW() 
        LIMIT 1
    ", [$tokenHash]);

    if ($tokenRecord) {
        $tokenValid = true;
        $setupTokenId = (int)$tokenRecord['id'];
    } else {
        $error = 'This setup token is invalid, expired, or has already been used. Please run install.php if you need a new setup token.';
    }
} else {
    $error = 'Setup token is required to initialize administrator credentials.';
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Session validation error. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!$username || !$fullName || !$email || !$password) {
            $error = 'Please fill out all mandatory fields.';
        } elseif (strlen($password) < 8) {
            $error = 'Admin password must be at least 8 characters long for security.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Password confirmation does not match.';
        } else {
            // Check if username/email already exists
            $existing = Database::fetchOne("SELECT id FROM admins WHERE username = ? OR email = ? LIMIT 1", [$username, $email]);
            if ($existing) {
                $error = 'An administrator with this username or email already exists.';
            } else {
                $passHash = password_hash($password, PASSWORD_BCRYPT);
                $adminId = Database::insert("
                    INSERT INTO admins (username, email, password_hash, full_name, role, is_active)
                    VALUES (?, ?, ?, ?, 'superadmin', 1)
                ", [$username, $email, $passHash, $fullName]);

                if ($adminId) {
                    // Mark token as used
                    Database::execute("UPDATE admin_setup_tokens SET is_used = 1 WHERE id = ?", [$setupTokenId]);

                    // Log in immediately
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $adminId;
                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_email'] = $email;
                    $_SESSION['admin_name'] = $fullName;
                    $_SESSION['admin_role'] = 'superadmin';

                    setFlash('success', 'Admin account configured successfully! Welcome to your boutique dashboard.');
                    header('Location: ' . ADMIN_URL . '/index.php');
                    exit;
                } else {
                    $error = 'Database insertion failed.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHÉRIE — Initial Administrator Setup</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    <style>
        body { background-color: var(--soft-cream); display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
    </style>
</head>
<body>

<div class="card-box" style="max-width:540px; width:100%; padding:40px; box-shadow:var(--shadow-medium);">
    <div style="text-align:center; margin-bottom:28px;">
        <h1 style="font-family:'Playfair Display',serif; font-size:32px; letter-spacing:3px; color:var(--chocolate); margin-bottom:4px;">CHÉRIE</h1>
        <div style="font-size:12px; color:var(--dusty-rose); font-weight:600; letter-spacing:1.5px; text-transform:uppercase;">Admin Security Setup</div>
        <p style="font-size:13.5px; color:#78666a; margin-top:8px;">Set up your personal administrator credentials. No default passwords are used.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($tokenValid): ?>
        <form method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="e.g. Head Curator" required autofocus>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Admin Username *</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="e.g. cherie_admin" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="admin@cherieboutique.com" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Password (Min 8 chars) *</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding:14px; font-size:15px; margin-top:10px;">
                Create Admin Account & Launch Dashboard &rarr;
            </button>
        </form>
    <?php else: ?>
        <div style="text-align:center; margin-top:20px;">
            <a href="<?= BASE_URL ?>/install.php" class="btn btn-outline">Go to Store Installer</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
