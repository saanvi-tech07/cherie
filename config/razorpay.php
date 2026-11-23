<?php
/**
 * CHÉRIE — Razorpay Payment Gateway (Test Mode) Configuration
 * Only use Razorpay Test Mode keys (rzp_test_...)
 */

if (!defined('CHERIE_APP')) {
    define('CHERIE_APP', true);
}

// Razorpay Test Mode Credentials
define('RAZORPAY_ENABLED', getenv('RAZORPAY_ENABLED') !== false ? (bool)getenv('RAZORPAY_ENABLED') : true);
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_sampleKey123'); // Public key (safe for client)
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: 'sampleSecretKey123456'); // Secret key (strictly server-side)
define('RAZORPAY_CURRENCY', 'INR');
define('RAZORPAY_AUTO_CAPTURE', true);
