<?php
require_once __DIR__ . '/../header.php';

// Get POST data (as JSON)
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit();
}

$user_id = intval($data['id']);
$username = $data['username'];
$user_type = intval($data['userType']);
$department_id = intval($data['departmentId']);
$user_status = intval($data['status']);
$editor_name = $data['editorName'];
$job_title = $data['jobTitle'];

// Update password if provided
$password_sql = '';
$stmt_types = 'sisisi';
$stmt_params = [$username, $user_type, $editor_name, $job_title, $department_id, $user_status];

if (!empty($data['password'])) {
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $password_sql = ', password = ?';
    $stmt_types .= 's';
    $stmt_params[] = $password;
}

// Update user info
$update_stmt = $conn->prepare("UPDATE users SET username = ?, user_type = ?, editor_name = ?, job_title = ?, department_id = ?, user_status = ?$password_sql WHERE id = ?");
$stmt_types .= 'i';
$stmt_params[] = $user_id;

$update_stmt->bind_param($stmt_types, ...$stmt_params);
$update_success = $update_stmt->execute();

if ($update_success) {
    // Delete existing permissions
    $delete_stmt = $conn->prepare("DELETE FROM role_permissions WHERE user_id = ?");
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();
    
    // Insert new permissions
    $submitted_permissions = $data['permissions'] ?? [];
    if (!empty($submitted_permissions)) {
        $perm_stmt = $conn->prepare("INSERT INTO role_permissions (user_id, user_type, page, action) VALUES (?, ?, ?, ?)");
        foreach ($submitted_permissions as $perm_str) {
            $parts = explode(':', $perm_str);
            if (count($parts) === 2) {
                $page = $parts[0];
                $action = $parts[1];
                $perm_stmt->bind_param("iiss", $user_id, $user_type, $page, $action);
                $perm_stmt->execute();
            }
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error updating user: ' . $conn->error]);
}

$conn->close();
?>
