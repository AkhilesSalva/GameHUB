-- ============================================================================
-- Game Hub - Database Migration
-- Version: 2.0 - Major Feature Update
-- ============================================================================
-- INSTRUCTIONS:
-- 1. Buka phpMyAdmin
-- 2. Pilih database 'db_game_crud'
-- 3. Klik tab 'Import' atau 'SQL'
-- 4. Import/paste file ini
-- 5. Klik 'Go'
-- 
-- Data lama TIDAK akan terhapus!
-- ============================================================================

-- ============================================================================
-- 1. MODIFY EXISTING TABLES
-- ============================================================================

-- Add new columns to games table
ALTER TABLE `games` 
ADD COLUMN IF NOT EXISTS `file_size` VARCHAR(50) DEFAULT NULL AFTER `gambar_path`,
ADD COLUMN IF NOT EXISTS `platform` VARCHAR(100) DEFAULT 'PC' AFTER `file_size`,
ADD COLUMN IF NOT EXISTS `trailer_url` VARCHAR(255) DEFAULT NULL AFTER `platform`,
ADD COLUMN IF NOT EXISTS `system_req_min` TEXT DEFAULT NULL AFTER `trailer_url`,
ADD COLUMN IF NOT EXISTS `system_req_rec` TEXT DEFAULT NULL AFTER `system_req_min`,
ADD COLUMN IF NOT EXISTS `version` VARCHAR(50) DEFAULT NULL AFTER `system_req_rec`,
ADD COLUMN IF NOT EXISTS `changelog` TEXT DEFAULT NULL AFTER `version`,
ADD COLUMN IF NOT EXISTS `coming_soon` TINYINT(1) DEFAULT 0 AFTER `changelog`,
ADD COLUMN IF NOT EXISTS `avg_rating` DECIMAL(3,2) DEFAULT 0.00 AFTER `coming_soon`,
ADD COLUMN IF NOT EXISTS `rating_count` INT DEFAULT 0 AFTER `avg_rating`,
ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `rating_count`;

-- Add status column to users table for ban/suspend feature
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `status` ENUM('active', 'banned', 'suspended') DEFAULT 'active' AFTER `role`,
ADD COLUMN IF NOT EXISTS `avatar` VARCHAR(255) DEFAULT NULL AFTER `status`,
ADD COLUMN IF NOT EXISTS `bio` TEXT DEFAULT NULL AFTER `avatar`;

-- ============================================================================
-- 2. CREATE NEW TABLES
-- ============================================================================

-- Wishlist table
CREATE TABLE IF NOT EXISTS `wishlist` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `game_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_wishlist` (`user_id`, `game_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ratings table
CREATE TABLE IF NOT EXISTS `ratings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `game_id` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `review` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_rating` (`user_id`, `game_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Game requests table
CREATE TABLE IF NOT EXISTS `game_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `game_name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `platform` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Screenshots table
CREATE TABLE IF NOT EXISTS `screenshots` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `game_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(255) DEFAULT NULL,
    `order_num` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Download history table
CREATE TABLE IF NOT EXISTS `download_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `game_id` INT NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `downloaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Broken link reports table
CREATE TABLE IF NOT EXISTS `broken_link_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `game_id` INT NOT NULL,
    `user_id` INT DEFAULT NULL,
    `link_type` VARCHAR(50) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('pending', 'fixed', 'dismissed') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `resolved_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity log table
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `target_type` VARCHAR(50) DEFAULT NULL,
    `target_id` INT DEFAULT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Download mirrors table (for multiple download links)
CREATE TABLE IF NOT EXISTS `download_mirrors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `game_id` INT NOT NULL,
    `mirror_name` VARCHAR(100) NOT NULL,
    `link_url` TEXT NOT NULL,
    `part_number` INT DEFAULT NULL,
    `file_size` VARCHAR(50) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- 3. CREATE INDEXES FOR PERFORMANCE
-- ============================================================================

CREATE INDEX IF NOT EXISTS `idx_wishlist_user` ON `wishlist`(`user_id`);
CREATE INDEX IF NOT EXISTS `idx_wishlist_game` ON `wishlist`(`game_id`);
CREATE INDEX IF NOT EXISTS `idx_ratings_game` ON `ratings`(`game_id`);
CREATE INDEX IF NOT EXISTS `idx_download_history_user` ON `download_history`(`user_id`);
CREATE INDEX IF NOT EXISTS `idx_download_history_game` ON `download_history`(`game_id`);
CREATE INDEX IF NOT EXISTS `idx_activity_log_user` ON `activity_log`(`user_id`);
CREATE INDEX IF NOT EXISTS `idx_screenshots_game` ON `screenshots`(`game_id`);

-- ============================================================================
-- MIGRATION COMPLETE!
-- ============================================================================
