<?php
/**
 * CHÉRIE — A Little Love in Every Detail
 * Core Application Configuration
 */

// Define execution protection
if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}

// Environment & Error Handling
define('APP_ENV', 'development'); // 'development' or 'production'

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Default Timezone
date_default_timezone_set('Asia/Kolkata');

// Application Details
define('APP_NAME', 'CHÉRIE');
define('APP_TAGLINE', 'A Little Love in Every Detail.');
define('APP_VERSION', '1.0.0');
define('APP_CURRENCY', 'INR');
define('APP_CURRENCY_SYMBOL', '₹');

// Auto-detect Base URL dynamically
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Script path resolution
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // If we're inside /admin or /admin/products, trim back to root
    $scriptDir = preg_replace('#/admin(/.*)?$#i', '', $scriptDir);
    $scriptDir = rtrim($scriptDir, '/');
    
    define('BASE_URL', $protocol . $host . $scriptDir);
    define('ADMIN_URL', BASE_URL . '/admin');
}

// File Paths
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'includes');
define('CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'config');
define('UPLOADS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('PRODUCT_UPLOADS_PATH', UPLOADS_PATH . DIRECTORY_SEPARATOR . 'products');
define('XML_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'xml');

// Brand Aesthetics Palette Constants
define('COLOR_BLUSH_PINK', '#F7DCE5');
define('COLOR_SOFT_CREAM', '#FFF9F5');
define('COLOR_DUSTY_ROSE', '#C58A9C');
define('COLOR_CHOCOLATE', '#49383C');
define('COLOR_LAVENDER', '#EAE3F5');
define('COLOR_WHITE', '#FFFFFF');

// Security & Session Settings
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    // Ensure session save directory exists and is writable
    $sessionDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'sessions';
    if (!is_dir($sessionDir)) {
        @mkdir($sessionDir, 0755, true);
    }
    if (is_dir($sessionDir) && is_writable($sessionDir)) {
        session_save_path($sessionDir);
    }

    // Configure secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    ini_set('session.cookie_samesite', 'Lax');
    @session_start();
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include Database Connection
require_once CONFIG_PATH . DIRECTORY_SEPARATOR . 'database.php';

// Include Global Helper Functions
require_once INCLUDES_PATH . DIRECTORY_SEPARATOR . 'functions.php';
