<?php 
require_once "constants/auth_check.php";

if (!hasAccess('dailyreport_overview.php', 'export_excel') && !hasAccess('dailyreport_overview.php', 'export') && !hasAccess('dailyreport_overview.php', 'view')) {
    die("Unauthorized Access");
}


mysqli_set_charset($conn, "utf8mb4");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=daily_report_all.xls");
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
        <td>المعرف (ID)</td>
        <td>التاريخ</td>
        <td>المشروع</td>
        <td>القسم</td>
        <td>نوع العمل</td>
        <td>المخاطر</td>
        <td>وصف الملاحظة</td>
        <td>الإجراء التصحيحي</td>
        <td>الحالة</td>
        <td>تاريخ الإغلاق</td>
        <td>ملاحظات الإغلاق</td>
        <td>أُنشئ بواسطة</td>
        <td>أُغلق بواسطة</td>
      </tr>';

$sql = "SELECT 
            dr.id, dr.date, pr.project_name, dp.department_name,
            dr.work_type, dr.risk, dr.observation_description,
            dr.operation_corrective, dr.report_status, dr.closed_at,
            dr.image_upload, dr.closure_notes, dr.closure_image,
            u.username, u2.username as closed_by_username
        FROM 
            daily_report AS dr
        LEFT JOIN project AS pr ON dr.project = pr.id
        LEFT JOIN department AS dp ON dr.department = dp.id
        LEFT JOIN users AS u ON dr.user_id = u.id
        LEFT JOIN users AS u2 ON dr.closed_by = u2.id";
        
$result = $conn->query($sql);

if ($result === false) {
    die("SQL Error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $status = ($row['report_status'] == 1) ? "مغلق" : "مفتوح";

    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['id'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['date'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['project_name'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['department_name'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['work_type'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['risk'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['observation_description'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['operation_corrective'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($status) . '</td>';
    echo '<td>' . htmlspecialchars($row['closed_at'] ?? '—') . '</td>';
    
   
    echo '<td>' . htmlspecialchars($row['closure_notes'] ?? '—') . '</td>';
    
   
    echo '<td>' . htmlspecialchars($row['username'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['closed_by_username'] ?? '—') . '</td>';
    echo '</tr>';
}

echo '</table></body></html>';
exit;
?>