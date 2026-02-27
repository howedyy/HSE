<?php
require_once __DIR__ . '/../header.php';

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);
$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["message" => "Username and password are required."]);
    exit();
}

// Security Check
$stmt = $conn->prepare("SELECT id, username, password, user_type, editor_name, job_title FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    
    // Support both hashed and plain passwords during migration
    if (password_verify($password, $user['password']) || $password == $user['password']) {
        
        // Setup Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        
        // Fetch REAL permissions from role_type_permissions
        $permissions = [];
        $pstmt = $conn->prepare("SELECT page, action FROM role_type_permissions WHERE role_type = ?");
        $pstmt->bind_param("i", $user['user_type']);
        $pstmt->execute();
        $pResult = $pstmt->get_result();
        while ($pRow = $pResult->fetch_assoc()) {
            $permissions[] = [
                'page' => $pRow['page'],
                'action' => $pRow['action'],
            ];
        }
        $pstmt->close();
        
        // Also fetch user-specific permissions
        $upstmt = $conn->prepare("SELECT page, action FROM role_permissions WHERE user_id = ?");
        $upstmt->bind_param("i", $user['id']);
        $upstmt->execute();
        $upResult = $upstmt->get_result();
        while ($upRow = $upResult->fetch_assoc()) {
            $permissions[] = [
                'page' => $upRow['page'],
                'action' => $upRow['action'],
            ];
        }
        $upstmt->close();

        // Prepare Response
        $response = [
            "success" => true,
            "user" => [
                "id" => (int)$user['id'],
                "username" => $user['username'],
                "role" => (int)$user['user_type'],
                "editorName" => $user['editor_name'] ?? '',
                "jobTitle" => $user['job_title'] ?? '',
            ],
            "permissions" => $permissions,
            "token" => session_id()
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Incorrect password."]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "User does not exist."]);
}

$stmt->close();
$conn->close();
?>
