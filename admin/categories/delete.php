<?php
/**
 * CHÉRIE — Delete Category
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && Database::isConnected()) {
            $hasProducts = Database::fetchOne("SELECT id FROM products WHERE category_id = ? LIMIT 1", [$id]);
            if ($hasProducts) {
                setFlash('danger', 'Cannot delete category: products are assigned to it.');
            } else {
                Database::execute("DELETE FROM categories WHERE id = ?", [$id]);
                setFlash('info', 'Category deleted successfully.');
            }
        }
    }
}

header('Location: ' . ADMIN_URL . '/categories/index.php');
exit;
