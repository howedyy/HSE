<?php 
require_once "constants/auth_check.php";

if (!hasAccess('ptw_overview.php', 'view')) {
    die("Unauthorized Access");
}

if (!isset($_GET['permit_number']) || empty($_GET['permit_number'])) {
    die("Error: No permit number provided.");
}

$permit_number = $conn->real_escape_string($_GET['permit_number']);

$sql = "SELECT p.*, d.department_name 
        FROM ptw p
        LEFT JOIN department d ON p.department = d.id
        WHERE p.permit_number = '$permit_number'";

$result = $conn->query($sql);

if (!$result) {
    die("Database query error: " . $conn->error);
}

if ($result->num_rows === 0) {
    die("Error: Permit not found with number: " . $permit_number);
}

$ptw_data = $result->fetch_assoc();

$status = (int)$ptw_data['ptw_status'];
switch ($status) {
    case 0:
        $statusText = "Not Approved";
        break;
    case 1:
        $statusText = "Approved";
        break;
    case 2:
        $statusText = "Finished";
        break;
    case 3:
        $statusText = "Not Completed";
        break;
    case 4:
        $statusText = "Non Compliance";
        break;
    default:
        $statusText = "Unknown";
}

$workStatus = (int)($ptw_data['work_status'] ?? 0);
switch ($workStatus) {
    case 0:
        $workStatusText = "Not Started";
        break;
    case 1:
        $workStatusText = "In Progress";
        break;
    case 2:
        $workStatusText = "Completed";
        break;
    case 3:
        $workStatusText = "Cancelled";
        break;
    default:
        $workStatusText = "Unknown";
}

echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Permit to Work: ' . $permit_number . '</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
            margin: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 { 
            text-align: center; 
            color: #2F3C7E; 
            padding-bottom: 10px;
            border-bottom: 2px solid #2F3C7E;
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #e6eeff;
            padding: 8px 15px;
            margin-top: 25px;
            margin-bottom: 15px;
            border-left: 5px solid #2F3C7E;
            font-weight: bold;
            font-size: 16px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        th { 
            background-color: #f2f2f2; 
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }
        td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            color: white;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        .status-approved { background-color: #4CAF50; }
        .status-finished { background-color: #2196F3; }
        .status-not-approved { background-color: #FFC107; }
        .status-non-compliance { background-color: #F44336; }
        .status-not-completed { background-color: #9E9E9E; }
        .print-button { 
            display: block; 
            margin: 20px auto; 
            padding: 10px 20px; 
            background-color: #4CAF50; 
            color: white; 
            border: none;
            border-radius: 4px;
            cursor: pointer; 
            font-size: 16px; 
        }
        .print-button:hover {
            background-color: #388E3C;
        }
        @media print {
            .print-button { display: none; }
            body { background-color: white; }
            .container { box-shadow: none; }
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Permit to Work (PTW) Details</h1>
        
        <div class="section-title">Basic Information</div>
        <table>
            <tr>
                <td style="width: 25%; font-weight: bold;">Permit Number:</td>
                <td style="width: 25%;">' . htmlspecialchars($permit_number) . '</td>
                <td style="width: 25%; font-weight: bold;">Status:</td>
                <td style="width: 25%;">
                    <span class="status-badge status-' . strtolower(str_replace(' ', '-', $statusText)) . '">
                        ' . htmlspecialchars($statusText) . '
                    </span>
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Editor Name:</td>
                <td>' . htmlspecialchars($ptw_data['editor_name'] ?? '—') . '</td>
                <td style="font-weight: bold;">Job Title:</td>
                <td>' . htmlspecialchars($ptw_data['job_title'] ?? '—') . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Department:</td>
                <td>' . htmlspecialchars($ptw_data['department_name'] ?? '—') . '</td>
                <td style="font-weight: bold;">Permit Date:</td>
                <td>' . htmlspecialchars($ptw_data['permit_date'] ?? '—') . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Company Name:</td>
                <td>' . htmlspecialchars($ptw_data['company_name'] ?? '—') . '</td>
                <td style="font-weight: bold;">Subcontractor:</td>
                <td>' . htmlspecialchars($ptw_data['subcontractor'] ?? '—') . '</td>
            </tr>
        </table>

        <div class="section-title">Project & Work Details</div>
        <table>
            <tr>
                <td style="width: 25%; font-weight: bold;">Project Name:</td>
                <td colspan="3">' . htmlspecialchars($ptw_data['project_name'] ?? '—') . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Work Location:</td>
                <td colspan="3">' . htmlspecialchars($ptw_data['work_location'] ?? '—') . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Work Description:</td>
                <td colspan="3">' . nl2br(htmlspecialchars($ptw_data['work_description'] ?? '—')) . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Operation Type:</td>
                <td colspan="3">' . htmlspecialchars($ptw_data['operation_type'] ?? '—') . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tools & Equipment:</td>
                <td colspan="3">' . htmlspecialchars($ptw_data['tools_equipment'] ?? '—') . '</td>
            </tr>
        </table>

        <div class="section-title">Schedule Information</div>
        <table>
            <tr>
                <td style="width: 25%; font-weight: bold;">Start Time:</td>
                <td style="width: 25%;">' . htmlspecialchars($ptw_data['start_time'] ?? '—') . '</td>
                <td style="width: 25%; font-weight: bold;">End Time:</td>
                <td style="width: 25%;">' . htmlspecialchars($ptw_data['end_time'] ?? '—') . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Work Status:</td>
                <td>' . htmlspecialchars($workStatusText) . '</td>
                <td style="font-weight: bold;">Completion Date:</td>
                <td>' . htmlspecialchars($ptw_data['completion_date'] ?? '—') . '</td>
            </tr>
        </table>

        <div class="section-title">Risk Assessment & Safety</div>
        <table>
            <tr>
                <td style="width: 25%; font-weight: bold;">Risk Assessment:</td>
                <td colspan="3">' . nl2br(htmlspecialchars($ptw_data['risk_assessment'] ?? '—')) . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Safety Measures:</td>
                <td colspan="3">' . nl2br(htmlspecialchars($ptw_data['safety_measures'] ?? '—')) . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Safety Manager:</td>
                <td>' . htmlspecialchars($ptw_data['safety_manager'] ?? '—') . '</td>
                <td style="font-weight: bold;">Safety Signature:</td>
                <td>' . (empty($ptw_data['safety_signature']) ? '—' : '<span style="color:green">Approved</span>') . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Safety Signature 3:</td>
                <td colspan="3">' . (empty($ptw_data['safety_signature_3']) ? '—' : '<span style="color:green">Approved</span>') . '</td>
            </tr>
        </table>

        <div class="section-title">Signatures & Approvals</div>
        <table>
            <tr>
                <td style="width: 25%; font-weight: bold;">Execution Manager Signature:</td>
                <td style="width: 25%;">' . (empty($ptw_data['execution_manager_signature']) ? '—' : '<span style="color:green">Approved</span>') . '</td>
                <td style="width: 25%; font-weight: bold;">Admin Signature:</td>
                <td style="width: 25%;">' . (empty($ptw_data['admin_signature']) ? '—' : '<span style="color:green">Approved</span>') . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Admin Signature 3:</td>
                <td colspan="3">' . (empty($ptw_data['admin_signature_3']) ? '—' : '<span style="color:green">Approved</span>') . '</td>
            </tr>
        </table>';

if ($status == 4 && !empty($ptw_data['noncompliance_reason'])) {
    echo '<div class="section-title" style="background-color:#ffebee; border-left-color:#F44336;">Non-Compliance Information</div>
    <table>
        <tr>
            <td style="width: 25%; font-weight: bold;">Non-Compliance Reason:</td>
            <td colspan="3">' . nl2br(htmlspecialchars($ptw_data['noncompliance_reason'])) . '</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Non-Compliance Date:</td>
            <td>' . htmlspecialchars($ptw_data['noncompliance_date'] ?? '—') . '</td>
            <td style="font-weight: bold;">Non-Compliance Time:</td>
            <td>' . htmlspecialchars($ptw_data['noncompliance_time'] ?? '—') . '</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Non-Compliance Officer:</td>
            <td>' . htmlspecialchars($ptw_data['noncompliance_officer'] ?? '—') . '</td>
            <td style="font-weight: bold;">Corrective Actions:</td>
            <td>' . nl2br(htmlspecialchars($ptw_data['corrective_actions'] ?? '—')) . '</td>
        </tr>
    </table>';
}

if ($workStatus == 3 && !empty($ptw_data['cancellation_reason'])) {
    echo '<div class="section-title" style="background-color:#ffebee; border-left-color:#F44336;">Cancellation Information</div>
    <table>
        <tr>
            <td style="width: 25%; font-weight: bold;">Cancellation Reason:</td>
            <td colspan="3">' . nl2br(htmlspecialchars($ptw_data['cancellation_reason'])) . '</td>
        </tr>
    </table>';
}

echo '<div class="footer">
        Generated on: ' . date('Y-m-d H:i:s') . ' | Permit Number: ' . htmlspecialchars($permit_number) . '<br>
        This is an official document from the PTW Management System
    </div>

    <button onclick="window.print();" class="print-button">Print Document</button>
    <script>
        // Auto print when page loads
        window.onload = function() {
            // Automatically print after a short delay
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</div>
</body>
</html>';
?>