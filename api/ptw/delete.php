<?php
require_once __DIR__ . '/../header.php';

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(["message" => "Not authenticated."]); exit(); }

$pCheck = $conn->prepare("SELECT 1 FROM role_type_permissions WHERE role_type = ? AND page = 'ptw_overview.php' AND (action = 'delete' OR action = '*') LIMIT 1");
$pCheck->bind_param("i", $_SESSION['user_type']); $pCheck->execute(); $pCheck->store_result();
if ($pCheck->num_rows === 0) { http_response_code(403); echo json_encode(["message" => "Permission denied."]); $pCheck->close(); exit(); }
$pCheck->close();

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(["message" => "DELETE/POST required."]); exit();
}

$input = json_decode(file_get_contents("php://input"), true) ?? [];
$permit_number = trim($input['permit_number'] ?? $_GET['permit_number'] ?? '');

if (!$permit_number) { http_response_code(400); echo json_encode(["message" => "permit_number required."]); exit(); }

$stmt = $conn->prepare("DELETE FROM PTW WHERE permit_number=?");
$stmt->bind_param("s", $permit_number);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Permit deleted."]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Permit not found or already deleted."]);
}
$stmt->close(); $conn->close();
?>
