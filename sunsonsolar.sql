-- Sun Son Solar application schema (MariaDB 10.4+ / MySQL 8+).
-- Import is non-destructive: existing rows are retained. Back up the database first.
-- This file defines the service-booking workflow; product pages currently expose a
-- catalog and support inquiries, not product checkout/order processing.

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';
SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS `sunsonsolar` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sunsonsolar`;

-- Accounts for customers, employees, and administrators.
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) NOT NULL DEFAULT '',
  `birthdate` date DEFAULT NULL,
  `gender` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(30) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'customer',
  `department` varchar(100) DEFAULT NULL,
  `job_title` varchar(100) NOT NULL DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role_department` (`role`, `department`),
  KEY `idx_users_role_job_title` (`role`, `job_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Upgrade the users-only SQL dump that this file replaces, without deleting accounts.
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `job_title` varchar(100) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS `is_active` tinyint(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `must_change_password` tinyint(1) NOT NULL DEFAULT 0;

-- Solar products displayed in the customer catalog.
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(140) NOT NULL,
  `category` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_products_active_category_name` (`is_active`, `category`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer product requests and delivery tracking (not an online payment gateway).
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `delivery_staff_id` int(10) unsigned DEFAULT NULL,
  `recipient_name` varchar(150) NOT NULL,
  `recipient_phone` varchar(30) NOT NULL,
  `delivery_address` varchar(500) NOT NULL,
  `delivery_notes` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `payment_status` varchar(30) NOT NULL DEFAULT 'to_be_confirmed',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tracking_code` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_orders_customer_created` (`customer_id`, `created_at`),
  KEY `idx_orders_delivery_status` (`delivery_staff_id`, `status`),
  KEY `idx_orders_status_created` (`status`, `created_at`),
  CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_orders_delivery_staff` FOREIGN KEY (`delivery_staff_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `product_name` varchar(140) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `idx_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services requested by customers and maintained by administrators.
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(140) NOT NULL,
  `category` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `availability` varchar(180) NOT NULL DEFAULT 'By appointment',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_services_active_name` (`is_active`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service order / appointment lifecycle and history.
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned DEFAULT NULL,
  `technician_id` int(10) unsigned DEFAULT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `customer_notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_bookings_customer_schedule` (`customer_id`, `scheduled_at`),
  KEY `idx_bookings_technician_schedule` (`technician_id`, `scheduled_at`),
  KEY `idx_bookings_service` (`service_id`),
  KEY `idx_bookings_status_schedule` (`status`, `scheduled_at`),
  CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_bookings_technician` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications support public, audience-wide, and direct (user_id) messages.
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `audience` varchar(30) NOT NULL DEFAULT 'customers',
  `type` varchar(30) NOT NULL DEFAULT 'update',
  `title` varchar(160) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_created` (`user_id`, `created_at`),
  KEY `idx_notifications_audience_created` (`audience`, `created_at`),
  KEY `idx_notifications_type` (`type`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer support requests and admin/dispatch replies.
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `subject` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'open',
  `admin_reply` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_support_tickets_customer` (`customer_id`, `id`),
  KEY `idx_support_tickets_status` (`status`),
  CONSTRAINT `fk_support_tickets_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One payment record per service booking, matching the current admin workflow.
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `receipt_number` varchar(80) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payments_booking` (`booking_id`),
  UNIQUE KEY `uq_payments_receipt` (`receipt_number`),
  KEY `idx_payments_customer_status` (`customer_id`, `status`),
  CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_payments_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Per-customer notification settings.
CREATE TABLE IF NOT EXISTS `customer_preferences` (
  `user_id` int(10) unsigned NOT NULL,
  `service_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `promotional_notifications` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_customer_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Employee field attendance, with optional booking and recorded coordinates.
CREATE TABLE IF NOT EXISTS `attendance_checkins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `booking_id` int(10) unsigned DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `checked_in_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_checkins_employee_time` (`employee_id`, `checked_in_at`),
  KEY `idx_checkins_booking` (`booking_id`),
  CONSTRAINT `fk_checkins_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_checkins_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Technician reports tied to the assigned service visit.
CREATE TABLE IF NOT EXISTS `service_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `issue` varchar(180) NOT NULL,
  `details` text NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_service_reports_booking` (`booking_id`),
  KEY `idx_service_reports_employee_created` (`employee_id`, `created_at`),
  CONSTRAINT `fk_service_reports_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_service_reports_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approved customer stories shown on the public landing page.
CREATE TABLE IF NOT EXISTS `customer_stories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `booking_id` int(10) unsigned DEFAULT NULL,
  `customer_display` varchar(100) NOT NULL,
  `service_name` varchar(140) NOT NULL,
  `quote` text NOT NULL,
  `moderation_status` varchar(20) NOT NULL DEFAULT 'approved',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_customer_stories_active_created` (`is_active`, `created_at`),
  KEY `idx_customer_stories_customer` (`customer_id`, `id`),
  KEY `idx_customer_stories_booking` (`booking_id`),
  KEY `idx_customer_stories_moderation` (`moderation_status`, `id`),
  CONSTRAINT `fk_customer_stories_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_customer_stories_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add feedback ownership/moderation fields when upgrading an earlier portal schema.
ALTER TABLE `customer_stories`
  ADD COLUMN IF NOT EXISTS `customer_id` int(10) unsigned DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `booking_id` int(10) unsigned DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `moderation_status` varchar(20) NOT NULL DEFAULT 'approved';

-- Existing account rows retained from the original SQL file. Passwords remain hashes.
INSERT IGNORE INTO `users`
  (`id`, `first_name`, `last_name`, `middle_name`, `birthdate`, `gender`, `email`, `phone_number`, `address`, `username`, `password_hash`, `role`, `department`, `created_at`)
VALUES
  (1, 'Katherine', 'Sinagaraw', 'Olap', '1990-06-01', 'Female', 'katherine.sinagaraw@sunsonsolar.com', '+639291230983', 'Pasig City', 'Kittykat16', '$2y$10$RWX0Da/Amu2ZNmyP53yf5e7D/sxEmAo8hkTHUNaQ8T3rsj9LWiTvW', 'admin', NULL, '2026-09-22 11:43:40'),
  (2, 'Sun', 'Son', 'Solis', '1967-01-08', 'Female', 'sol.solis@sunsonsolar.com', '+639987456321', 'Pasig City', 'admin', '$2y$10$Q.9NH0YvLjcoKEJ/ph/yHegWXf9.sG/7wtIj/DpsfX01C03ezg3Xq', 'admin', NULL, '2026-09-22 11:43:40'),
  (3, 'Trestan', 'Pacalioga', 'Allas', '2005-12-30', 'male', 'trestanallas1230@gmail.com', '+639945015944', '2052', 'tantan', '$2y$10$y./OOq8ywt9QdkbqGoh8YeeR4CPQ6bkJYk9eG/X5bTJhqS3hmlOUu', 'customer', NULL, '2026-09-22 11:45:14');

-- Add one temporary active dispatcher login for evaluation only when none exists.
-- Delete or rotate this test account before production deployment.
SET @dispatcher_password_hash = CONCAT('$2y$10$', SUBSTRING(SHA2(UUID(), 256), 1, 53));
INSERT INTO `users` (`first_name`, `last_name`, `middle_name`, `birthdate`, `gender`, `email`, `phone_number`, `address`, `username`, `password_hash`, `role`, `department`, `job_title`, `is_active`, `must_change_password`, `created_at`)
SELECT 'Dispatch', 'Test', '', NULL, '', 'dispatcher.test@sunsonsolar.local', '', '', 'dispatch.test', @dispatcher_password_hash, 'employee', 'Dispatch', 'Dispatcher', 1, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `role` = 'employee' AND `department` = 'Dispatch' AND `is_active` = 1);

-- Catalog starter rows match the application migration; prices can be managed in admin.
INSERT INTO `products` (`name`, `category`, `description`, `price`, `is_active`, `created_at`)
SELECT 'Solar panels', 'Panels', 'High-efficiency photovoltaic panels for homes and businesses.', 0.00, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `name` = 'Solar panels');
INSERT INTO `products` (`name`, `category`, `description`, `price`, `is_active`, `created_at`)
SELECT 'Solar inverters', 'Inverters', 'Reliable conversion and management for solar energy systems.', 0.00, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `name` = 'Solar inverters');
INSERT INTO `products` (`name`, `category`, `description`, `price`, `is_active`, `created_at`)
SELECT 'Energy storage', 'Batteries', 'Battery storage options to keep essential loads powered.', 0.00, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `name` = 'Energy storage');
INSERT INTO `products` (`name`, `category`, `description`, `price`, `is_active`, `created_at`)
SELECT 'Mounting and wiring', 'Balance of system', 'Racking, mounting hardware, and solar-rated wiring.', 0.00, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `name` = 'Mounting and wiring');

