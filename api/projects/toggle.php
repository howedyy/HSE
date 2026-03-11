<?php
require_once __DIR__ . '/../header.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Project ID required']);
    exit();
}

$project_id = intval($data['id']);

// Flip status
$stmt = $conn->prepare(
    "UPDATE project SET project_status = CASE WHEN project_status = 1 THEN 0 ELSE 1 END WHERE id = ?"
);
$stmt->bind_param("i", $project_id);
$ok = $stmt->execute();

if ($ok) {
    // Return new status
    $row = $conn->query("SELECT project_status FROM project WHERE id = $project_id")->fetch_assoc();
    echo json_encode(['success' => true, 'project_status' => (int)$row['project_status']]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to toggle project status']);
}

$conn->close();
?>
