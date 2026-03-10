<?php
require_once __DIR__ . '/../header.php';

// Filters from request
$selectedProject = $_GET['project'] ?? '';
$selectedDepartment = $_GET['department'] ?? '';
$selectedOperation = $_GET['operation'] ?? '';
$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';

$whereConditions = [];
$params = [];
$types = "";

if ($selectedProject !== '') {
    // PTW table might store project name, need to check. 
    // Based on ptw_analysis.php, it queries project table for name if project ID is provided.
    $stmt = $conn->prepare("SELECT project_name FROM project WHERE id = ?");
    $stmt->bind_param("i", $selectedProject);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $whereConditions[] = "p.project_name = ?";
        $params[] = $row['project_name'];
        $types .= "s";
    }
}

if ($selectedDepartment !== '') {
    $whereConditions[] = "p.department = ?";
    $params[] = (int)$selectedDepartment;
    $types .= "i";
}

if ($selectedOperation !== '') {
    $whereConditions[] = "p.operation_type = ?";
    $params[] = $selectedOperation;
    $types .= "s";
}

if ($startDate !== '') {
    $whereConditions[] = "p.permit_date >= ?";
    $params[] = $startDate;
    $types .= "s";
}

if ($endDate !== '') {
    $whereConditions[] = "p.permit_date <= ?";
    $params[] = $endDate;
    $types .= "s";
}

$whereClause = count($whereConditions) > 0 ? "WHERE " . implode(" AND ", $whereConditions) : "";

// 1. PTWs by Department
$deptQuery = "
    SELECT d.department_name as name, COUNT(*) as count 
    FROM ptw p
    LEFT JOIN department d ON p.department = d.id
    $whereClause
    GROUP BY d.department_name
    ORDER BY count DESC
";
$stmt = $conn->prepare($deptQuery);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$byDepartment = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 2. PTWs by Operation Type
$typeQuery = "
    SELECT p.operation_type as type, COUNT(*) as count 
    FROM ptw p
    $whereClause
    GROUP BY p.operation_type
    ORDER BY count DESC
";
$stmt = $conn->prepare($typeQuery);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$byType = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. PTWs Over Time (Last 30 days or filtered range)
$timeQuery = "
    SELECT p.permit_date as date, COUNT(*) as count 
    FROM ptw p
    $whereClause
    GROUP BY p.permit_date
    ORDER BY p.permit_date ASC
";
$stmt = $conn->prepare($timeQuery);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$overTime = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 4. Overdue PTWs
$overdueCondition = "p.ptw_status IN (0, 1, 3) AND p.permit_date < CURDATE()";
$overdueWhere = $whereClause ? "$whereClause AND $overdueCondition" : "WHERE $overdueCondition";

$overdueQuery = "
    SELECT p.id, d.department_name, p.operation_type, p.permit_date, p.ptw_status, pr.project_name
    FROM ptw p
    LEFT JOIN department d ON p.department = d.id
    LEFT JOIN project pr ON p.project_name = pr.project_name
    $overdueWhere
    ORDER BY p.permit_date DESC
";
$stmt = $conn->prepare($overdueQuery);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$overdueItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'byDepartment' => $byDepartment,
    'byType' => $byType,
    'overTime' => $overTime,
    'overdue' => [
        'count' => count($overdueItems),
        'items' => $overdueItems
    ],
    'filters' => [
        'project' => $selectedProject,
        'department' => $selectedDepartment,
        'operation' => $selectedOperation,
        'startDate' => $startDate,
        'endDate' => $endDate
    ]
], JSON_UNESCAPED_UNICODE);

$conn->close();
