-- Add image_path column to observation_comments table
ALTER TABLE `observation_comments` ADD COLUMN `image_path` TEXT NULL AFTER `comment_text`;
