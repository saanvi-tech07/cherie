<?php
/**
 * CHÉRIE — Safe Automated Web & CLI Installer
 * Creates database and tables if not exist, seeds sample data, and generates a secure one-time admin setup token.
 */

// Define execution protection
define('CHERIE_APP', true);

// Report all errors during installation
ini_set('display_errors', 1);
error_reporting(E_ALL);

$baseDir = __DIR__;
require_once $baseDir . '/config/database.php';

$isCli = (php_sapi_name() === 'cli');

$status = [
    'db_connected' => false,
    'tables_created' => false,
    'data_seeded' => false,
    'setup_token' => null,
    'errors' => []
];

// Helper to run installation
function runInstallation(&$status, $baseDir) {
    try {
        // Step 1: Connect to MySQL server (without selecting db first)
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `" . DB_NAME . "`;");
        $status['db_connected'] = true;

        // Step 2: Run schema.sql safely
        $schemaSql = file_get_contents($baseDir . '/database/schema.sql');
        $pdo->exec($schemaSql);
        $status['tables_created'] = true;

        // Step 3: Run seed_data.sql safely
        $seedSql = file_get_contents($baseDir . '/database/seed_data.sql');
        $pdo->exec($seedSql);
        $status['data_seeded'] = true;

        // Step 4: Check if any admin exists. If not, generate a one-time setup token
        $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM `admins`")->fetchColumn();
        if ($adminCount === 0) {
            $rawToken = bin2hex(random_bytes(24));
            $tokenHash = hash('sha256', $rawToken);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));

            // Clear any old unused tokens
            $pdo->exec("DELETE FROM `admin_setup_tokens` WHERE `is_used` = 0");

            $stmt = $pdo->prepare("INSERT INTO `admin_setup_tokens` (`token_hash`, `is_used`, `expires_at`) VALUES (?, 0, ?)");
            $stmt->execute([$tokenHash, $expiresAt]);

            $status['setup_token'] = $rawToken;
        }

    } catch (Exception $e) {
        $status['errors'][] = $e->getMessage();
    }
}

// Handle Form Trigger or CLI
$executed = false;
if ($isCli || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_install']))) {
    runInstallation($status, $baseDir);
    $executed = true;
}

