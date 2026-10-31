-- ============================================================================
-- Banana Quiz Game - Database Schema
-- CIS046-3 Software For Enterprise - University Assignment
-- Author: University Student (with AI pair-programming assistance acknowledged)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `banana_game` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `banana_game`;

-- ----------------------------------------------------------------------------
-- Table: users
-- Stores registered user accounts with virtual identities and secure password hashes.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: game_results
-- Stores game session records, scores, questions attempted, and accuracy.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `game_results` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `score` INT NOT NULL DEFAULT 0,
    `total_questions` INT NOT NULL DEFAULT 0,
    `correct_answers` INT NOT NULL DEFAULT 0,
    `accuracy` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `played_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_game_results_user` (`user_id`),
    INDEX `idx_game_results_date` (`played_at`),
    CONSTRAINT `fk_game_results_user` 
        FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Seed Data for Testing & Demonstration
-- Default demo user:
--   Email:    test@example.com
--   Username: testuser
--   Password: Password123!
-- ----------------------------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'testuser', 'test@example.com', '$2y$10$ygvdalGM97vEUpDsixTJu.rpkzrYYmmoI0AU0QFP6eY7fWEU.rsLC', NOW())
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `game_results` (`id`, `user_id`, `score`, `total_questions`, `correct_answers`, `accuracy`, `played_at`) VALUES
(1, 1, 50, 5, 5, 100.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 1, 40, 5, 4, 80.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 1, 30, 5, 3, 60.00, NOW())
ON DUPLICATE KEY UPDATE `id`=`id`;
