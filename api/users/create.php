<?php
require_once __DIR__ . '/../header.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['username']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$username     = trim($data['username']);
$password     = password_hash($data['password'], PASSWORD_DEFAULT);
$user_type    = intval($data['userType'] ?? 2);
$dept_id      = !empty($data['departmentId']) ? intval($data['departmentId']) : null;
$user_status  = intval($data['userStatus'] ?? 1);
$editor_name  = trim($data['editorName'] ?? '');
$job_title    = trim($data['jobTitle'] ?? '');

// Check for duplicate username
$check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'Username already exists']);
    exit();
}

// Insert user
$stmt = $conn->prepare(
    "INSERT INTO users (username, password, user_type, department_id, user_status, editor_name, job_title)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("ssiisis", $username, $password, $user_type, $dept_id, $user_status, $editor_name, $job_title);
$ok = $stmt->execute();

if (!$ok) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create user: ' . $conn->error]);
    exit();
}

$new_user_id = $conn->insert_id;

// Insert permissions
$permissions = $data['permissions'] ?? [];
if (!empty($permissions)) {
    $perm_stmt = $conn->prepare(
        "INSERT INTO role_permissions (user_id, user_type, page, action) VALUES (?, ?, ?, ?)"
    );
    foreach ($permissions as $perm_str) {
        $parts = explode(':', $perm_str);
        if (count($parts) === 2) {
            $page   = $parts[0];
            $action = $parts[1];
            $perm_stmt->bind_param("iiss", $new_user_id, $user_type, $page, $action);
            $perm_stmt->execute();
        }
    }
}

echo json_encode(['success' => true, 'id' => $new_user_id, 'message' => 'User created successfully']);
$conn->close();
?>
