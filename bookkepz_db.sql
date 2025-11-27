-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
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
CREATE DATABASE IF NOT EXISTS `bookkepz_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `bookkepz_db`;

-- Dumping structure for table bookkepz_db.customers
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.customers: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.invoices
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `service_type_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `qty` int DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.invoices: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.invoice_items
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `qty` int DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.invoice_items: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `label` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.permissions: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.role_permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role` enum('admin','staff') COLLATE utf8mb4_general_ci NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.role_permissions: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.service_type
CREATE TABLE IF NOT EXISTS `service_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.service_type: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.staff_permissions
CREATE TABLE IF NOT EXISTS `staff_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `module` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `can_access` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `staff_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.staff_permissions: ~0 rows (approximately)

-- Dumping structure for table bookkepz_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `job_title` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_general_ci,
  `business_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `country` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `role` enum('admin','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'admin',
  `invite_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `invited_by` int DEFAULT NULL,
  `invitation_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT '0',
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
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `can_reports` tinyint(1) DEFAULT '0',
  `can_sales` tinyint(1) DEFAULT '0',
  `can_payroll` tinyint(1) DEFAULT '0',
  `can_clients` tinyint(1) DEFAULT '0',
  `can_settings` tinyint(1) DEFAULT '0',
  `can_view_reports` tinyint(1) NOT NULL DEFAULT '0',
  `can_manage_payroll` tinyint(1) NOT NULL DEFAULT '0',
  `can_manage_sales` tinyint(1) NOT NULL DEFAULT '0',
  `can_manage_inventory` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table bookkepz_db.user_permissions: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
