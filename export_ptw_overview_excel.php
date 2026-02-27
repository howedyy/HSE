<?php 
require_once "constants/auth_check.php";

if (!hasAccess('ptw_overview.php', 'export_excel') && !hasAccess('ptw_overview.php', 'export') && !hasAccess('ptw_overview.php', 'view')) {
    die("Unauthorized Access");
}

mysqli_set_charset($conn, "utf8mb4");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=ptw_overview_all.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
      <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <style>
        td {
            direction: rtl;
            text-align: right;
            vertical-align: middle;
            mso-number-format:"\@";
        }
      </style>
      </head>
      <body>
      <table border="1">';

echo '<tr style="font-weight:bold; background-color:#f0f0f0;">
        <td>رقم التصريح (Permit Number)</td>
        <td>القسم (Department)</td>
        <td>المشروع (Project)</td>
        <td>موقع العمل (Location)</td>
        <td>تاريخ التصريح (Date)</td>
        <td>نوع العملية (Operation)</td>
        <td>احتياطات السلامة (MEASURES)</td>
        <td>مدير السلامة (Safety Manager)</td>
        <td>الحالة (Status)</td>
      </tr>';

$sql = "SELECT p.*, d.department_name
        FROM PTW p
        LEFT JOIN department d ON p.department = d.id
        ORDER BY p.permit_date DESC";
        
$result = $conn->query($sql);

if ($result === false) {
    die("SQL Error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $status = (int)$row['ptw_status'];
    $statusText = "";
    switch ($status) {
        case 0: $statusText = "Not Approved"; break;
        case 1: $statusText = "Approved"; break;
        case 2: $statusText = "Finished"; break;
        case 3: $statusText = "Not Completed"; break;
        case 4: $statusText = "Non Compliance"; break;
        default: $statusText = "Unknown";
    }

    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['permit_number'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['department_name'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['project_name'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['work_location'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['permit_date'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['operation_type'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['safety_measures'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['safety_manager'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($statusText) . '</td>';
    echo '</tr>';
}

echo '</table></body></html>';
exit;
?>
