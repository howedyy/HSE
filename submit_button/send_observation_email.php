<?php
// CORS Headers — Dynamic localhost port matching for development
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (preg_match('/^http:\/\/localhost:\d+$/', $origin)) {
    header("Access-Control-Allow-Origin: " . $origin);
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle Preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Send observation email notification
require_once(__DIR__ . '/../constants/auth_check.php');
if (!hasAccess('dailyreport_overview.php', 'send_email')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access']);
    exit;
}
require_once(__DIR__ . '/../constants/dbconnect.php');
require_once(__DIR__ . '/../sendemail/vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$report_id = $_POST['report_id'] ?? null;

if (!$report_id) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit;
}

// Fetch report details with department and project emails
$query = "
    SELECT 
        dr.id,
        dr.date,
        dr.risk,
        dr.observation_description,
        dr.description,
        dr.closed_at,
        pr.project_name,
        pr.email as project_email,
        d.department_name,
        d.email as department_email,
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

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
    exit;
}

$report = $result->fetch_assoc();
$stmt->close();

// Prepare email content
$risk_badge = '';
$risk_color = '';
switch ($report['risk']) {
    case 'عالية':
        $risk_badge = '🔴 High Risk';
        $risk_color = '#d9534f';
        break;
    case 'متوسطه':
        $risk_badge = '🟡 Medium Risk';
        $risk_color = '#f0ad4e';
        break;
    case 'منخفضة':
        $risk_badge = '🟢 Low Risk';
        $risk_color = '#5cb85c';
        break;
    default:
        $risk_badge = 'Unknown Risk';
        $risk_color = '#777';
}

$status = $report['closed_at'] ? 'Closed' : 'Open';

// Get server base URL
// Get server base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

// Try to get the actual IP address instead of localhost
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // Try to get the local network IP
    $local_ip = '';
    if (function_exists('gethostbyname')) {
        $local_ip = gethostbyname(gethostname());
    }
    // If we can't get IP, use a default that might work better
    $host = $local_ip ?: '192.168.1.100'; // Replace with your actual local IP
} else {
    $host = $_SERVER['HTTP_HOST'];
}

$base_url = $protocol . $host;
$link_url = $base_url . "/Edara-HSE111/dailyreport_overview.php?highlight=" . urlencode($report_id);

$emailSubject = "HSE Observation Report - " . $report['project_name'] . " (ID: #" . $report_id . ")";
$emailBody = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
    <div style='background-color: #2196F3; color: white; padding: 15px; border-radius: 5px 5px 0 0; text-align: center;'>
        <h2 style='margin: 0;'>New HSE Observation Report</h2>
    </div>
    
    <div style='background-color: white; padding: 20px; border-radius: 0 0 5px 5px;'>
        <h3 style='color: #333; border-bottom: 2px solid #2196F3; padding-bottom: 10px;'>Report Details</h3>
        
        <table style='width: 100%; border-collapse: collapse;'>
            <tr>
                <td style='padding: 8px; font-weight: bold; color: #555;'>Report ID:</td>
                <td style='padding: 8px;'>#" . htmlspecialchars($report_id) . "</td>
            </tr>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 8px; font-weight: bold; color: #555;'>🏢 Department:</td>
                <td style='padding: 8px;'>" . htmlspecialchars($report['department_name']) . "</td>
            </tr>
            <tr>
                <td style='padding: 8px; font-weight: bold; color: #555;'>🏗️ Project:</td>
                <td style='padding: 8px;'>" . htmlspecialchars($report['project_name']) . "</td>
            </tr>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 8px; font-weight: bold; color: #555;'>⚠️ Risk Level:</td>
                <td style='padding: 8px;'>
                    <span style='background-color: $risk_color; color: white; padding: 4px 10px; border-radius: 3px; font-weight: bold;'>
                        $risk_badge
                    </span>
                </td>
            </tr>
            <tr>
                <td style='padding: 8px; font-weight: bold; color: #555;'>📝 Observation:</td>
                <td style='padding: 8px;'>" . htmlspecialchars($report['observation_description']) . "</td>
            </tr>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 8px; font-weight: bold; color: #555;'>📅 Submitted:</td>
                <td style='padding: 8px;'>" . htmlspecialchars($report['date']) . "</td>
            </tr>
            <tr>
                <td style='padding: 8px; font-weight: bold; color: #555;'>👤 Created By:</td>
                <td style='padding: 8px;'>" . htmlspecialchars($report['created_by']) . "</td>
            </tr>
            <tr style='background-color: #f5f5f5;'>
                <td style='padding: 8px; font-weight: bold; color: #555;'>Status:</td>
                <td style='padding: 8px;'>$status</td>
            </tr>
        </table>
        
        <div style='margin-top: 20px; padding: 15px; background-color: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;'>
            <p style='color: #333; margin: 0;'><strong>📋 Description:</strong></p>
            <p style='color: #666; margin: 10px 0 0 0; line-height: 1.6;'>" . nl2br(htmlspecialchars($report['description'])) . "</p>
        </div>
        
        <div style='text-align: center; margin-top: 25px;'>
            <a href='$link_url' style='display: inline-block; padding: 12px 30px; background-color: #0275d8; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                📊 View Full Report
            </a>
        </div>
    </div>
