<?php
require_once __DIR__ . '/../header.php';

// RBAC Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Not authenticated."]);
    exit();
}

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

$id = $input['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(["message" => "Report ID is required."]);
    exit;
}

// Ownership Check (Admin or Creator)
$oStmt = $conn->prepare("SELECT user_id, report_status, email_sent FROM daily_report WHERE id = ?");
$oStmt->bind_param("i", $id);
$oStmt->execute();
$report_meta = $oStmt->get_result()->fetch_assoc();
$oStmt->close();

if (!$report_meta) {
    http_response_code(404);
    echo json_encode(["message" => "Report not found."]);
    exit;
}

if ($report_meta['report_status'] != 0) {
    http_response_code(403);
    echo json_encode(["message" => "Resolved reports cannot be edited."]);
    exit;
}

// Ensure cannot edit if email is sent
if ($report_meta['email_sent'] == 1) {
    http_response_code(403);
    echo json_encode(["message" => "This report cannot be edited after an email has been sent."]);
    exit;
}

if ($_SESSION['user_id'] != $report_meta['user_id'] && $_SESSION['user_type'] != 1) {
    http_response_code(403);
    echo json_encode(["message" => "You doesn't have permission to edit this report."]);
    exit;
}

// Update fields
$project = $input['project'] ?? '';
$department = $input['department'] ?? '';
$observation = $input['observation'] ?? '';
$work_type = $input['work_type'] ?? '';
$risk = $input['risk'] ?? '';
$observation_description = $input['observation_description'] ?? '';
$operation_corrective = $input['operation_corrective'] ?? '';
$description = $input['description'] ?? '';

// Handle image uploads (additions)
$new_images = [];
if (isset($_FILES['images']) && is_array($_FILES['images']['tmp_name'])) {
    $upload_dir = __DIR__ . '/../../assests/uploads/';
    foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
            $filename = uniqid('dr_') . '.' . $ext;
            move_uploaded_file($tmp, $upload_dir . $filename);
            $new_images[] = $filename;
        }
    }
}

// Merge images or handle full update
$keep_images_json = $input['existing_images'] ?? '[]';
$keep_images = json_decode($keep_images_json, true);
$all_images = array_merge($keep_images, $new_images);
$image_json = !empty($all_images) ? json_encode($all_images) : null;

$stmt = $conn->prepare("UPDATE daily_report SET project = ?, department = ?, observation = ?, work_type = ?, risk = ?, observation_description = ?, operation_corrective = ?, description = ?, image_upload = ? WHERE id = ?");
$stmt->bind_param("iisssssssi", $project, $department, $observation, $work_type, $risk, $observation_description, $operation_corrective, $description, $image_json, $id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Report updated successfully.",
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to update report: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
