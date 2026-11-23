<?php
/**
 * CHÉRIE — Customer Logout
 */

define('CHERIE_APP', true);
require_once __DIR__ . '/config/config.php';

unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);
unset($_SESSION['user_role']);

session_regenerate_id(true);

setFlash('info', 'You have been safely signed out. We hope to see you again soon! ✨');
header('Location: ' . BASE_URL . '/index.php');
exit;
