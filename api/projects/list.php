<?php
require_once __DIR__ . '/../header.php';

$result = $conn->query("SELECT id, project_name, project_status, region, email FROM project ORDER BY project_name ASC");
$projects = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = [
            'id'             => (int)$row['id'],
            'project_name'   => $row['project_name'],
            'project_status' => (int)$row['project_status'],
            'region'         => $row['region'] !== null ? (int)$row['region'] : null,
            'email'          => $row['email'],
        ];
    }
}

echo json_encode(['data' => $projects], JSON_UNESCAPED_UNICODE);
$conn->close();
?>
