<?php
require_once __DIR__ . '/../header.php';

$sql = "SELECT u.id, u.username, u.user_type, u.user_status, u.editor_name, u.job_title,
        d.department_name
        FROM users u LEFT JOIN department d ON u.department_id = d.id ORDER BY u.id ASC";

$result = $conn->query($sql);
$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => (int)$row['id'],
            'username' => $row['username'],
            'userType' => (int)$row['user_type'],
            'userTypeName' => match((int)$row['user_type']) { 1 => 'Admin', 2 => 'HSE', 3 => 'Operation', default => 'Unknown' },
            'department' => $row['department_name'] ?? 'N/A',
            'status' => (int)$row['user_status'] === 1 ? 'Active' : 'Inactive',
            'editorName' => $row['editor_name'] ?? '',
            'jobTitle' => $row['job_title'] ?? '',
        ];
    }
}

echo json_encode(['data' => $users], JSON_UNESCAPED_UNICODE);
$conn->close();
?>
