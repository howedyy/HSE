<?php
// Send overdue email notification
require_once(__DIR__ . '/../constants/auth_check.php');
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
        TIMESTAMPDIFF(HOUR, dr.date, IFNULL(dr.closed_at, NOW())) as delay_hours,
        CASE
            WHEN dr.risk = 'عالية' THEN 8
            WHEN dr.risk = 'متوسطه' THEN 12
            WHEN dr.risk = 'منخفضة' THEN 24
            ELSE 48
        END as threshold_hours
    FROM daily_report dr
    LEFT JOIN project pr ON dr.project = pr.id
    LEFT JOIN department d ON dr.department = d.id
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

// Calculate overdue hours
$exceeded_by = max(0, $report['delay_hours'] - $report['threshold_hours']);

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

$status = $report['closed_at'] ? 'Closed (Late)' : 'Still Open';

// Get server base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $host;
$link_url = $base_url . "/Edara-HSE111/dailyreport_overview.php?highlight=" . urlencode($report_id);

$emailSubject = "⚠️ OVERDUE REPORT - " . $report['project_name'] . " (ID: #" . $report_id . ")";
$emailBody = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
    <div style='background-color: #ff5252; color: white; padding: 15px; border-radius: 5px 5px 0 0; text-align: center;'>
        <h2 style='margin: 0;'>⚠️ OVERDUE REPORT ALERT ⚠️</h2>
    </div>
    
    <div style='background-color: white; padding: 20px; border-radius: 0 0 5px 5px;'>
        <h3 style='color: #333; border-bottom: 2px solid #ff5252; padding-bottom: 10px;'>Report Details</h3>
        
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
                <td style='padding: 8px; font-weight: bold; color: #555;'>Status:</td>
                <td style='padding: 8px;'>$status</td>
            </tr>
            <tr style='background-color: #fff3cd;'>
                <td style='padding: 8px; font-weight: bold; color: #856404;'>⏱️ Overdue By:</td>
                <td style='padding: 8px; color: #856404; font-weight: bold;'>$exceeded_by hours</td>
            </tr>
        </table>
        
        <div style='margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #ff5252; border-radius: 4px;'>
            <p style='color: #333; margin: 0;'><strong>📋 Description:</strong></p>
            <p style='color: #666; margin: 10px 0 0 0; line-height: 1.6;'>" . nl2br(htmlspecialchars($report['description'])) . "</p>
        </div>
        
        <div style='text-align: center; margin-top: 25px;'>
            <a href='$link_url' style='display: inline-block; padding: 12px 30px; background-color: #0275d8; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                📊 View Full Report
            </a>
        </div>
        
        <div style='margin-top: 20px; padding: 10px; background-color: #fff3cd; border-radius: 4px; text-align: center; color: #856404;'>
            <small><strong>⚠️ This report has exceeded its resolution timeline. Please take immediate action.</strong></small>
        </div>
    </div>
</div>
";

// Send Email using PHPMailer
$mail = new PHPMailer(true);
$emailsSent = [];
$emailErrors = [];

try {
    // Gmail SMTP Configuration (matching working send_email.php)
    $mail->isSMTP();
    $mail->SMTPDebug = 0;  // Disable debug to prevent breaking JSON response
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'mohamedhowedy766@gmail.com';
    $mail->Password   = 'zlvtfddathvpquwd';
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

    $mail->setFrom('noreply@edaraproperty.net', 'HSE Overdue Alert');
    
    // Add recipients
    $recipientsAdded = false;
    
    if (!empty($report['department_email'])) {
        $mail->addAddress($report['department_email']);
        $recipientsAdded = true;
        $emailsSent[] = $report['department_email'];
    }
    
    if (!empty($report['project_email'])) {
        $mail->addAddress($report['project_email']);
        $recipientsAdded = true;
        $emailsSent[] = $report['project_email'];
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
    
    error_log("Overdue email sent successfully for report #$report_id to: " . implode(', ', $emailsSent));
    
    echo json_encode([
        'success' => true, 
        'message' => 'Email sent successfully to ' . count($emailsSent) . ' recipient(s)',
        'recipients' => $emailsSent
    ]);

} catch (Exception $e) {
    error_log("Failed to send overdue email for report #$report_id: " . $mail->ErrorInfo);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send email: ' . $mail->ErrorInfo
    ]);
}

$conn->close();
?>
