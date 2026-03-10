<?php
require_once __DIR__ . '/../header.php';

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(["message" => "Not authenticated."]); exit(); }

$pCheck = $conn->prepare("SELECT 1 FROM role_type_permissions WHERE role_type = ? AND page = 'ptw_overview.php' AND (action = 'finish' OR action = '*') LIMIT 1");
$pCheck->bind_param("i", $_SESSION['user_type']); $pCheck->execute(); $pCheck->store_result();
if ($pCheck->num_rows === 0) { http_response_code(403); echo json_encode(["message" => "Permission denied."]); $pCheck->close(); exit(); }
$pCheck->close();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["message" => "POST required."]); exit(); }

$input = json_decode(file_get_contents("php://input"), true) ?? [];
$permit_number       = trim($input['permit_number']       ?? '');
$cancellation_reason = trim($input['cancellation_reason'] ?? '');
$completion_date     = trim($input['completion_date']     ?? '');
$admin_signature_3   = trim($input['admin_signature_3']   ?? '');
$safety_signature_3  = trim($input['safety_signature_3']  ?? '');

if (!$permit_number) { http_response_code(400); echo json_encode(["message" => "permit_number required."]); exit(); }

// 3 = Not Completed (has cancellation reason), 2 = Finished
$work_status = ($cancellation_reason !== '') ? 3 : 2;

$stmt = $conn->prepare("UPDATE PTW SET ptw_status=?, work_status=?, cancellation_reason=?, completion_date=?, admin_signature_3=?, safety_signature_3=? WHERE permit_number=? AND ptw_status=1 LIMIT 1");
$stmt->bind_param("iisssss", $work_status, $work_status, $cancellation_reason, $completion_date, $admin_signature_3, $safety_signature_3, $permit_number);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $username = $_SESSION['username'] ?? 'Unknown';
    $action   = ($work_status === 2) ? 'Finished' : 'Not Completed';
    $notes    = ($work_status === 3) ? "Not completed reason: $cancellation_reason" : "Completed on: $completion_date";
    $log = $conn->prepare("INSERT INTO ptw_history (permit_number, action, action_by, action_date, notes) VALUES (?, ?, ?, NOW(), ?)");
    $log->bind_param("ssss", $permit_number, $action, $username, $notes);
    $log->execute(); $log->close();
    echo json_encode(["success" => true, "message" => "Permit closed."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Could not close permit (not in Approved state or not found)."]);
}
$stmt->close(); $conn->close();
?>
