<?php
// Export Overdue Reports to Excel (HTML format for better styling and Arabic support)
require_once "constants/dbconnect.php";
require_once "constants/auth_check.php"; // Ensure user is logged in

if (!hasAccess('dailyreport_analysis.php', 'export_excel') && !hasAccess('dailyreport_analysis.php', 'export') && !hasAccess('dailyreport_analysis.php', 'view')) {
    die("Unauthorized Access");
}
mysqli_set_charset($conn, "utf8mb4");

// Apply Filters
$selectedProject = $_GET['project'] ?? '';
$selectedDepartment = $_GET['department'] ?? '';

$filterConditions = [];
if ($selectedProject !== '') {
    $filterConditions[] = "project = " . intval($selectedProject);
}
if ($selectedDepartment !== '') {
    $filterConditions[] = "department = " . intval($selectedDepartment);
}
$whereClause = count($filterConditions) ? "WHERE " . implode(' AND ', $filterConditions) : '';

// Overdue Condition Logic
$overdueCondition = "
(
    (dr.closed_at IS NULL AND TIMESTAMPDIFF(HOUR, dr.date, NOW()) >
        CASE
            WHEN dr.risk = 'عالية' THEN 8
            WHEN dr.risk = 'متوسطه' THEN 12
            WHEN dr.risk = 'منخفضة' THEN 24
        END
    )
    OR
    (dr.closed_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, dr.date, dr.closed_at) >
        CASE
            WHEN dr.risk = 'عالية' THEN 8
            WHEN dr.risk = 'متوسطه' THEN 12
            WHEN dr.risk = 'منخفضة' THEN 24
        END
    )
)
";

$finalClause = $whereClause
    ? $whereClause . " AND $overdueCondition"
    : "WHERE $overdueCondition";

// Query to fetch overdue reports
$sql = "
    SELECT 
        dr.id,
        dr.date as report_date,
        pr.project_name,
        d.department_name,
        dr.risk,
        dr.observation_description,
        dr.description,
        dr.report_status,
        dr.closed_at,
        u.username as created_by
    FROM daily_report dr
    LEFT JOIN project pr ON dr.project = pr.id
    LEFT JOIN department d ON dr.department = d.id
    LEFT JOIN users u ON dr.user_id = u.id
    $finalClause
    ORDER BY dr.date DESC
";

$result = $conn->query($sql);
if (!$result) {
    die("Query Failed: " . $conn->error);
}

// Generate Filename
$filename = "overdue_reports_" . date('Y-m-d') . ".xls";

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Start HTML Output
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '<!--[if gte mso 9]>';
echo '<xml>';
echo ' <x:ExcelWorkbook>';
echo '  <x:ExcelWorksheets>';
echo '   <x:ExcelWorksheet>';
echo '    <x:Name>Overdue Reports</x:Name>';
echo '    <x:WorksheetOptions>';
echo '     <x:Print>';
echo '      <x:ValidPrinterInfo/>';
echo '     </x:Print>';
echo '    </x:WorksheetOptions>';
echo '   </x:ExcelWorksheet>';
echo '  </x:ExcelWorksheets>';
echo ' </x:ExcelWorkbook>';
echo '</xml>';
echo '<![endif]-->';
echo '<style>';
echo '  body { font-family: Calibri, Arial, sans-serif; }';
echo '  table { border-collapse: collapse; width: 100%; }';
echo '  th { background-color: #28a745; color: white; border: 1px solid #000; padding: 10px; text-align: center; }';
echo '  td { border: 1px solid #000; padding: 5px; vertical-align: middle; text-align: center; }';
echo '  .text-left { text-align: left; }';
echo '</style>';
echo '</head>';
echo '<body>';

echo '<table>';
echo '<thead>';
echo '<tr>';
echo '<th>Report ID</th>';
echo '<th>Date</th>';
echo '<th>Project</th>';
echo '<th>Department</th>';
echo '<th>Risk Level</th>';
echo '<th>Status</th>';
echo '<th>Closed At</th>';
echo '<th>Created By</th>';
echo '<th>Hours Overdue</th>';
echo '<th>Description</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// Fetch and write rows
while ($row = $result->fetch_assoc()) {
    $risk = $row['risk'];
    $submitted = strtotime($row['report_date']);
    $isClosed = !empty($row['closed_at']);
    $closed = $isClosed ? strtotime($row['closed_at']) : time();
    
    // Calculate Threshold
    switch ($risk) {
        case 'عالية': $threshold = 8; break;
        case 'متوسطه': $threshold = 12; break;
        case 'منخفضة': $threshold = 24; break;
        default: $threshold = 48;
    }

    $delaySeconds = $closed - $submitted;
    $hoursDiff = round($delaySeconds / 3600);
    $overdueBy = max(0, $hoursDiff - $threshold);
    $status = $row['report_status'] == 1 ? 'Closed' : 'Open';
    
    echo '<tr>';
    echo '<td>' . $row['id'] . '</td>';
    echo '<td>' . $row['report_date'] . '</td>';
    echo '<td>' . htmlspecialchars($row['project_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['department_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['risk']) . '</td>';
    echo '<td>' . $status . '</td>';
    echo '<td>' . ($row['closed_at'] ?? 'Open') . '</td>';
    echo '<td>' . htmlspecialchars($row['created_by']) . '</td>';
    echo '<td style="color: red; font-weight: bold;">' . $overdueBy . ' hrs</td>';
    echo '<td class="text-left">' . htmlspecialchars($row['description']) . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</body>';
echo '</html>';

$conn->close();
exit();
?>
