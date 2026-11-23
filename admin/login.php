<?php
/**
 * CHÉRIE — Admin Login
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__) . '/config/config.php';

if (isAdminLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$error = '';

// Check if any admin exists. If none exist, suggest running installer or setup
$adminCount = 0;
if (Database::isConnected()) {
    $adminCount = (int)Database::fetchOne("SELECT COUNT(*) AS total FROM admins")['total'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) {
            $error = 'Please enter both your administrator username and password.';
        } elseif (!Database::isConnected()) {
            $error = 'Database connection error.';
        } else {
            $admin = Database::fetchOne("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1", [$username, $username]);

            if ($admin && password_verify($password, $admin['password_hash'])) {
                if (!$admin['is_active']) {
                    $error = 'This administrative account is inactive.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    $_SESSION['admin_role'] = $admin['role'];

                    // Update last login
                    Database::execute("UPDATE admins SET last_login = NOW() WHERE id = ?", [$admin['id']]);

                    setFlash('success', 'Welcome back, ' . htmlspecialchars($admin['full_name']) . '!');
                    $redirect = $_SESSION['admin_redirect'] ?? ADMIN_URL . '/index.php';
                    unset($_SESSION['admin_redirect']);
                    header('Location: ' . $redirect);
                    exit;
                }
            } else {
                $error = 'Invalid administrative credentials.';
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
    <title>CHÉRIE — Administrator Portal Sign In</title>
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

<div class="card-box" style="max-width:440px; width:100%; padding:40px; box-shadow:var(--shadow-medium);">
    <div style="text-align:center; margin-bottom:28px;">
        <h1 style="font-family:'Playfair Display',serif; font-size:32px; letter-spacing:3px; color:var(--chocolate); margin-bottom:4px;">CHÉRIE</h1>
        <div style="font-size:11.5px; color:var(--dusty-rose); font-weight:600; letter-spacing:1.5px; text-transform:uppercase;">Admin Portal</div>
    </div>

    <?php if ($adminCount === 0): ?>
        <div class="alert alert-warning">
            ⚠️ No admin account exists yet. Run <a href="<?= BASE_URL ?>/install.php" style="color:#B7950B; font-weight:600; text-decoration:underline;">install.php</a> to generate your one-time admin setup token!
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <?= csrfField() ?>
        <div class="form-group">
            <label class="form-label">Username or Admin Email *</label>
            <input type="text" name="username" class="form-control" placeholder="admin_user" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-dark btn-block" style="padding:14px; font-size:14.5px; margin-top:10px;">
            Sign In to Dashboard &rarr;
        </button>
    </form>

    <div style="margin-top:24px; text-align:center; font-size:12.5px; color:#8c767b; border-top:1px solid #f2e2e6; padding-top:16px;">
        <a href="<?= BASE_URL ?>/index.php" style="color:var(--dusty-rose);">&larr; Return to Customer Storefront</a>
    </div>
</div>

</body>
</html>
