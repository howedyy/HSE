<?php
require_once __DIR__ . '/../header.php';

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$id   = isset($data['id']) ? (int)$data['id'] : 0;

if (!$id || !$type) {
    http_response_code(400);
    echo json_encode(['error' => 'id and type are required']);
    exit;
}

if ($type === 'observation') {
    // Get current status
    $res = $conn->query("SELECT status FROM report_observation_types WHERE id = $id");
    if (!$res || $res->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        exit;
    }
    $row = $res->fetch_assoc();
    $newStatus = $row['status'] == 1 ? 0 : 1;
    $conn->query("UPDATE report_observation_types SET status = $newStatus WHERE id = $id");
    echo json_encode(['id' => $id, 'status' => $newStatus]);
} elseif ($type === 'work_type') {
    $res = $conn->query("SELECT status FROM report_work_types WHERE id = $id");
    if (!$res || $res->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        exit;
    }
    $row = $res->fetch_assoc();
    $newStatus = $row['status'] == 1 ? 0 : 1;
    $conn->query("UPDATE report_work_types SET status = $newStatus WHERE id = $id");
    echo json_encode(['id' => $id, 'status' => $newStatus]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid type']);
}

$conn->close();
?>
