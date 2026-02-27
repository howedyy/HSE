-- RUN THIS SQL IN PHPMYADMIN OR MYSQL WORKBENCH TO ACTIVATE THE BUTTONS
-- This grants full Commenting, Editing, and Deleting permissions to HSE Users (Role Type 2) 
-- and Admin Users (Role Type 1) for the Daily Report Overview

-- Disable Safe Update Mode temporarily to allow deleting by non-key columns
SET SQL_SAFE_UPDATES = 0;

-- 1. First, make sure we don't have duplicates
DELETE FROM role_type_permissions 
WHERE page = 'dailyreport_overview.php' 
AND action IN ('add_comment', 'edit_comment', 'delete_comment', 'edit', 'delete');

-- 2. Grant permissions to Admins (Role 1)
INSERT INTO role_type_permissions (role_type, page, action) VALUES 
(1, 'dailyreport_overview.php', 'add_comment'),
(1, 'dailyreport_overview.php', 'edit_comment'),
(1, 'dailyreport_overview.php', 'delete_comment'),
(1, 'dailyreport_overview.php', 'edit'),
(1, 'dailyreport_overview.php', 'delete');

-- 3. Grant permissions to HSE Staff (Role 2)
INSERT INTO role_type_permissions (role_type, page, action) VALUES 
(2, 'dailyreport_overview.php', 'add_comment'),
(2, 'dailyreport_overview.php', 'edit_comment'),
(2, 'dailyreport_overview.php', 'delete_comment'),
(2, 'dailyreport_overview.php', 'edit'),
(2, 'dailyreport_overview.php', 'delete');

-- Re-enable Safe Update Mode
SET SQL_SAFE_UPDATES = 1;

-- 4. (Optional) If you want to grant it to YOUR specific user account specifically
-- Replace "5" with your actual User ID if you know it
-- INSERT INTO role_permissions (user_id, page, action) VALUES 
-- (5, 'dailyreport_overview.php', 'add_comment'),
-- (5, 'dailyreport_overview.php', 'edit_comment'),
-- (5, 'dailyreport_overview.php', 'delete_comment'),
-- (5, 'dailyreport_overview.php', 'edit'),
-- (5, 'dailyreport_overview.php', 'delete');
