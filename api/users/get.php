<?php
require_once __DIR__ . '/../header.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID is required']);
    exit();
}

$user_id = intval($_GET['id']);

// Fetch user details
$user_stmt = $conn->prepare("SELECT u.*, d.department_name FROM users u LEFT JOIN department d ON u.department_id = d.id WHERE u.id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit();
}

$user = $user_result->fetch_assoc();

// Unified Permissions fetching
$user_permissions = [];

// 1. Role Defaults
$role_type = intval($user['user_type']);
$default_stmt = $conn->prepare("SELECT page, action FROM role_type_permissions WHERE role_type = ?");
$default_stmt->bind_param("i", $role_type);
$default_stmt->execute();
$default_result = $default_stmt->get_result();
while ($default = $default_result->fetch_assoc()) {
    $user_permissions[] = trim($default['page']) . ':' . trim($default['action']);
}
$default_stmt->close();

// 2. Individual Overrides/Additions
$perm_query = $conn->prepare("SELECT page, action FROM role_permissions WHERE user_id = ?");
$perm_query->bind_param("i", $user_id);
$perm_query->execute();
$perm_result = $perm_query->get_result();
while ($perm = $perm_result->fetch_assoc()) {
    $user_permissions[] = trim($perm['page']) . ':' . trim($perm['action']);
}
$perm_query->close();

// Filter unique permissions to avoid duplicates in the UI
$user_permissions = array_values(array_unique($user_permissions));

// Clean sensitive data
unset($user['password']);

echo json_encode([
    'data' => [
        'user' => $user,
        'permissions' => $user_permissions
    ]
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>
