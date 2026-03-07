<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once __DIR__ . '/../header.php';

// RBAC Check
if (!isset($_SESSION['user_id'])) {
    error_log("Email Dispatch: Not authenticated session.");
    http_response_code(401);
    echo json_encode(["message" => "Not authenticated."]);
    exit();
}
error_log("Email Dispatch: Authed as user " . ($_SESSION['user_id'] ?? 'N/A') . " type " . ($_SESSION['user_type'] ?? 'N/A'));

// Cast to int for safety in bind_param
$user_type = (int)($_SESSION['user_type'] ?? 0);
$user_id = (int)($_SESSION['user_id'] ?? 0);

// Check permission: dailyreport_overview.php / send_email
$pCheck = $conn->prepare("SELECT 1 FROM role_type_permissions WHERE role_type = ? AND page = 'dailyreport_overview.php' AND (action = 'send_email' OR action = '*') LIMIT 1");
$pCheck->bind_param("i", $user_type);
$pCheck->execute();
$pCheck->store_result();
if ($pCheck->num_rows === 0) {
    error_log("Email Dispatch: No role permissions, checking user specific.");
    // Fallback to user-specific
    $upCheck = $conn->prepare("SELECT 1 FROM role_permissions WHERE user_id = ? AND page = 'dailyreport_overview.php' AND (action = 'send_email' OR action = '*') LIMIT 1");
    $upCheck->bind_param("i", $user_id);
    $upCheck->execute();
    $upCheck->store_result();
    if ($upCheck->num_rows === 0) {
        http_response_code(403);
        echo json_encode(["message" => "You do not have permission to send emails."]);
        $upCheck->close();
        $pCheck->close();
        exit();
    }
    $upCheck->close();
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

$report_id = $input['report_id'] ?? null;
if (!$report_id) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit;
}

require_once __DIR__ . '/../../sendemail/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fetch report details
$query = "
    SELECT 
        dr.id, dr.date, dr.risk, dr.observation_description, dr.description, dr.closed_at,
        pr.project_name, pr.email as project_email,
        d.department_name, d.email as department_email,
        u.username as created_by
    FROM daily_report dr
    LEFT JOIN project pr ON dr.project = pr.id
    LEFT JOIN department d ON dr.department = d.id
    LEFT JOIN users u ON dr.user_id = u.id
    WHERE dr.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();
error_log("Email Dispatch: Query executed. Num rows: " . $result->num_rows);

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
    exit;
}

$report = $result->fetch_assoc();
error_log("Email Dispatch: Found report for project: " . $report['project_name']);
$stmt->close();

// Prepare email content
$risk_badge = '';
$risk_color = '';
switch ($report['risk']) {
    case 'عالية': $risk_badge = '🔴 High Risk'; $risk_color = '#d9534f'; break;
    case 'متوسطه': $risk_badge = '🟡 Medium Risk'; $risk_color = '#f0ad4e'; break;
    case 'منخفضة': $risk_badge = '🟢 Low Risk'; $risk_color = '#5cb85c'; break;
    default: $risk_badge = 'Unknown Risk'; $risk_color = '#777';
}

$status = $report['closed_at'] ? 'Closed' : 'Open';

$emailSubject = "HSE Observation Report - " . $report['project_name'] . " (ID: #" . $report_id . ")";
$emailBody = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
    <div style='background-color: #2196F3; color: white; padding: 15px; border-radius: 5px 5px 0 0; text-align: center;'>
        <h2 style='margin: 0;'>New HSE Observation Report</h2>
    </div>
    <div style='background-color: white; padding: 20px; border-radius: 0 0 5px 5px;'>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr><td style='padding: 8px;'><b>ID:</b></td><td>#" . htmlspecialchars($report_id) . "</td></tr>
            <tr><td style='padding: 8px;'><b>Project:</b></td><td>" . htmlspecialchars($report['project_name']) . "</td></tr>
            <tr><td style='padding: 8px;'><b>Department:</b></td><td>" . htmlspecialchars($report['department_name']) . "</td></tr>
            <tr><td style='padding: 8px;'><b>Risk:</b></td><td><span style='color:$risk_color'>$risk_badge</span></td></tr>
            <tr><td style='padding: 8px;'><b>Observation:</b></td><td>" . htmlspecialchars($report['observation_description']) . "</td></tr>
        </table>
        <div style='margin-top: 20px; padding: 15px; border-left: 4px solid #2196F3; background:#e3f2fd;'>
            <p><b>Description:</b></p>
            <p>" . nl2br(htmlspecialchars($report['description'])) . "</p>
        </div>
    </div>
</div>";

$mail = new PHPMailer(true);
error_log("Email Dispatch: Initializing PHPMailer");
try {
    $mail->isSMTP();
    $mail->Host       = 'mail.edaraproperty.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply@edaraproperty.net';
    $mail->Password   = 'Bmyv@%$Nz5QMB7K';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    
    // Add SSL options for compatibility (especially on local environments)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    $mail->setFrom('noreply@edaraproperty.net', 'HSE Report System');
    
    $recipientsAdded = false;
    if (!empty($report['project_email'])) {
        $mail->addAddress($report['project_email']);
        $recipientsAdded = true;
    }
    if (!empty($report['department_email'])) {
        $mail->addCC($report['department_email']);
        $recipientsAdded = true;
    }
    
    // Hardcoded CCs
    $mail->addCC('Ahmed.ali@edaraproperty.net');
    $mail->addCC('hse.manager@edaraproperty.net');
    $recipientsAdded = true; // Since we have hardcoded ones
    
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = $emailSubject;
    $mail->Body    = $emailBody;

    error_log("Email Dispatch: Attempting to send...");
    $mail->send();
    error_log("Email Dispatch: Sent successfully.");
    
    $updateStmt = $conn->prepare("UPDATE daily_report SET email_sent = 1, email_sent_at = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $report_id);
    $updateStmt->execute();
    $updateStmt->close();

    echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
} catch (\Exception $e) {
    error_log("Email Dispatch Fatal Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
}

$conn->close();
