<?php
require_once __DIR__ . '/../header.php';

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$name = trim($data['name'] ?? '');
$parent_id = isset($data['observation_type_id']) ? (int)$data['observation_type_id'] : null;

if (!$name) {
    http_response_code(400);
    echo json_encode(['error' => 'Name is required']);
    exit;
}

if ($type === 'observation') {
    $stmt = $conn->prepare("INSERT INTO report_observation_types (name, status) VALUES (?, 1)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    echo json_encode(['id' => $conn->insert_id, 'name' => $name, 'status' => 1]);
} elseif ($type === 'work_type') {
    if (!$parent_id) {
        http_response_code(400);
        echo json_encode(['error' => 'observation_type_id is required for work_type']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO report_work_types (name, observation_type_id, status) VALUES (?, ?, 1)");
    $stmt->bind_param("si", $name, $parent_id);
    $stmt->execute();
    echo json_encode(['id' => $conn->insert_id, 'name' => $name, 'status' => 1]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid type']);
}

$conn->close();
?>
