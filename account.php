<?php
/**
 * CHÉRIE — Customer Account & Profile Management
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

requireAuth('login.php');

$userId = (int)$_SESSION['user_id'];
$user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

if (!$user) {
    header('Location: ' . BASE_URL . '/logout.php');
    exit;
}

$successMsg = '';
$errorMsg = '';

// Handle Profile Details Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Security validation failed.';
    } else {
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $city = sanitizeInput($_POST['city'] ?? '');
        $state = sanitizeInput($_POST['state'] ?? '');
        $postalCode = sanitizeInput($_POST['postal_code'] ?? '');

        if ($fullName) {
            Database::execute("
                UPDATE users 
                SET full_name = ?, phone = ?, address = ?, city = ?, state = ?, postal_code = ?
                WHERE id = ?
            ", [$fullName, $phone, $address, $city, $state, $postalCode, $userId]);

            $_SESSION['user_name'] = $fullName;
            $user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
            $successMsg = 'Your profile details have been saved successfully! ✨';
        } else {
            $errorMsg = 'Full name cannot be left blank.';
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Security validation failed.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $user['password_hash'])) {
            $errorMsg = 'Your current password was entered incorrectly.';
        } elseif (strlen($newPassword) < 6) {
            $errorMsg = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $errorMsg = 'New password confirmation does not match.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
            Database::execute("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $userId]);
            $successMsg = 'Password changed successfully.';
        }
    }
}

// Stats
$orderCount = (int)Database::fetchOne("SELECT COUNT(*) AS total FROM orders WHERE user_id = ?", [$userId])['total'];
$wishlistCount = getWishlistCount();

$pageTitle = 'My Boutique Account';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 35px; margin-bottom: 80px;">
    
    <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px; margin-bottom:30px; border-bottom:1px solid var(--border-color); padding-bottom:20px;">
        <div>
            <span class="product-cat">CLIENT DASHBOARD</span>
            <h1 style="font-size:32px;">Bonjour, <?= htmlspecialchars($user['full_name']) ?>! 🌸</h1>
            <p style="font-size:14px; color:#78666a; margin-top:4px;">Manage your shipping addresses and personal preferences.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline btn-sm" style="color:#C0392B; border-color:#F5B7B1;">Sign Out</a>
        </div>
    </div>

    <?php if ($successMsg): ?><div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div><?php endif; ?>
    <?php if ($errorMsg): ?><div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div><?php endif; ?>

    <!-- Quick Stats Cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:35px;">
        <div class="card-box" style="margin-bottom:0; display:flex; align-items:center; gap:18px;">
            <div style="font-size:32px; background:var(--blush); width:60px; height:60px; border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center;">📦</div>
            <div>
                <div style="font-size:24px; font-weight:600;"><?= $orderCount ?></div>
                <a href="<?= BASE_URL ?>/orders.php" style="font-size:13px; color:var(--dusty-rose); font-weight:500;">Orders Placed &rarr;</a>
            </div>
        </div>

        <div class="card-box" style="margin-bottom:0; display:flex; align-items:center; gap:18px;">
            <div style="font-size:32px; background:var(--lavender); width:60px; height:60px; border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center;">🤍</div>
            <div>
                <div style="font-size:24px; font-weight:600;"><?= $wishlistCount ?></div>
                <a href="<?= BASE_URL ?>/wishlist.php" style="font-size:13px; color:var(--dusty-rose); font-weight:500;">Saved in Wishlist &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Forms Grid -->
    <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:40px; align-items:start;">
        
        <!-- Profile / Shipping Details Form -->
        <div class="card-box">
            <h3 style="font-size:20px; margin-bottom:20px; border-bottom:1px solid #f2e2e6; padding-bottom:10px;">
                Profile & Delivery Address
            </h3>

            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="update_profile" value="1">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address (Immutable)</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:#f5eded;">
                </div>

                <div class="form-group">
                    <label class="form-label">Street Address & Flat No.</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="e.g. Flat 402, Rose Villa, Linking Rd">
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($user['state'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">PIN Code</label>
                        <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="padding:12px 28px;">
                    Save Profile Changes
                </button>
            </form>
        </div>

        <!-- Change Password Form -->
        <div class="card-box">
            <h3 style="font-size:20px; margin-bottom:20px; border-bottom:1px solid #f2e2e6; padding-bottom:10px;">
                Change Password
            </h3>

            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="change_password" value="1">

                <div class="form-group">
                    <label class="form-label">Current Password *</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">New Password *</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password *</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-dark btn-block" style="padding:12px 24px;">
                    Update Password
                </button>
            </form>
        </div>

    </div>

</div>

<style>
@media (max-width: 860px) {
    div[style*="grid-template-columns: 1.5fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
