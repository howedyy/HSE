<?php
require_once __DIR__ . '/../header.php';

// Fetch all available permissions grouped by page (DISTINCT to avoid duplicates from standardization)
$permQuery = $conn->query("SELECT DISTINCT page_name, action, description FROM role_type ORDER BY page_name, action");
$permissions = [];

if ($permQuery) {
    while ($row = $permQuery->fetch_assoc()) {
        $permissions[] = [
            'page' => $row['page_name'],
            'action' => $row['action'],
            'description' => $row['description'] ?: ucfirst(pathinfo($row['page_name'], PATHINFO_FILENAME)) . ' (' . ucfirst($row['action']) . ')',
            'value' => trim($row['page_name']) . ':' . trim($row['action'])
        ];
    }
}

echo json_encode(['data' => $permissions], JSON_UNESCAPED_UNICODE);
$conn->close();
?>
