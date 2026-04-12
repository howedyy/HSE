-- Create observation_comments table
CREATE TABLE IF NOT EXISTS `observation_comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `report_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `comment_text` TEXT NOT NULL,
  `image_path` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_observation_comments_report` FOREIGN KEY (`report_id`) REFERENCES `daily_report` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_observation_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for better performance
CREATE INDEX idx_report_created ON observation_comments(report_id, created_at DESC);
