<?php
require_once __DIR__ . '/../header.php';

// RBAC Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Not authenticated."]);
    exit();
}

// Check permission: ptw.php / submit
$pCheck = $conn->prepare("SELECT 1 FROM role_type_permissions WHERE role_type = ? AND page = 'ptw.php' AND (action = 'submit' OR action = '*') LIMIT 1");
$pCheck->bind_param("i", $_SESSION['user_type']);
$pCheck->execute();
$pCheck->store_result();
if ($pCheck->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["message" => "You do not have permission to submit PTW permits."]);
    $pCheck->close();
    exit();
}
$pCheck->close();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "POST required."]);
    exit();
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents("php://input"), true);
} else {
    $input = $_POST;
}

$editor_name = $input['editor_name'] ?? '';
$job_title = $input['job_title'] ?? '';
$department = $input['department'] ?? 0;
$project_name = $input['project_name'] ?? '';
$work_location = $input['work_location'] ?? '';
$permit_date = $input['permit_date'] ?? '';
$start_time = $input['start_time'] ?? '';
$end_time = $input['end_time'] ?? '';
$work_description = $input['work_description'] ?? '';
$tools_equipment = $input['tools_equipment'] ?? '';
$operation_type = $input['operation_type'] ?? '';
$company_name = $input['company_name'] ?? '';
$execution_manager = $input['execution_manager'] ?? '';
$admin_signature = $input['admin_signature'] ?? '';

// Auto-generate permit number
$result = $conn->query("SELECT MAX(id) as max_id FROM PTW");
$max_id = $result ? (int)$result->fetch_assoc()['max_id'] : 0;
$permit_number = 'PTW_' . sprintf('%03d', $max_id + 1);

$stmt = $conn->prepare("INSERT INTO PTW (permit_number, permit_date, editor_name, job_title, department, project_name, work_location, start_time, end_time, work_description, tools_equipment, operation_type, company_name, execution_manager_signature, admin_signature, ptw_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
$stmt->bind_param("ssssississsssss", $permit_number, $permit_date, $editor_name, $job_title, $department, $project_name, $work_location, $start_time, $end_time, $work_description, $tools_equipment, $operation_type, $company_name, $execution_manager, $admin_signature);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Permit submitted successfully.",
        "id" => $stmt->insert_id,
        "permitNumber" => $permit_number,
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to submit: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
