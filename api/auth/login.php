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

// 1. Fetch user from 'users' table in current connection (which is 'hse')
$stmt = $conn->prepare("SELECT id, username, password, user_type, editor_name, job_title FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // 2. Check password (supports both plain and hashed)
    $password_matches = ($password === $user['password'] || password_verify($password, $user['password']));
    
    if ($password_matches) {
        // 3. Setup Session
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        
        // 4. Fetch Permissions
        $permissions = [];
        // Role-based
        $pstmt = $conn->prepare("SELECT page, action FROM role_type_permissions WHERE role_type = ?");
        $role_type = (int)$user['user_type'];
        $pstmt->bind_param("i", $role_type);
        $pstmt->execute();
        $pres = $pstmt->get_result();
        while($prow = $pres->fetch_assoc()) $permissions[] = $prow;
        $pstmt->close();
        
        // User-specific
        $upstmt = $conn->prepare("SELECT page, action FROM role_permissions WHERE user_id = ?");
        $user_id = (int)$user['id'];
        $upstmt->bind_param("i", $user_id);
        $upstmt->execute();
        $ures = $upstmt->get_result();
        while($urow = $ures->fetch_assoc()) $permissions[] = $urow;
        $upstmt->close();

        echo json_encode([
            "success" => true,
            "user" => [
                "id" => $user_id,
                "username" => $user['username'],
                "role" => $role_type,
                "editorName" => $user['editor_name'] ?? '',
                "jobTitle" => $user['job_title'] ?? ''
            ],
            "permissions" => $permissions,
            "token" => session_id()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Invalid password for user '$username'."]);
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "User '$username' not found."]);
}

$stmt->close();
$conn->close();
?>
