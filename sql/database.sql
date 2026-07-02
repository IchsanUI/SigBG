-- =====================================================================
-- SigBG - Digital Signage System
-- Database schema v1.0
-- Target: MySQL 5.7+ / MariaDB 10.3+
-- Created: 2026-07-02
-- =====================================================================

DROP DATABASE IF EXISTS `digital_signage`;
CREATE DATABASE `digital_signage` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `digital_signage`;

-- ---------------------------------------------------------------------
-- 1. admin_users
-- ---------------------------------------------------------------------
CREATE TABLE `admin_users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. media
-- ---------------------------------------------------------------------
CREATE TABLE `media` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('image','video') NOT NULL,
  `file_path` VARCHAR(255) NOT NULL COMMENT 'Path relatif dari root, ex: assets/uploads/media/abc.jpg',
  `title` VARCHAR(150) NULL,
  `duration` INT(11) UNSIGNED NULL COMMENT 'Durasi tayang (detik), khusus image. NULL untuk video.',
  `file_size` BIGINT(20) UNSIGNED NULL,
  `mime_type` VARCHAR(50) NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_media_type_deleted` (`type`, `is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. playlists
-- ---------------------------------------------------------------------
CREATE TABLE `playlists` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = fallback playlist yang diputar jika tidak ada jadwal',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_playlists_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. playlist_items
-- ---------------------------------------------------------------------
CREATE TABLE `playlist_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `playlist_id` INT(11) UNSIGNED NOT NULL,
  `media_id` INT(11) UNSIGNED NOT NULL,
  `order_index` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_playlist_media` (`playlist_id`, `media_id`),
  KEY `idx_playlist_order` (`playlist_id`, `order_index`),
  CONSTRAINT `fk_pi_playlist` FOREIGN KEY (`playlist_id`) REFERENCES `playlists`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pi_media` FOREIGN KEY (`media_id`) REFERENCES `media`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. schedules
-- ---------------------------------------------------------------------
CREATE TABLE `schedules` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `playlist_id` INT(11) UNSIGNED NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `days_of_week` VARCHAR(20) NOT NULL COMMENT '7-bit string like "1111111" (Mon..Sun), atau "everyday"',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_schedule_active` (`is_active`),
  CONSTRAINT `fk_sch_playlist` FOREIGN KEY (`playlist_id`) REFERENCES `playlists`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. ticker_settings
-- ---------------------------------------------------------------------
CREATE TABLE `ticker_settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `text_content` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `speed` INT(11) NOT NULL DEFAULT 50 COMMENT 'pixel per detik',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. login_attempts (brute force protection)
-- ---------------------------------------------------------------------
CREATE TABLE `login_attempts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attempts_ip_time` (`ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- Initial seed
-- =====================================================================

-- Default admin (username: admin, password: admin123)
-- Bcrypt hash generated by PHP password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO `admin_users` (`username`, `password_hash`) VALUES
('admin', '$2y$10$ABtmAsAh/a/G/06hvmSnbOjB1X8mQP8WmB5kqnGGNCTA25csdnDf.');

-- Default empty playlist (fallback)
INSERT INTO `playlists` (`name`, `is_default`) VALUES ('Default Playlist', 1);

-- Ticker default (off)
INSERT INTO `ticker_settings` (`text_content`, `is_active`, `speed`) VALUES
('Selamat datang di SigBG Digital Signage', 0, 50);