if ($isCli) {
    echo "====================================================\n";
    echo "  CHÉRIE E-COMMERCE INSTALLER (CLI MODE)\n";
    echo "====================================================\n";
    if ($status['db_connected']) {
        echo "[OK] Database connected & initialized: " . DB_NAME . "\n";
    }
    if ($status['tables_created']) {
        echo "[OK] Database tables verified & created safely.\n";
    }
    if ($status['data_seeded']) {
        echo "[OK] 21 Categories and 42 sample products seeded.\n";
    }
    if ($status['setup_token']) {
        echo "\n[IMPORTANT] ONE-TIME ADMIN SETUP TOKEN:\n";
        echo "Token: " . $status['setup_token'] . "\n";
        echo "Visit URL: http://localhost/cherie/admin/setup.php?token=" . $status['setup_token'] . "\n\n";
    }
    if (!empty($status['errors'])) {
        echo "[ERRORS OCCURRED]:\n";
        foreach ($status['errors'] as $err) {
            echo " - " . $err . "\n";
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CHÉRIE — Store Installation & Database Setup</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --blush: #F7DCE5;
            --soft-cream: #FFF9F5;
            --dusty-rose: #C58A9C;
            --chocolate: #49383C;
            --lavender: #EAE3F5;
            --white: #FFFFFF;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--soft-cream);
            color: var(--chocolate);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-card {
            background: var(--white);
            max-width: 650px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(73, 56, 60, 0.08);
            border: 1px solid rgba(197, 138, 156, 0.2);
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, var(--blush), var(--lavender));
            padding: 40px 30px;
            text-align: center;
            border-bottom: 1px solid rgba(197, 138, 156, 0.15);
        }
        .logo-title {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            letter-spacing: 4px;
            color: var(--chocolate);
            margin-bottom: 8px;
        }
        .tagline {
            font-size: 14px;
            color: var(--dusty-rose);
            letter-spacing: 1px;
            font-style: italic;
        }
        .install-body {
            padding: 35px 30px;
        }
        .step-list {
            list-style: none;
            margin: 20px 0;
        }
        .step-item {
            padding: 12px 16px;
            margin-bottom: 10px;
            border-radius: 10px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .step-item.success {
            background-color: #EBF8EE;
            color: #1F7A38;
            border: 1px solid #B8E4C3;
        }
        .step-item.error {
            background-color: #FDECEA;
            color: #C0392B;
            border: 1px solid #F5B7B1;
        }
        .token-box {
            background: #FDF4F7;
            border: 2px dashed var(--dusty-rose);
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .token-val {
            font-family: monospace;
            font-size: 18px;
            background: #FFFFFF;
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-block;
            margin: 12px 0;
            color: var(--chocolate);
            font-weight: 600;
            word-break: break-all;
            user-select: all;
        }
        .btn-primary {
            display: inline-block;
            width: 100%;
            background-color: var(--dusty-rose);
            color: var(--white);
            text-decoration: none;
            text-align: center;
            padding: 14px 24px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 15px;
            letter-spacing: 0.5px;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(197, 138, 156, 0.3);
        }
        .btn-primary:hover {
            background-color: #b07487;
            transform: translateY(-1px);
        }
        .db-info {
            background: var(--soft-cream);
            border-radius: 10px;
            padding: 15px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid #F2E4DF;
        }
    </style>
</head>
<body>

<div class="install-card">
    <div class="install-header">
        <h1 class="logo-title">CHÉRIE</h1>
        <p class="tagline">A Little Love in Every Detail.</p>
    </div>

    <div class="install-body">
        <?php if (!$executed): ?>
            <h2 style="font-family:'Playfair Display',serif; font-size: 22px; margin-bottom: 12px;">Store Database Setup</h2>
            <p style="font-size: 14px; line-height: 1.6; color: #665257; margin-bottom: 20px;">
                Welcome to <strong>CHÉRIE Boutique</strong> installation. This safe setup wizard checks your MySQL environment, creates the <code>cherie_db</code> database and all required tables without dropping any existing records, and seeds 21 product categories with 42 luxury boutique products.
            </p>

            <div class="db-info">
                <strong>Current Connection Configuration:</strong><br>
                Host: <code><?= htmlspecialchars(DB_HOST) ?>:<?= htmlspecialchars(DB_PORT) ?></code><br>
                Database Name: <code><?= htmlspecialchars(DB_NAME) ?></code><br>
                User: <code><?= htmlspecialchars(DB_USER) ?></code>
            </div>

            <form method="POST">
                <button type="submit" name="run_install" value="1" class="btn-primary">
                    Start Safe Installation & Seed Data &rarr;
                </button>
            </form>
        <?php else: ?>
            <h2 style="font-family:'Playfair Display',serif; font-size: 22px; margin-bottom: 15px;">Installation Status</h2>
            
            <ul class="step-list">
                <?php if ($status['db_connected']): ?>
                    <li class="step-item success">&#10003; Database connected and verified (<code><?= htmlspecialchars(DB_NAME) ?></code>)</li>
                <?php endif; ?>

                <?php if ($status['tables_created']): ?>
                    <li class="step-item success">&#10003; All 18 database tables verified and created safely</li>
                <?php endif; ?>

                <?php if ($status['data_seeded']): ?>
                    <li class="step-item success">&#10003; 21 Categories, 42 Products, Variants & Settings seeded successfully</li>
                <?php endif; ?>

                <?php if (!empty($status['errors'])): ?>
                    <?php foreach ($status['errors'] as $err): ?>
                        <li class="step-item error">&#10007; <?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <?php if ($status['setup_token']): ?>
                <div class="token-box">
                    <h3 style="font-family:'Playfair Display',serif; font-size: 18px; color: var(--chocolate);">
                        &#128272; One-Time Admin Setup Token Generated
                    </h3>
                    <p style="font-size: 13px; color: #766367; margin-top: 6px;">
                        Copy this secret token to create your administrator account. It will expire in 48 hours and can only be used once.
                    </p>
                    <div class="token-val"><?= htmlspecialchars($status['setup_token']) ?></div>
                    <br>
                    <a href="admin/setup.php?token=<?= urlencode($status['setup_token']) ?>" class="btn-primary" style="display:inline-block; max-width: 300px; margin-top: 10px;">
                        Create Admin Account &rarr;
                    </a>
                </div>
            <?php else: ?>
                <div style="margin: 25px 0; text-align: center;">
                    <a href="index.php" class="btn-primary" style="display:inline-block; max-width: 260px; margin: 5px;">
                        Visit Storefront &rarr;
                    </a>
                    <a href="admin/login.php" class="btn-primary" style="display:inline-block; max-width: 260px; margin: 5px; background-color: var(--chocolate);">
                        Admin Login &rarr;
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
