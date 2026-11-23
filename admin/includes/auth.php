<?php
/**
 * CHÉRIE — Admin Authentication Check
 */

if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}

require_once dirname(__DIR__, 2) . '/config/config.php';

// Verify admin login session
if (!isAdminLoggedIn()) {
    $_SESSION['admin_redirect'] = $_SERVER['REQUEST_URI'];
    setFlash('warning', 'Please sign in to access the boutique administration suite.');
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

$currentAdmin = getCurrentAdmin();
