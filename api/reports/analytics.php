<?php
require_once __DIR__ . '/../header.php';

// RBAC Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Not authenticated."]);
    exit();
}

// Filters from request
$selectedProject = $_GET['project'] ?? '';
$selectedDepartment = $_GET['department'] ?? '';

$whereConditions = [];
$params = [];
$types = "";

if ($selectedProject !== '') {
    $whereConditions[] = "dr.project = ?";
    $params[] = (int)$selectedProject;
    $types .= "i";
}
if ($selectedDepartment !== '') {
    $whereConditions[] = "dr.department = ?";
    $params[] = (int)$selectedDepartment;
    $types .= "i";
}

$whereClause = count($whereConditions) > 0 ? "WHERE " . implode(" AND ", $whereConditions) : "";

// --- 1. Reports by Department ---
$deptSql = "SELECT d.department_name, COUNT(*) as count 
            FROM daily_report dr 
            LEFT JOIN department d ON dr.department = d.id 
            $whereClause 
            GROUP BY d.department_name 
            ORDER BY count DESC";
$stmt = $conn->prepare($deptSql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$deptResult = $stmt->get_result();
$byDepartment = [];
while($row = $deptResult->fetch_assoc()) {
    $byDepartment[] = [
        "name" => $row['department_name'] ?? 'Undefined',
        "count" => (int)$row['count']
    ];
}
$stmt->close();

// --- 2. Risk Breakdown ---
$riskSql = "SELECT risk, COUNT(*) as count FROM daily_report dr $whereClause GROUP BY risk";
$stmt = $conn->prepare($riskSql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$riskResult = $stmt->get_result();
$byRisk = [];
while($row = $riskResult->fetch_assoc()) {
    $byRisk[] = [
        "risk" => $row['risk'],
        "count" => (int)$row['count']
    ];
}
$stmt->close();

// --- 3. Reports Trends (Last 30 Days) ---
$trendConditions = $whereConditions;
$trendConditions[] = "dr.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$trendWhere = "WHERE " . implode(" AND ", $trendConditions);

$trendSql = "SELECT DATE(dr.date) as day, COUNT(*) as count 
             FROM daily_report dr 
             $trendWhere 
             GROUP BY day 
             ORDER BY day ASC";
$stmt = $conn->prepare($trendSql);
if (!$stmt) {
    die(json_encode(["error" => "Trend Query Prep Failed: " . $conn->error, "sql" => $trendSql]));
}
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$trendResult = $stmt->get_result();
$trend = [];
while($row = $trendResult->fetch_assoc()) {
    $trend[] = [
        "date" => $row['day'],
        "count" => (int)$row['count']
    ];
}
$stmt->close();

// --- 4. Overdue Analysis ---
$overdueCondition = "
(
    (dr.closed_at IS NULL AND TIMESTAMPDIFF(HOUR, dr.date, NOW()) >
        CASE
            WHEN dr.risk = 'عالية' OR dr.risk = 'High' THEN 8
            WHEN dr.risk = 'متوسطه' OR dr.risk = 'Medium' THEN 12
            WHEN dr.risk = 'منخفضة' OR dr.risk = 'Low' THEN 24
            ELSE 48
        END
    )
    OR
    (dr.closed_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, dr.date, dr.closed_at) >
        CASE
            WHEN dr.risk = 'عالية' OR dr.risk = 'High' THEN 8
            WHEN dr.risk = 'متوسطه' OR dr.risk = 'Medium' THEN 12
            WHEN dr.risk = 'منخفضة' OR dr.risk = 'Low' THEN 24
            ELSE 48
        END
    )
)";

$overdueConditions = $whereConditions;
$overdueConditions[] = $overdueCondition;
$overdueWhere = "WHERE " . implode(" AND ", $overdueConditions);

$overdueSql = "SELECT dr.id, dr.date, dr.risk, dr.report_status, dr.closed_at, 
                      pr.project_name, dp.department_name, u.username as created_by
               FROM daily_report dr
               LEFT JOIN project pr ON dr.project = pr.id
               LEFT JOIN department dp ON dr.department = dp.id
               LEFT JOIN users u ON dr.user_id = u.id
               $overdueWhere
               ORDER BY dr.date DESC";

$stmt = $conn->prepare($overdueSql);
if (!$stmt) {
    die(json_encode(["error" => "Overdue Query Prep Failed: " . $conn->error, "sql" => $overdueSql]));
}
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$overdueResult = $stmt->get_result();
$overdueItems = [];

while($row = $overdueResult->fetch_assoc()) {
    $submittedAt = strtotime($row['date']);
    $closedAt = $row['closed_at'] ? strtotime($row['closed_at']) : time();
    $delayHours = round(($closedAt - $submittedAt) / 3600);
    
    $threshold = 48;
    $risk = $row['risk'];
    if ($risk == 'عالية' || $risk == 'High') $threshold = 8;
    else if ($risk == 'متوسطه' || $risk == 'Medium') $threshold = 12;
    else if ($risk == 'منخفضة' || $risk == 'Low') $threshold = 24;

    $row['id'] = (int)$row['id'];
    $row['report_status'] = (int)$row['report_status'];
    $row['delay_hours'] = $delayHours;
    $row['exceeded_by'] = max(0, $delayHours - $threshold);
    $overdueItems[] = $row;
}
$stmt->close();

echo json_encode([
    "byDepartment" => $byDepartment,
    "byRisk" => $byRisk,
    "trend" => $trend,
    "overdue" => [
        "count" => count($overdueItems),
        "items" => $overdueItems
    ]
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>
