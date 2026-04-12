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
        <td>وصف العمل (Work Description)</td>
        <td>احتياطات السلامة (MEASURES)</td>
        <td>مدير السلامة (Safety Manager)</td>
        <td>الحالة (Status)</td>
      </tr>';

// Build filter conditions
$filterConditions = [];

// The frontend passes filters as a query string in the 'filters' parameter
if (isset($_GET['filters'])) {
    parse_str($_GET['filters'], $searchFilters);
} else {
    $searchFilters = $_GET; // Fallback
}

if (!empty($searchFilters['search'])) {
    $s = $conn->real_escape_string($searchFilters['search']);
    $filterConditions[] = "(p.permit_number LIKE '%$s%' OR p.work_location LIKE '%$s%' OR p.work_description LIKE '%$s%')";
}
if (!empty($searchFilters['project'])) {
    $filterConditions[] = "p.project = " . intval($searchFilters['project']);
}
if (!empty($searchFilters['department'])) {
    $filterConditions[] = "p.department = " . intval($searchFilters['department']);
}
if (!empty($searchFilters['status'])) {
    $filterConditions[] = "p.ptw_status = " . intval($searchFilters['status']);
}
if (!empty($searchFilters['type'])) {
    $filterConditions[] = "p.operation_type = '" . $conn->real_escape_string($searchFilters['type']) . "'";
}
if (!empty($searchFilters['startDate'])) {
    $filterConditions[] = "p.permit_date >= '" . $conn->real_escape_string($searchFilters['startDate']) . "'";
}
if (!empty($searchFilters['endDate'])) {
    $filterConditions[] = "p.permit_date <= '" . $conn->real_escape_string($searchFilters['endDate']) . "'";
}

$whereClause = count($filterConditions) > 0 ? 'WHERE ' . implode(' AND ', $filterConditions) : '';

$sql = "SELECT p.*, d.department_name
        FROM PTW p
        LEFT JOIN department d ON p.department = d.id
        $whereClause
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
    echo '<td>' . htmlspecialchars($row['work_description'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['safety_measures'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($row['safety_manager'] ?? '—') . '</td>';
    echo '<td>' . htmlspecialchars($statusText) . '</td>';
    echo '</tr>';
}

echo '</table></body></html>';
exit;
?>
