-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for bookkepz_db
CREATE DATABASE IF NOT EXISTS `bookkepz_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `bookkepz_db`;

-- Dumping structure for table bookkepz_db.customers
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_id` varchar(20) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `business_id` (`business_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.customers: ~8 rows (approximately)
INSERT INTO `customers` (`id`, `business_id`, `business_name`, `email`, `phone`, `address`) VALUES
	(1, '606E0B7F', 'BOOKKEPZ PH', 'bookkepzofficial@gmail.com', '09453353402', 'TAGBILARAN CITY'),
	(2, '813FAD6A', 'ANT MART', 'bookkepzofficial@gmail.com', '09453353402', ''),
	(3, 'D9547BBB', 'DUNKIN', 'bookkepzofficial@gmail.com', '09453353402', ''),
	(4, 'E7305EE5', 'MCDO', 'bookkepzofficial@gmail.com', '09453353402', ''),
	(5, '4611A4D0', 'JOBILE', 'bookkepzofficial@gmail.com', '09453353402', ''),
	(6, '710559CB', 'Cocoberry', '', '', ''),
	(7, '65C9939B', 'Inasal', '', '', ''),
	(8, 'ADB284F5', 'CHOWKING', '', '', ''),
	(9, '91544CFF', 'KFC', '', '', '');

-- Dumping structure for table bookkepz_db.invoices
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_ref` varchar(50) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `business_id` varchar(20) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `sale` decimal(15,2) DEFAULT 0.00,
  `cost` decimal(15,2) DEFAULT 0.00,
  `expenses` decimal(15,2) DEFAULT 0.00,
  `income` decimal(15,2) DEFAULT 0.00,
  `vat` decimal(15,2) DEFAULT 0.00,
  `discount` decimal(15,2) DEFAULT 0.00,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `invoice_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Active',
  `terms` varchar(50) DEFAULT NULL,
  `items` text DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `shipping` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_customer_invoice` (`business_id`,`invoice_ref`) USING BTREE,
  CONSTRAINT `fk_invoices_customer` FOREIGN KEY (`business_id`) REFERENCES `customers` (`business_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=386 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.invoices: ~15 rows (approximately)
INSERT INTO `invoices` (`id`, `invoice_ref`, `invoice_number`, `business_id`, `customer_name`, `sale`, `cost`, `expenses`, `income`, `vat`, `discount`, `balance`, `paid`, `invoice_date`, `due_date`, `status`, `terms`, `items`, `qty`, `rate`, `subtotal`, `tax`, `shipping`, `total`, `notes`, `attachments`, `created_at`) VALUES
	(371, 'INV-20/11/2025-01', NULL, '813FAD6A', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-11-20', '2025-11-21', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, '', '["file_691eaa1e62d80.xlsx"]', '2025-11-20 05:41:50'),
	(372, '69696969', NULL, '606E0B7F', 'BOOKKEPZ PH', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 100.00, 5000.00, 0.00, '2025-11-13', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:24'),
	(373, '9453353402', NULL, '606E0B7F', 'BOOKKEPZ PH', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 100.00, 2000.00, 0.00, '2025-11-13', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:24'),
	(374, '69696969', NULL, '813FAD6A', 'ANT MART', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 100.00, 5000.00, 0.00, '2025-11-13', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25'),
	(375, '9453353402', NULL, '813FAD6A', 'ANT MART', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 100.00, 2000.00, 0.00, '2025-11-13', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25'),
	(376, '69696969', NULL, 'D9547BBB', 'DUNKIN', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 100.00, 5000.00, 0.00, '2025-11-14', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25'),
	(377, '9453353402', NULL, 'D9547BBB', 'DUNKIN', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 100.00, 2000.00, 0.00, '2025-11-14', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25'),
	(378, '69696969', NULL, 'E7305EE5', 'MCDO', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 0.00, 5000.00, 0.00, '2025-11-14', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25'),
	(379, '9453353402', NULL, 'E7305EE5', 'MCDO', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 0.00, 2000.00, 0.00, '2025-11-15', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25'),
	(380, '69696969', NULL, '4611A4D0', 'JOBILE', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 0.00, 2000.00, 0.00, '2025-11-15', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25'),
	(381, '9453353402', NULL, '4611A4D0', 'JOBILE', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 0.00, 2000.00, 0.00, '2025-11-15', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25'),
	(382, '69696969', NULL, '710559CB', 'Cocoberry', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 0.00, 2000.00, 0.00, '2025-11-15', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25'),
	(383, '9453353402', NULL, '710559CB', 'Cocoberry', 10000.00, 7000.00, 5000.00, 3000.00, 1.12, 0.00, 2000.00, 0.00, '2025-11-15', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25'),
	(384, '9453353402', NULL, '65C9939B', 'Inasal', 0.00, 0.00, 0.00, 0.00, 1.12, 0.00, 2000.00, 0.00, '2025-11-15', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25'),
	(385, '69696969', NULL, '65C9939B', 'Inasal', 0.00, 0.00, 0.00, 0.00, 1.12, 0.00, 2000.00, 0.00, '2025-11-15', '2025-11-15', 'Active', NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '["invoice_import_1763617344.xlsx"]', '2025-11-20 05:42:25');

-- Dumping structure for table bookkepz_db.invoice_items
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.invoice_items: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table bookkepz_db.migrations: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `label` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.permissions: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.role_permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('admin','staff') NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.role_permissions: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.service_type
CREATE TABLE IF NOT EXISTS `service_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.service_type: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.staff_permissions
CREATE TABLE IF NOT EXISTS `staff_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `can_access` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `staff_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.staff_permissions: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `role` enum('admin','staff') DEFAULT 'admin',
  `invite_token` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `invited_by` int(11) DEFAULT NULL,
  `invitation_token` varchar(255) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `activated_at` datetime DEFAULT NULL,
  `invite_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.users: ~3 rows (approximately)
INSERT INTO `users` (`id`, `first_name`, `last_name`, `job_title`, `bio`, `business_name`, `address`, `country`, `phone`, `website`, `profile_image`, `email`, `password`, `reset_token`, `reset_expires`, `role`, `invite_token`, `status`, `created_at`, `is_active`, `invited_by`, `invitation_token`, `is_online`, `activated_at`, `invite_expires`) VALUES
	(5, 'ADMIN', 'ADMIN', 'IT', 'GWAPO KO NAMAN!', 'Bookkepz', 'Tagbilaran City', 'Philippines', '99999999999', '', 'uploads/profile/1761804983_Happy Bubble Tea GIF by Mira & Ink.gif', 'admin@bookkepz.com', '$2y$10$FTp2YZ6oNjKXHaHA9H.47Owyeqj9JY./mS75dhl71Cs7dd6.PZfg.', NULL, NULL, 'admin', NULL, 'active', '2025-10-29 03:34:46', 1, NULL, NULL, 0, NULL, NULL),
	(9, 'STAFF', '1', '', '', 'Bookkepz', 'Tagbilaran City', 'Philippines', '99999999999', '', 'uploads/profile/1761807911_Happy Bubble Tea GIF by Mira & Ink.gif', 'staff1@bookkepz.com', '$2y$10$iQLZG3mdZowQvXcdJnoiyeRKZGqNbjKEtebaQ351v4bMgMRYolix2', NULL, NULL, 'staff', NULL, 'active', '2025-10-29 07:35:15', 1, NULL, NULL, 0, NULL, NULL),
	(13, 'STAFF', '2', NULL, NULL, 'Bookkepz', 'Tagbilaran City', NULL, '99999999999', NULL, NULL, 'staff2@bookkepz.com', '$2y$10$KJnKvFnMi3Mi6NRwTkQEj.kXwK39og0xv/P0v..txT5CJvBYI3V5e', NULL, NULL, 'staff', NULL, 'active', '2025-10-30 06:21:09', 1, NULL, NULL, 0, NULL, NULL);

-- Dumping structure for table bookkepz_db.user_permissions
CREATE TABLE IF NOT EXISTS `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `can_reports` tinyint(1) DEFAULT 0,
  `can_sales` tinyint(1) DEFAULT 0,
  `can_payroll` tinyint(1) DEFAULT 0,
  `can_clients` tinyint(1) DEFAULT 0,
  `can_settings` tinyint(1) DEFAULT 0,
  `can_view_reports` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_payroll` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_sales` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_inventory` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.user_permissions: ~0 rows (approximately)
INSERT INTO `user_permissions` (`id`, `user_id`, `email`, `can_reports`, `can_sales`, `can_payroll`, `can_clients`, `can_settings`, `can_view_reports`, `can_manage_payroll`, `can_manage_sales`, `can_manage_inventory`) VALUES
	(6, 9, 'staff1@bookkepz.com', 0, 0, 0, 0, 0, 0, 0, 1, 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
