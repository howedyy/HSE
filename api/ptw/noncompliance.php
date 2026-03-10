<?php
require_once __DIR__ . '/../header.php';

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(["message" => "Not authenticated."]); exit(); }

$pCheck = $conn->prepare("SELECT 1 FROM role_type_permissions WHERE role_type = ? AND page = 'ptw_overview.php' AND (action = 'edit' OR action = '*') LIMIT 1");
$pCheck->bind_param("i", $_SESSION['user_type']); $pCheck->execute(); $pCheck->store_result();
if ($pCheck->num_rows === 0) { http_response_code(403); echo json_encode(["message" => "Permission denied."]); $pCheck->close(); exit(); }
$pCheck->close();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["message" => "POST required."]); exit(); }

$input = json_decode(file_get_contents("php://input"), true) ?? [];
$permit_number = trim($input['permit_number'] ?? '');

if (!$permit_number) { http_response_code(400); echo json_encode(["message" => "permit_number required."]); exit(); }

// Only mark non-compliance if currently Approved (status=1)
$stmt = $conn->prepare("UPDATE PTW SET ptw_status=4 WHERE permit_number=? AND ptw_status=1 LIMIT 1");
$stmt->bind_param("s", $permit_number);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $username = $_SESSION['username'] ?? 'Unknown';
    $notes = "Marked Non-Compliance by: $username";
    $log = $conn->prepare("INSERT INTO ptw_history (permit_number, action, action_by, action_date, notes) VALUES (?, 'Non-Compliance', ?, NOW(), ?)");
    $log->bind_param("sss", $permit_number, $username, $notes);
    $log->execute(); $log->close();
    echo json_encode(["success" => true, "message" => "Permit marked as non-compliance."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Could not update permit (must be in Approved state)."]);
}
$stmt->close(); $conn->close();
?>
