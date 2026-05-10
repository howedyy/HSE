<?php
require_once __DIR__ . '/../header.php';

// Check for admin permission (assuming role 1 is admin)
$user_role = (int)($_SESSION['user_type'] ?? 0);
if ($user_role !== 1) {
    http_response_code(403);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? ''; // 'operation' or 'measure'
$name = $input['name'] ?? '';
$risk = $input['risk'] ?? '';

if (empty($type) || empty($name)) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing required fields']);
    exit;
}

if ($type === 'operation') {
    $stmt = $conn->prepare("INSERT INTO ptw_operation_types (operation_name, risk_assessment) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $risk);
} else if ($type === 'measure') {
    $stmt = $conn->prepare("INSERT INTO ptw_safety_measures (measure_name) VALUES (?)");
    $stmt->bind_param("s", $name);
} else {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid type']);
    exit;
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
