<?php
require_once __DIR__ . '/../header.php';

// --- Dashboard Stats (mirrors start_page.php logic) ---

$stats = [
    'total_ptw' => 0,
    'pending_ptw' => 0,
    'completed_ptw' => 0,
    'active_ptw' => 0,
    'today_reports' => 0,
];

$result = $conn->query("SELECT COUNT(*) as count FROM PTW");
$stats['total_ptw'] = $result ? (int)$result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE ptw_status = 0");
$stats['pending_ptw'] = $result ? (int)$result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE ptw_status = 2");
$stats['completed_ptw'] = $result ? (int)$result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE ptw_status = 1");
$stats['active_ptw'] = $result ? (int)$result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM daily_report WHERE DATE(date) = CURDATE()");
$stats['today_reports'] = $result ? (int)$result->fetch_assoc()['count'] : 0;

// --- Best Safety Practices ---
$total_good_sql = "SELECT COUNT(*) as count FROM daily_report WHERE observation_description = 'ممارسه جيده'";
$res = $conn->query($total_good_sql);
$total_good = $res ? (int)$res->fetch_assoc()['count'] : 0;

$best_project_sql = "
    SELECT p.project_name, COUNT(*) as good_count
    FROM daily_report dr LEFT JOIN project p ON dr.project = p.id
    WHERE dr.observation_description = 'ممارسه جيده'
    GROUP BY dr.project ORDER BY good_count DESC LIMIT 1
";
$res = $conn->query($best_project_sql);
$best = ($res && $row = $res->fetch_assoc()) ? $row : null;
$best_name = $best ? $best['project_name'] : 'None';
$best_count = $best ? (int)$best['good_count'] : 0;

$best_total = 1;
if ($best_name !== 'None') {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM daily_report dr LEFT JOIN project p ON dr.project = p.id WHERE p.project_name = ?");
    $stmt->bind_param("s", $best_name);
    $stmt->execute();
    $r = $stmt->get_result();
    $best_total = $r ? max((int)$r->fetch_assoc()['count'], 1) : 1;
    $stmt->close();
}
$best_pct = round(($best_count / $best_total) * 100);

// --- High Risk Monitoring ---
$res = $conn->query("SELECT COUNT(*) as count FROM daily_report WHERE risk = 'عالية' OR risk = 'High'");
$total_hr = $res ? (int)$res->fetch_assoc()['count'] : 0;

$overdue_sql = "SELECT COUNT(*) as count FROM daily_report WHERE (risk = 'عالية' OR risk = 'High') AND (
    (closed_at IS NULL AND TIMESTAMPDIFF(HOUR, date, NOW()) > 8)
    OR (closed_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, date, closed_at) > 8)
)";
$res = $conn->query($overdue_sql);
$overdue_hr = $res ? (int)$res->fetch_assoc()['count'] : 0;
$hr_compliance = $total_hr > 0 ? round((($total_hr - $overdue_hr) / $total_hr) * 100) : 100;

// Top risk project
$res = $conn->query("SELECT pr.project_name, COUNT(*) as count FROM daily_report dr LEFT JOIN project pr ON dr.project = pr.id WHERE (dr.risk = 'عالية' OR dr.risk = 'High') AND ((dr.closed_at IS NULL AND TIMESTAMPDIFF(HOUR, dr.date, NOW()) > 8) OR (dr.closed_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, dr.date, dr.closed_at) > 8)) GROUP BY dr.project ORDER BY count DESC LIMIT 1");
$top_risk_project = ($res && $row = $res->fetch_assoc()) ? $row['project_name'] : 'None';

// Top risk department
$res = $conn->query("SELECT d.department_name, COUNT(*) as count FROM daily_report dr LEFT JOIN department d ON dr.department = d.id WHERE (dr.risk = 'عالية' OR dr.risk = 'High') AND ((dr.closed_at IS NULL AND TIMESTAMPDIFF(HOUR, dr.date, NOW()) > 8) OR (dr.closed_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, dr.date, dr.closed_at) > 8)) GROUP BY dr.department ORDER BY count DESC LIMIT 1");
$top_risk_dept = ($res && $row = $res->fetch_assoc()) ? $row['department_name'] : 'None';

// --- Task Completion ---
$task_completion = $stats['total_ptw'] > 0 ? round(($stats['completed_ptw'] / $stats['total_ptw']) * 100) : 0;

// --- Monthly Trend (last 6 months) ---
$monthly = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $label = date('M', strtotime("-$i months"));
    $res = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE DATE_FORMAT(permit_date, '%Y-%m') = '$month'");
    $count = $res ? (int)$res->fetch_assoc()['count'] : 0;
    $monthly[] = ['month' => $label, 'count' => $count];
}

// --- Recent Incidents ---
$incidents = [];
$inc_sql = "SELECT dr.id, dr.date, dr.description, dr.risk, dr.report_status, p.project_name
            FROM daily_report dr LEFT JOIN project p ON dr.project = p.id
            ORDER BY dr.date DESC LIMIT 5";
$inc_res = $conn->query($inc_sql);
if ($inc_res) {
    while ($row = $inc_res->fetch_assoc()) {
        $incidents[] = [
            'id' => (int)$row['id'],
            'date' => $row['date'],
            'description' => $row['description'],
            'risk' => $row['risk'],
            'status' => (int)$row['report_status'],
            'project' => $row['project_name'] ?? 'N/A',
        ];
    }
}

// --- Build Response ---
echo json_encode([
    'stats' => $stats,
    'bestPractices' => [
        'percentage' => $best_pct,
        'projectName' => $best_name,
        'totalGood' => $total_good,
    ],
    'highRisk' => [
        'complianceRate' => $hr_compliance,
        'topProject' => $top_risk_project,
        'topDepartment' => $top_risk_dept,
        'totalHighRisk' => $total_hr,
        'overdueCount' => $overdue_hr,
    ],
    'taskCompletion' => $task_completion,
    'monthlyTrend' => $monthly,
    'recentIncidents' => $incidents,
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>
