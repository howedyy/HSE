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
        <td>صور الملاحظة</td>
        <td>صور الإغلاق</td>
        <td>أُنشئ بواسطة</td>
        <td>أُغلق بواسطة</td>
      </tr>';

// Build filter conditions
$filterConditions = [];
if (isset($_GET['project']) && $_GET['project'] !== '') {
    $filterConditions[] = "dr.project = " . intval($_GET['project']);
}
if (isset($_GET['department']) && $_GET['department'] !== '') {
    $filterConditions[] = "dr.department = " . intval($_GET['department']);
}
if (isset($_GET['created_by']) && $_GET['created_by'] !== '') {
    $filterConditions[] = "dr.user_id = " . intval($_GET['created_by']);
}
if (isset($_GET['risk']) && $_GET['risk'] !== '') {
    $filterConditions[] = "dr.risk = '" . $conn->real_escape_string($_GET['risk']) . "'";
}
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $filterConditions[] = "dr.report_status = " . intval($_GET['status']);
}
if (isset($_GET['startDate']) && $_GET['startDate'] !== '') {
    $filterConditions[] = "dr.date >= '" . $conn->real_escape_string($_GET['startDate']) . " 00:00:00'";
}
if (isset($_GET['endDate']) && $_GET['endDate'] !== '') {
    $filterConditions[] = "dr.date <= '" . $conn->real_escape_string($_GET['endDate']) . " 23:59:59'";
}

$whereClause = count($filterConditions) > 0 ? 'WHERE ' . implode(' AND ', $filterConditions) : '';

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
        LEFT JOIN users AS u2 ON dr.closed_by = u2.id
        $whereClause
        ORDER BY dr.date DESC";
        
$result = $conn->query($sql);

if ($result === false) {
    die("SQL Error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $status = ($row['report_status'] == 1) ? "مغلق" : "مفتوح";
    
    // Base URL for absolute links
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $baseUrl = $protocol . "://" . $host . $dir . '/';

    // Format Observation Images Links
    $obsLinks = [];
    if (!empty($row['image_upload'])) {
        $images = json_decode($row['image_upload'], true);
        if (is_array($images)) {
            foreach ($images as $index => $img) {
                $url = $baseUrl . "assests/uploads/" . ltrim($img, '/');
                $obsLinks[] = '<a href="' . $url . '">صورة ' . ($index + 1) . '</a>';
            }
        } else {
            $url = $baseUrl . "assests/uploads/" . ltrim($row['image_upload'], '/');
            $obsLinks[] = '<a href="' . $url . '">صورة</a>';
        }
    }
    $obsLinksHtml = implode(", ", $obsLinks);

    // Format Closure Images Links
    $clsLinks = [];
    if (!empty($row['closure_image'])) {
        $images = json_decode($row['closure_image'], true);
        if (is_array($images)) {
            foreach ($images as $index => $img) {
                $url = $baseUrl . "assests/uploads/closures/" . ltrim($img, '/');
                $clsLinks[] = '<a href="' . $url . '">غلق ' . ($index + 1) . '</a>';
            }
        } else {
            $url = $baseUrl . "assests/uploads/closures/" . ltrim($row['closure_image'], '/');
            $clsLinks[] = '<a href="' . $url . '">صورة الغلق</a>';
        }
    }
    $clsLinksHtml = implode(", ", $clsLinks);

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
    echo '<td>' . $obsLinksHtml . '</td>';
    echo '<td>' . $clsLinksHtml . '</td>';
    echo '<td>' . htmlspecialchars($row['username'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['closed_by_username'] ?? '—') . '</td>';
    echo '</tr>';
}

echo '</table></body></html>';
exit;
?>