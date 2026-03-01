<?php
require_once __DIR__ . '/../header.php';

// RBAC Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Not authenticated."]);
    exit();
}

// Check permission: dailyreport_overview.php / delete
$pCheck = $conn->prepare("SELECT 1 FROM role_type_permissions WHERE role_type = ? AND page = 'dailyreport_overview.php' AND (action = 'delete' OR action = '*') LIMIT 1");
$pCheck->bind_param("i", $_SESSION['user_type']);
$pCheck->execute();
$pCheck->store_result();
if ($pCheck->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["message" => "You do not have permission to delete daily reports."]);
    $pCheck->close();
    exit();
}
$pCheck->close();

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    // Also allow POST with _method=DELETE for easier handling in some clients
    if (!($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'DELETE')) {
        http_response_code(405);
        echo json_encode(["message" => "DELETE required."]);
        exit();
    }
}

$id = $_GET['id'] ?? $_POST['id'] ?? '';

if (!$id) {
    http_response_code(400);
    echo json_encode(["message" => "Report ID is required."]);
    exit();
}

$stmt = $conn->prepare("DELETE FROM daily_report WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Report deleted successfully."
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to delete report: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
