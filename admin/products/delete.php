<?php
/**
 * CHÉRIE — Delete Product Action
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && Database::isConnected()) {
            Database::execute("DELETE FROM products WHERE id = ?", [$id]);
            setFlash('info', 'Piece has been permanently deleted from the boutique catalog.');
        }
    } else {
        setFlash('danger', 'Security validation failed.');
    }
}

header('Location: ' . ADMIN_URL . '/products/index.php');
exit;
