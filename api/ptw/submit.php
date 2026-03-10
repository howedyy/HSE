<?php
require_once __DIR__ . '/../header.php';

// RBAC Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Not authenticated."]);
    exit();
}

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

// Support both JSON and multipart/form-data (for file uploads)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
} else {
    $input = $_POST;
}

// --- Collect fields ---
$editor_name              = $conn->real_escape_string($input['editor_name']              ?? '');
$job_title                = $conn->real_escape_string($input['job_title']                ?? '');
$department               = $conn->real_escape_string($input['department']               ?? '');
$project_name             = $conn->real_escape_string($input['project_name']             ?? '');
$work_location            = $conn->real_escape_string($input['work_location']            ?? '');
$permit_date              = $conn->real_escape_string($input['permit_date']              ?? '');
$start_time               = $conn->real_escape_string($input['start_time']              ?? '');
$end_time                 = $conn->real_escape_string($input['end_time']                ?? '');
$work_description         = $conn->real_escape_string($input['work_description']        ?? '');
$tools_equipment          = $conn->real_escape_string($input['tools_equipment']         ?? '');
$company_name             = $conn->real_escape_string($input['company_name']            ?? '');
$execution_manager        = $conn->real_escape_string($input['execution_manager']       ?? '');
$admin_signature          = $conn->real_escape_string($input['admin_signature']         ?? '');
$operation_type           = $conn->real_escape_string($input['operation_type']          ?? '');
$risk_assessment          = $conn->real_escape_string($input['risk_assessment']         ?? '');

// Safety measures: may be a JSON array (from React) or comma-separated string
$safety_raw = $input['safety_measures'] ?? '';
if (is_array($safety_raw)) {
    $safety_measures = $conn->real_escape_string(implode(',', $safety_raw));
} elseif (is_string($safety_raw) && strpos($safety_raw, '[') === 0) {
    // JSON-encoded array sent as string
    $decoded = json_decode($safety_raw, true);
    $safety_measures = $conn->real_escape_string(is_array($decoded) ? implode(',', $decoded) : $safety_raw);
} else {
    $safety_measures = $conn->real_escape_string($safety_raw);
}

// --- Permit number is auto-generated from the table's auto-increment ID ---
// We insert first, then build PTW{id} from the inserted row's id.

// --- Insert record (without permit_number — generated after insert) ---
$stmt = $conn->prepare(
    "INSERT INTO PTW (permit_date, editor_name, job_title, department, project_name,
     work_location, start_time, end_time, work_description, tools_equipment, operation_type,
     risk_assessment, safety_measures, company_name, execution_manager_signature, admin_signature, ptw_status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)"
);
$stmt->bind_param(
    "ssssssssssssssss",
    $permit_date, $editor_name, $job_title, $department, $project_name,
    $work_location, $start_time, $end_time, $work_description, $tools_equipment, $operation_type,
    $risk_assessment, $safety_measures, $company_name, $execution_manager, $admin_signature
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to submit: " . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit();
}
$insertId    = $stmt->insert_id;
$stmt->close();

// --- Build permit number from the auto-increment id and update the row ---
$permit_number = 'PTW' . str_pad($insertId, 3, '0', STR_PAD_LEFT);
$upd = $conn->prepare("UPDATE PTW SET permit_number = ? WHERE id = ?");
$upd->bind_param("si", $permit_number, $insertId);
$upd->execute();
$upd->close();

// --- Handle optional image upload ---
if (isset($_FILES['attachment_image']) && $_FILES['attachment_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../../assests/uploads/ptw_closure/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $ext         = pathinfo($_FILES['attachment_image']['name'], PATHINFO_EXTENSION);
    $newFilename = time() . '_' . uniqid() . '_attachment.' . $ext;
    $targetFile  = $uploadDir . $newFilename;
    if (move_uploaded_file($_FILES['attachment_image']['tmp_name'], $targetFile)) {
        $imgStmt = $conn->prepare("INSERT INTO ptw_images (permit_number, image_path, image_type) VALUES (?, ?, 'attachment')");
        if ($imgStmt) {
            $imgStmt->bind_param("ss", $permit_number, $newFilename);
            $imgStmt->execute();
            $imgStmt->close();
        }
    }
}

// --- Telegram notification ---
$botToken        = "7790658256:AAFeCjdZ_IllGGp1_5IE4A7P1eSoLCcWgHk";
$westRegionChatId = -1002005572564;
$eastRegionChatId = -1002285365220;

// Determine chat ID by project region
$chatId = $westRegionChatId;
$regionStmt = $conn->prepare("SELECT region FROM project WHERE project_name = ?");
if ($regionStmt) {
    $regionStmt->bind_param("s", $project_name);
    $regionStmt->execute();
    $regionResult = $regionStmt->get_result();
    if ($regionResult->num_rows > 0) {
        $region = (int)$regionResult->fetch_assoc()['region'];
        $chatId = ($region === 2) ? $eastRegionChatId : $westRegionChatId;
    }
    $regionStmt->close();
}

// Resolve department name
$department_name = $department;
$deptStmt = $conn->prepare("SELECT department_name FROM department WHERE id = ?");
if ($deptStmt) {
    $deptStmt->bind_param("s", $department);
    $deptStmt->execute();
    $deptResult = $deptStmt->get_result();
    if ($deptResult->num_rows > 0) {
        $department_name = $deptResult->fetch_assoc()['department_name'];
    }
    $deptStmt->close();
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host     = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1')
            ? (gethostbyname(gethostname()) ?: '192.168.1.100')
            : $_SERVER['HTTP_HOST'];
$link_url = $protocol . $host . "/ptw_overview.php?highlight=" . urlencode($permit_number);

$telegramMsg = "📢 New PTW Submission\n\n"
             . "🏢 Department: $department_name\n"
             . "🏗️ Project Name: $project_name\n"
             . "📍 Work Location: $work_location\n"
             . "🔢 Permit Number: $permit_number\n"
             . "⚙️ Work Description: $work_description\n"
             . "🛠️ Tools and Equipment: $tools_equipment\n"
             . "🕐 Submitted: " . date('Y-m-d H:i:s') . "\n\n"
             . "Link: $link_url";

// Send Telegram (non-blocking, ignore failure)
$telegramSent = false;
if (function_exists('curl_init')) {
    $ch = curl_init("https://api.telegram.org/bot$botToken/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_POSTFIELDS     => ['chat_id' => $chatId, 'text' => $telegramMsg],
    ]);
    $tgResponse = curl_exec($ch);
    $tgStatus   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $telegramSent = ($tgStatus >= 200 && $tgStatus < 300);
}

$conn->close();

echo json_encode([
    "success"      => true,
    "message"      => "Permit submitted successfully.",
    "id"           => $insertId,
    "permitNumber" => $permit_number,
    "telegram"     => $telegramSent,
], JSON_UNESCAPED_UNICODE);
?>
