<?php
require_once "dbconnect.php";
function hasAccess($page, $action = 'view') {
    global $conn;

    if (!isset($_SESSION['user_id'])) return false;

    // 1. Check user-specific permissions
    $stmt = $conn->prepare("
        SELECT 1 FROM role_permissions 
        WHERE user_id = ? 
          AND page = ? 
          AND (action = ? OR action = '*')
        LIMIT 1
    ");
    $stmt->bind_param("iss", $_SESSION['user_id'], $page, $action);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        return true;
    }
    $stmt->close();

    // 2. Optionally fallback to role_type_permissions (recommended)
    if (isset($_SESSION['user_type'])) {
        $role_stmt = $conn->prepare("
            SELECT 1 FROM role_type_permissions 
            WHERE role_type = ? 
              AND page = ? 
              AND (action = ? OR action = '*')
            LIMIT 1
        ");
        $role_stmt->bind_param("iss", $_SESSION['user_type'], $page, $action);
        $role_stmt->execute();
        $role_stmt->store_result();

        $hasRoleAccess = $role_stmt->num_rows > 0;
        $role_stmt->close();
        return $hasRoleAccess;
    }

    return false;
}