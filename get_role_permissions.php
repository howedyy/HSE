<?php
require_once "constants/dbconnect.php";

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
    echo json_encode($permissions);
}
?>