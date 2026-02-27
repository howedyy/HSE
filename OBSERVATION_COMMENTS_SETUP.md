# Observation Comments Feature - Setup Guide

## Overview
This feature adds a comprehensive commenting system to the Daily Report Overview page with full RBAC (Role-Based Access Control) integration. Users can add, edit, and delete comments on observations, with permissions controlled by the admin.

## Database Setup

### 1. Create the Comments Table
Run the SQL script located at:
```
database/create_observation_comments_table.sql
```

Or execute this SQL directly in your database:
```sql
CREATE TABLE IF NOT EXISTS `observation_comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `report_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `comment_text` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_observation_comments_report` FOREIGN KEY (`report_id`) REFERENCES `daily_report` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_observation_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_report_created ON observation_comments(report_id, created_at DESC);
```

## RBAC Permissions Setup

### Required Permissions
The system uses the following actions for the `dailyreport_overview.php` page:

1. **add_comment** - Allows users to add new comments
2. **edit_comment** - Allows users to edit their own comments
3. **delete_comment** - Allows users to delete their own comments

### Setting Up Permissions for Users

#### Option 1: Using role_permissions (User-Specific)
Add permissions for specific users:

```sql
-- Allow user with ID 5 to add comments
INSERT INTO role_permissions (user_id, page, action) 
VALUES (5, 'dailyreport_overview.php', 'add_comment');

-- Allow user with ID 5 to edit comments
INSERT INTO role_permissions (user_id, page, action) 
VALUES (5, 'dailyreport_overview.php', 'edit_comment');

-- Allow user with ID 5 to delete comments
INSERT INTO role_permissions (user_id, page, action) 
VALUES (5, 'dailyreport_overview.php', 'delete_comment');
```

#### Option 2: Using role_type_permissions (Role-Based)
Add permissions for all users of a specific role type:

```sql
-- Allow all users with user_type = 2 (HSE Users) to add comments
INSERT INTO role_type_permissions (role_type, page, action) 
VALUES (2, 'dailyreport_overview.php', 'add_comment');

-- Allow all users with user_type = 2 to edit comments
INSERT INTO role_type_permissions (role_type, page, action) 
VALUES (2, 'dailyreport_overview.php', 'edit_comment');

-- Allow all users with user_type = 2 to delete comments
INSERT INTO role_type_permissions (role_type, page, action) 
VALUES (2, 'dailyreport_overview.php', 'delete_comment');
```

#### Option 3: Grant All Comment Permissions at Once
```sql
-- For specific user (replace 5 with actual user_id)
INSERT INTO role_permissions (user_id, page, action) VALUES
(5, 'dailyreport_overview.php', 'add_comment'),
(5, 'dailyreport_overview.php', 'edit_comment'),
(5, 'dailyreport_overview.php', 'delete_comment');

-- For all users of a role type (replace 2 with actual user_type)
INSERT INTO role_type_permissions (role_type, page, action) VALUES
(2, 'dailyreport_overview.php', 'add_comment'),
(2, 'dailyreport_overview.php', 'edit_comment'),
(2, 'dailyreport_overview.php', 'delete_comment');
```

### Checking Current Permissions
```sql
-- Check permissions for a specific user
SELECT * FROM role_permissions 
WHERE user_id = 5 AND page = 'dailyreport_overview.php';

-- Check permissions for a role type
SELECT * FROM role_type_permissions 
WHERE role_type = 2 AND page = 'dailyreport_overview.php';
```

### Removing Permissions
```sql
-- Remove specific permission from user
DELETE FROM role_permissions 
WHERE user_id = 5 AND page = 'dailyreport_overview.php' AND action = 'add_comment';

-- Remove all comment permissions from user
DELETE FROM role_permissions 
WHERE user_id = 5 AND page = 'dailyreport_overview.php' 
AND action IN ('add_comment', 'edit_comment', 'delete_comment');

-- Remove permissions from role type
DELETE FROM role_type_permissions 
WHERE role_type = 2 AND page = 'dailyreport_overview.php' 
AND action IN ('add_comment', 'edit_comment', 'delete_comment');
```

## Feature Behavior

### User Experience
1. **Viewing Comments**: When a user clicks the "+" button to expand observation details, comments are automatically loaded
2. **Adding Comments**: Users with `add_comment` permission see an "➕ Add Comment" button
3. **Editing Comments**: Users can only edit their own comments if they have `edit_comment` permission
4. **Deleting Comments**: Users can only delete their own comments if they have `delete_comment` permission

### Security Features
- Users can only edit/delete their own comments
- All actions are validated server-side with RBAC checks
- SQL injection protection via prepared statements
- XSS protection via htmlspecialchars()
- Ownership validation before edit/delete operations

## Files Created/Modified

### New Files Created:
1. `database/create_observation_comments_table.sql` - Database schema
2. `add_observation_comment.php` - Backend for adding comments
3. `edit_observation_comment.php` - Backend for editing comments
4. `delete_observation_comment.php` - Backend for deleting comments
5. `get_observation_comments.php` - Backend for fetching comments

### Modified Files:
1. `dailyreport_overview.php` - Added comment UI and functionality

## Testing the Feature

### 1. Test Adding Comments
1. Log in as a user with `add_comment` permission
2. Navigate to Daily Report Overview
3. Click "+" to expand an observation
4. Click "➕ Add Comment"
5. Enter comment text and submit
6. Verify comment appears in the list

### 2. Test Editing Comments
1. Click "✏️ Edit" on your own comment
2. Modify the text
3. Submit the changes
4. Verify the comment is updated with "(edited)" indicator

### 3. Test Deleting Comments
1. Click "🗑️ Delete" on your own comment
2. Confirm the deletion
3. Verify the comment is removed

### 4. Test Permission Restrictions
1. Log in as a user without permissions
2. Verify the "Add Comment" button doesn't appear
3. Verify edit/delete buttons don't appear on comments

## Troubleshooting

### Comments Not Loading
- Check browser console for JavaScript errors
- Verify `get_observation_comments.php` is accessible
- Check database connection

### Permission Denied Errors
- Verify user has correct permissions in database
- Check `role_permissions` or `role_type_permissions` tables
- Ensure user is logged in (check session)

### Can't Edit/Delete Own Comments
- Verify user_id matches in session and database
- Check that `edit_comment` or `delete_comment` permissions exist
- Look for JavaScript errors in browser console

## Admin User Interface Integration

To add a user interface for admins to manage these permissions, you can:

1. Create a permissions management page
2. List all users and their current permissions
3. Provide checkboxes for each permission type
4. Use AJAX to update permissions in real-time

Example SQL for admin interface:
```sql
-- Get all users with their comment permissions
SELECT 
    u.id,
    u.username,
    u.user_type,
    GROUP_CONCAT(rp.action) as user_permissions,
    GROUP_CONCAT(rtp.action) as role_permissions
FROM users u
LEFT JOIN role_permissions rp ON u.id = rp.user_id 
    AND rp.page = 'dailyreport_overview.php'
    AND rp.action IN ('add_comment', 'edit_comment', 'delete_comment')
LEFT JOIN role_type_permissions rtp ON u.user_type = rtp.role_type 
    AND rtp.page = 'dailyreport_overview.php'
    AND rtp.action IN ('add_comment', 'edit_comment', 'delete_comment')
GROUP BY u.id;
```

## Support

For issues or questions about this feature, check:
1. Database table exists and has correct structure
2. Permissions are set correctly in RBAC tables
3. User session is active
4. Browser console for JavaScript errors
5. Server error logs for PHP errors
