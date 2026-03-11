<?php
require_once __DIR__ . '/../header.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['project_name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Project name is required']);
    exit();
}

$name   = trim($data['project_name']);
$region = isset($data['region']) ? (int)$data['region'] : 1;
$email  = isset($data['email']) ? trim($data['email']) : null;
$status = 1; // Active by default

$stmt = $conn->prepare("INSERT INTO project (project_name, project_status, region, email) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siis", $name, $status, $region, $email);
$ok = $stmt->execute();

if ($ok) {
    echo json_encode([
        'success' => true, 
        'id' => $conn->insert_id, 
        'project_name' => $name,
        'region' => $region,
        'email' => $email,
        'message' => 'Project created'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create project: ' . $conn->error]);
}

$conn->close();
?>
