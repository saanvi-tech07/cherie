<?php
/**
 * CHÉRIE — Admin Logout
 */

define('CHERIE_APP', true);
require_once dirname(__DIR__) . '/config/config.php';

unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);

session_regenerate_id(true);

setFlash('info', 'You have been safely signed out from the admin portal.');
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
