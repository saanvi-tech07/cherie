<?php
/**
 * CHÉRIE — Mail Configuration
 * Configure your SMTP credentials here or via environment variables.
 */

if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}

define('MAIL_ENABLED', getenv('MAIL_ENABLED') !== false ? (bool)getenv('MAIL_ENABLED') : false);
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.gmail.com');
define('MAIL_PORT', (int)(getenv('MAIL_PORT') ?: 587));
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: 'your-email@gmail.com');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: 'your-app-password');
define('MAIL_ENCRYPTION', getenv('MAIL_ENCRYPTION') ?: 'tls'); // 'tls' or 'ssl'
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: 'orders@cherieboutique.com');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'CHÉRIE Boutique');
define('ADMIN_NOTIFICATION_EMAIL', getenv('ADMIN_NOTIFICATION_EMAIL') ?: 'admin@cherieboutique.com');