</div>
";

// Send Email using PHPMailer
$mail = new PHPMailer(true);
$emailsSent = [];
$emailErrors = [];

try {
    // Gmail SMTP Configuration
    $mail->isSMTP();
    $mail->SMTPDebug = 0;  // Disable debug to prevent breaking JSON response
    $mail->Host       = 'mail.edaraproperty.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply@edaraproperty.net';
    $mail->Password   = 'Bmyv@%$Nz5QMB7K';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->Timeout    = 30;
    
    // Add SSL options for compatibility
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->setFrom('noreply@edaraproperty.net', 'HSE Report System');
    
    // Add recipients
    $recipientsAdded = false;
    
    // Project email (To)
    if (!empty($report['project_email'])) {
        $mail->addAddress($report['project_email']);
        $recipientsAdded = true;
        $emailsSent[] = "To: " . $report['project_email'];
    }

    // Department email (CC)
    if (!empty($report['department_email'])) {
        $mail->addCC($report['department_email']);
        $recipientsAdded = true;
        $emailsSent[] = "CC: " . $report['department_email'];
    }

    // Fixed CC recipients
    $fixedCCs = [
        'Ahmed.ali@edaraproperty.net',
        'hse.manager@edaraproperty.net',
        'hse@edaraproperty.net',
        'hse.east@edaraproperty.net'
    ];

    foreach ($fixedCCs as $ccEmail) {
        $ccEmail = trim($ccEmail);
        $mail->addCC($ccEmail);
        $recipientsAdded = true;
        $emailsSent[] = "CC: " . $ccEmail;
    }

    if (!$recipientsAdded) {
        echo json_encode([
            'success' => false, 
            'message' => 'No email addresses found for this project or department'
        ]);
        exit;
    }

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = $emailSubject;
    $mail->Body    = $emailBody;
    $mail->AltBody = strip_tags($emailBody);

    $mail->send();
    
    // Update database to mark email as sent
    $updateQuery = "UPDATE daily_report SET email_sent = 1, email_sent_at = NOW() WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $report_id);
    $updateStmt->execute();
    $updateStmt->close();
    
    error_log("Observation email sent successfully for report #$report_id to: " . implode(', ', $emailsSent));
    
    echo json_encode([
        'success' => true, 
        'message' => 'Email sent successfully to ' . count($emailsSent) . ' recipient(s)',
        'recipients' => $emailsSent
    ]);

} catch (Exception $e) {
    error_log("Failed to send observation email for report #$report_id: " . $mail->ErrorInfo);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send email: ' . $mail->ErrorInfo
    ]);
}

$conn->close();
?>
