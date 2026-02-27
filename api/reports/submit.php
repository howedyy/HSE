<?php
require_once __DIR__ . '/../header.php';

// RBAC Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Not authenticated."]);
    exit();
}

// Check permission: dailyreport.php / submit
$pCheck = $conn->prepare("SELECT 1 FROM role_type_permissions WHERE role_type = ? AND page = 'dailyreport.php' AND (action = 'submit' OR action = '*') LIMIT 1");
$pCheck->bind_param("i", $_SESSION['user_type']);
$pCheck->execute();
$pCheck->store_result();
if ($pCheck->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["message" => "You do not have permission to submit daily reports."]);
    $pCheck->close();
    exit();
}
$pCheck->close();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "POST required."]);
    exit();
}

// Accept both JSON and FormData
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents("php://input"), true);
} else {
    $input = $_POST;
}

$project = $input['project'] ?? '';
$department = $input['department'] ?? '';
$observation = $input['observation'] ?? '';
$work_type = $input['work_type'] ?? '';
$risk = $input['risk'] ?? '';
$observation_description = $input['observation_description'] ?? '';
$operation_corrective = $input['operation_corrective'] ?? '';
$description = $input['description'] ?? '';
$user_id = $_SESSION['user_id'];
$date = date('Y-m-d H:i:s');

// Handle image uploads
$uploaded_images = [];
if (isset($_FILES['images']) && is_array($_FILES['images']['tmp_name'])) {
    $upload_dir = __DIR__ . '/../../assests/uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
            $filename = uniqid('dr_') . '.' . $ext;
            move_uploaded_file($tmp, $upload_dir . $filename);
            $uploaded_images[] = $filename;
        }
    }
}

$image_json = !empty($uploaded_images) ? json_encode($uploaded_images) : null;

$stmt = $conn->prepare("INSERT INTO daily_report (date, project, department, observation, work_type, risk, observation_description, operation_corrective, description, image_upload, user_id, report_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
$stmt->bind_param("siisssssssi", $date, $project, $department, $observation, $work_type, $risk, $observation_description, $operation_corrective, $description, $image_json, $user_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Report submitted successfully.",
        "id" => $stmt->insert_id,
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to submit report: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