INSERT INTO `services` (`name`, `category`, `description`, `price`, `availability`, `is_active`, `created_at`)
SELECT 'Solar consultation', 'Consultation', 'Discuss your site, energy use, and solar options.', 0.00, 'By appointment', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `services` WHERE `name` = 'Solar consultation');
INSERT INTO `services` (`name`, `category`, `description`, `price`, `availability`, `is_active`, `created_at`)
SELECT 'System design and permitting', 'Design', 'Plan a system and coordinate the required permits.', 0.00, 'By appointment', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `services` WHERE `name` = 'System design and permitting');
INSERT INTO `services` (`name`, `category`, `description`, `price`, `availability`, `is_active`, `created_at`)
SELECT 'Solar installation', 'Installation', 'Professional installation and system commissioning.', 0.00, 'By appointment', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `services` WHERE `name` = 'Solar installation');
INSERT INTO `services` (`name`, `category`, `description`, `price`, `availability`, `is_active`, `created_at`)
SELECT 'Maintenance and repair', 'Maintenance', 'Keep your solar equipment performing as expected.', 0.00, 'By appointment', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `services` WHERE `name` = 'Maintenance and repair');
INSERT INTO `services` (`name`, `category`, `description`, `price`, `availability`, `is_active`, `created_at`)
SELECT 'System monitoring', 'Monitoring', 'Review production and identify performance issues.', 0.00, 'By appointment', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `services` WHERE `name` = 'System monitoring');
