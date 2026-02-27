<?php
// Export Overdue PTWs to Excel (HTML format for better styling and Arabic support)
require_once "constants/dbconnect.php";
require_once "constants/auth_check.php"; // Ensure user is logged in

if (!hasAccess('ptw_analysis.php', 'export_excel') && !hasAccess('ptw_analysis.php', 'export') && !hasAccess('ptw_analysis.php', 'view')) {
    die("Unauthorized Access");
}
mysqli_set_charset($conn, "utf8mb4");

// Apply Filters
$selectedProject = $_GET['project'] ?? '';
$selectedDepartment = $_GET['department'] ?? '';
$selectedOperation = $_GET['operation'] ?? '';

$filterConditions = [];

if ($selectedProject !== '') {
    // The PTW table stores actual project names, not IDs
    // Get the project name from the ID
    $projectQuery = "SELECT project_name FROM project WHERE id = " . intval($selectedProject);
    $projectResult = $conn->query($projectQuery);
    if ($projectResult && $projectRow = $projectResult->fetch_assoc()) {
        $projectName = $projectRow['project_name'];
        $filterConditions[] = "p.project_name = '" . $conn->real_escape_string($projectName) . "'";
    }
}
if ($selectedDepartment !== '') {
    $filterConditions[] = "p.department = " . intval($selectedDepartment);
}
if ($selectedOperation !== '') {
    $filterConditions[] = "p.operation_type = '" . $conn->real_escape_string($selectedOperation) . "'";
}

$whereClause = count($filterConditions) ? "WHERE " . implode(' AND ', $filterConditions) : '';

// Overdue Condition Logic: Status 0, 1, 3 and Date < Today
$overdueCondition = "p.ptw_status IN (0, 1, 3) AND p.permit_date < CURDATE()";

$finalClause = $whereClause ? "$whereClause AND $overdueCondition" : "WHERE $overdueCondition";

// Query to fetch overdue PTWs
$sql = "
    SELECT 
        p.id,
        p.permit_number,
        p.project_name,
        d.department_name,
        p.operation_type,
        p.permit_date,
        p.ptw_status,
        p.work_location
    FROM ptw p
    LEFT JOIN department d ON p.department = d.id
    $finalClause
    ORDER BY p.permit_date ASC
";

$result = $conn->query($sql);
if (!$result) {
    die("Query Failed: " . $conn->error);
}

// Generate Filename
$filename = "overdue_ptw_" . date('Y-m-d') . ".xls";

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
echo '    <x:Name>Overdue PTWs</x:Name>';
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
echo '  th { background-color: #d9534f; color: white; border: 1px solid #000; padding: 10px; text-align: center; }'; // Using red to signify overdue/alert
echo '  td { border: 1px solid #000; padding: 5px; vertical-align: middle; text-align: center; }';
echo '  .text-left { text-align: left; }';
echo '</style>';
echo '</head>';
echo '<body>';

echo '<table>';
echo '<thead>';
echo '<tr>';
echo '<th>Permit No</th>';
echo '<th>Department</th>';
echo '<th>Project</th>';
echo '<th>Work Location</th>';
echo '<th>Operation Type</th>';
echo '<th>Permit Date</th>';
echo '<th>Status</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// Fetch and write rows
while ($row = $result->fetch_assoc()) {
    $statusText = "";
    switch ($row['ptw_status']) {
        case 0: $statusText = "Not Approved"; break;
        case 1: $statusText = "Approved"; break;
        case 2: $statusText = "Finished"; break;
        case 3: $statusText = "Not Completed"; break;
        case 4: $statusText = "Non Compliance"; break;
        default: $statusText = "Unknown";
    }

    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['permit_number']) . '</td>';
    echo '<td>' . htmlspecialchars($row['department_name'] ?? '_') . '</td>';
    echo '<td>' . htmlspecialchars($row['project_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['work_location']) . '</td>';
    echo '<td>' . htmlspecialchars($row['operation_type']) . '</td>';
    echo '<td>' . $row['permit_date'] . '</td>';
    echo '<td>' . $statusText . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</body>';
echo '</html>';

$conn->close();
exit();
?>
