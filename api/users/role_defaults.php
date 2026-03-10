<?php
require_once __DIR__ . '/../header.php';

if (isset($_GET['role_type'])) {
    $role_type = intval($_GET['role_type']);
    $stmt = $conn->prepare("SELECT CONCAT(page, ':', action) AS perm FROM role_type_permissions WHERE role_type = ?");
    $stmt->bind_param("i", $role_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row['perm'];
    }
    echo json_encode(['data' => $permissions]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'role_type is required']);
}
$conn->close();
?>
