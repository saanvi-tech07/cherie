-- ===================================================
-- CHÉRIE E-COMMERCE DATABASE SCHEMA
-- Database: cherie_db
-- Character Set: utf8mb4, Collation: utf8mb4_unicode_ci
-- Safe execution: Uses CREATE TABLE IF NOT EXISTS
-- ===================================================

CREATE DATABASE IF NOT EXISTS `cherie_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cherie_db`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(100) NULL,
    `postal_code` VARCHAR(20) NULL,
    `country` VARCHAR(100) DEFAULT 'India',
    `role` ENUM('customer', 'admin') DEFAULT 'customer',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_email` (`email`),
    INDEX `idx_user_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Admins Table (Dedicated Role-Based Management)
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(60) NOT NULL UNIQUE,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `role` ENUM('superadmin', 'manager', 'editor') DEFAULT 'superadmin',
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_admin_username` (`username`),
    INDEX `idx_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. One-Time Secure Admin Setup Tokens
CREATE TABLE IF NOT EXISTS `admin_setup_tokens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `token_hash` VARCHAR(255) NOT NULL,
    `is_used` TINYINT(1) DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Categories Table (21 Categories Supported)
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `image_url` VARCHAR(255) NULL,
    `parent_id` INT UNSIGNED NULL DEFAULT NULL,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_category_slug` (`slug`),
    INDEX `idx_category_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Products Table
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(280) NOT NULL UNIQUE,
    `sku` VARCHAR(60) NOT NULL UNIQUE,
    `description` LONGTEXT NOT NULL,
    `short_description` VARCHAR(500) NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `compare_at_price` DECIMAL(10,2) NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `low_stock_threshold` INT DEFAULT 5,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_new` TINYINT(1) DEFAULT 1,
    `is_active` TINYINT(1) DEFAULT 1,
    `rating_avg` DECIMAL(3,2) DEFAULT 5.00,
    `reviews_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
    INDEX `idx_product_slug` (`slug`),
    INDEX `idx_product_category` (`category_id`),
    INDEX `idx_product_featured` (`is_featured`),
    INDEX `idx_product_active` (`is_active`),
    INDEX `idx_product_price` (`price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Product Images Table (Multiple Images per product)
CREATE TABLE IF NOT EXISTS `product_images` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `is_primary` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_image_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Product Variants (Sizes, Shades, Styles)
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `variant_type` VARCHAR(50) NOT NULL, -- 'Size', 'Color', 'Shade'
    `variant_value` VARCHAR(100) NOT NULL, -- 'XS', 'S', 'M', 'L', 'Soft Blush'
    `additional_price` DECIMAL(10,2) DEFAULT 0.00,
    `stock_quantity` INT DEFAULT 20,
    `sku` VARCHAR(70) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_variant_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Persistent Shopping Cart Header
CREATE TABLE IF NOT EXISTS `cart` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `session_id` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_cart_session` (`session_id`),
    INDEX `idx_cart_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Cart Items
CREATE TABLE IF NOT EXISTS `cart_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cart_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `variant_id` INT UNSIGNED NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`cart_id`) REFERENCES `cart`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Wishlist
CREATE TABLE IF NOT EXISTS `wishlist` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_product` (`user_id`, `product_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT UNSIGNED NULL,
    `customer_name` VARCHAR(150) NOT NULL,
    `customer_email` VARCHAR(191) NOT NULL,
    `customer_phone` VARCHAR(30) NOT NULL,
    `shipping_address` TEXT NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `state` VARCHAR(100) NOT NULL,
    `postal_code` VARCHAR(20) NOT NULL,
    `country` VARCHAR(100) DEFAULT 'India',
    `payment_method` ENUM('cod', 'razorpay') DEFAULT 'cod',
    `payment_status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    `order_status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    `subtotal` DECIMAL(10,2) NOT NULL,
    `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `razorpay_order_id` VARCHAR(100) NULL,
    `razorpay_payment_id` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_order_number` (`order_number`),
    INDEX `idx_order_user` (`user_id`),
    INDEX `idx_order_status` (`order_status`),
    INDEX `idx_order_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Order Items
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `product_sku` VARCHAR(60) NOT NULL,
    `variant_info` VARCHAR(150) NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL,
    `total_price` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Payments Log Table
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL,
    `transaction_id` VARCHAR(100) NULL,
    `payment_gateway` VARCHAR(50) DEFAULT 'razorpay',
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(10) DEFAULT 'INR',
    `status` VARCHAR(50) NOT NULL,
    `gateway_response` LONGTEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    INDEX `idx_payment_order` (`order_id`),
    INDEX `idx_payment_trans` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Customer Reviews & Ratings
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `customer_name` VARCHAR(150) NOT NULL,
    `customer_email` VARCHAR(191) NOT NULL,
    `rating` TINYINT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NULL,
    `comment` TEXT NOT NULL,
    `is_approved` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_review_product` (`product_id`),
    INDEX `idx_review_approved` (`is_approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Password Reset Tokens
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_reset_email` (`email`),
    INDEX `idx_reset_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Contact Messages
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `replied_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_contact_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Newsletter Subscribers
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `is_active` TINYINT(1) DEFAULT 1,
    `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Website Settings
CREATE TABLE IF NOT EXISTS `website_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `setting_group` VARCHAR(50) DEFAULT 'general',
    `description` VARCHAR(255) NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
