<?php
require_once __DIR__ . '/../header.php';

// RBAC Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Not authenticated."]);
    exit();
}

// Check permission: dailyreport_overview.php / submit (Close is considered a submission of closure)
$pCheck = $conn->prepare("SELECT 1 FROM role_type_permissions WHERE role_type = ? AND page = 'dailyreport_overview.php' AND (action = 'submit' OR action = '*') LIMIT 1");
$pCheck->bind_param("i", $_SESSION['user_type']);
$pCheck->execute();
$pCheck->store_result();
if ($pCheck->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["message" => "You do not have permission to close observations."]);
    $pCheck->close();
    exit();
}
$pCheck->close();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "POST required."]);
    exit();
}

$id = $_POST['id'] ?? '';
$notes = $_POST['notes'] ?? '';
$closed_by = $_SESSION['user_id'];
$closed_at = date('Y-m-d H:i:s');

if (!$id) {
    http_response_code(400);
    echo json_encode(["message" => "Report ID is required."]);
    exit();
}

// Handle closure image upload
$closure_image = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../../assests/uploads/closures/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('closure_') . '.' . $ext;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
        $closure_image = $filename;
    }
}

$stmt = $conn->prepare("UPDATE daily_report SET report_status = 1, closed_at = ?, closed_by = ?, closure_notes = ?, closure_image = ? WHERE id = ?");
$stmt->bind_param("sissi", $closed_at, $closed_by, $notes, $closure_image, $id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Observation closed successfully."
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to close observation: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
