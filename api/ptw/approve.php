<?php
require_once __DIR__ . '/../header.php';

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(["message" => "Not authenticated."]); exit(); }

// Requires 'edit' on ptw_overview.php
$pCheck = $conn->prepare("SELECT 1 FROM role_type_permissions WHERE role_type = ? AND page = 'ptw_overview.php' AND (action = 'edit' OR action = '*') LIMIT 1");
$pCheck->bind_param("i", $_SESSION['user_type']); $pCheck->execute(); $pCheck->store_result();
if ($pCheck->num_rows === 0) { http_response_code(403); echo json_encode(["message" => "Permission denied."]); $pCheck->close(); exit(); }
$pCheck->close();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["message" => "POST required."]); exit(); }

$input = json_decode(file_get_contents("php://input"), true) ?? [];
$permit_number    = trim($input['permit_number']    ?? '');
$safety_manager   = trim($input['safety_manager']   ?? '');
$safety_signature = trim($input['safety_signature'] ?? '');

if (!$permit_number) { http_response_code(400); echo json_encode(["message" => "permit_number required."]); exit(); }

// Only approve if currently Pending (status=0)
$stmt = $conn->prepare("UPDATE PTW SET safety_manager=?, safety_signature=?, ptw_status=1 WHERE permit_number=? AND ptw_status=0 LIMIT 1");
$stmt->bind_param("sss", $safety_manager, $safety_signature, $permit_number);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $username = $_SESSION['username'] ?? 'Unknown';
    $notes = "Approved by: $safety_manager";
    $log = $conn->prepare("INSERT INTO ptw_history (permit_number, action, action_by, action_date, notes) VALUES (?, 'Approved', ?, NOW(), ?)");
    $log->bind_param("sss", $permit_number, $username, $notes);
    $log->execute(); $log->close();
    echo json_encode(["success" => true, "message" => "Permit approved."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Could not approve permit (already approved or not found)."]);
}
$stmt->close(); $conn->close();
?>
