<?php
require_once __DIR__ . '/../header.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT id, username, user_type, editor_name, job_title FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Fetch permissions
        $permissions = [];
        $pstmt = $conn->prepare("SELECT page, action FROM role_type_permissions WHERE role_type = ?");
        $pstmt->bind_param("i", $user['user_type']);
        $pstmt->execute();
        $pResult = $pstmt->get_result();
        while ($pRow = $pResult->fetch_assoc()) {
            $permissions[] = ['page' => $pRow['page'], 'action' => $pRow['action']];
        }
        $pstmt->close();
        
        // Also user-specific permissions
        $upstmt = $conn->prepare("SELECT page, action FROM role_permissions WHERE user_id = ?");
        $upstmt->bind_param("i", $user['id']);
        $upstmt->execute();
        $upResult = $upstmt->get_result();
        while ($upRow = $upResult->fetch_assoc()) {
            $permissions[] = ['page' => $upRow['page'], 'action' => $upRow['action']];
        }
        $upstmt->close();

        echo json_encode([
            "isAuthenticated" => true,
            "user" => [
                "id" => (int)$user['id'],
                "username" => $user['username'],
                "role" => (int)$user['user_type'],
                "editorName" => $user['editor_name'] ?? '',
                "jobTitle" => $user['job_title'] ?? '',
            ],
            "permissions" => $permissions,
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(401);
        echo json_encode(["isAuthenticated" => false, "message" => "User not found."]);
    }
    $stmt->close();
} else {
    http_response_code(401);
    echo json_encode(["isAuthenticated" => false, "message" => "Not logged in."]);
}

$conn->close();
?>
