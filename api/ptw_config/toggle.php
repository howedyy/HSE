<?php
require_once __DIR__ . '/../header.php';

// Check for admin permission
$user_role = (int)($_SESSION['user_role'] ?? 0);
if ($user_role !== 1) {
    http_response_code(403);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? ''; // 'operation' or 'measure'
$id = (int)($input['id'] ?? 0);
$status = (int)($input['status'] ?? 0);

if (!$id || empty($type)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid ID or type']);
    exit;
}

if ($type === 'operation') {
    $stmt = $conn->prepare("UPDATE ptw_operation_types SET is_active = ? WHERE id = ?");
} else if ($type === 'measure') {
    $stmt = $conn->prepare("UPDATE ptw_safety_measures SET is_active = ? WHERE id = ?");
} else {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid type']);
    exit;
}

$stmt->bind_param("ii", $status, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
