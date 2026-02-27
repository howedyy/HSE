<?php
// Check authentication first
require_once "constants/auth_check.php";
require_once "include/header.php";
require_once "constants/dbconnect.php";
require_once "constants/auth.php";
require_once "include/language_setup.php"; // Enable Bilingual Support

$loggedInUser = $_SESSION['username'] ?? 'User';
$userType = $_SESSION['user_type'] ?? 0;

// --- Data Gathering ---

// 1. PTW Stats
$stats = [
    'total_ptw' => 0,
    'pending_ptw' => 0,
    'completed_ptw' => 0,
    'today_reports' => 0,
    'active_ptw' => 0 
];

$result = $conn->query("SELECT COUNT(*) as count FROM PTW");
$stats['total_ptw'] = $result ? $result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE ptw_status = 0");
$stats['pending_ptw'] = $result ? $result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE ptw_status = 2");
$stats['completed_ptw'] = $result ? $result->fetch_assoc()['count'] : 0;

// Active = Approved (1)
$result = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE ptw_status = 1");
$stats['active_ptw'] = $result ? $result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM daily_report WHERE DATE(date) = CURDATE()");
$stats['today_reports'] = $result ? $result->fetch_assoc()['count'] : 0;

// Best Safety Practices Metric - Project with most "Good Practices"
// Count total daily reports with "Good Practice" (ممارسه جيده)
$total_good_practices_sql = "SELECT COUNT(*) as count FROM daily_report WHERE observation_description = 'ممارسه جيده'";
$res = $conn->query($total_good_practices_sql);
$total_good_practices = $res ? $res->fetch_assoc()['count'] : 0;

// Get the project with most good practices
$best_project_sql = "
    SELECT p.project_name, COUNT(*) as good_practice_count
    FROM daily_report dr
    LEFT JOIN project p ON dr.project = p.id
    WHERE dr.observation_description = 'ممارسه جيده'
    GROUP BY dr.project
    ORDER BY good_practice_count DESC
    LIMIT 1
";
$res = $conn->query($best_project_sql);
$best_project_data = ($res && $row = $res->fetch_assoc()) ? $row : null;
$best_project_name = $best_project_data ? $best_project_data['project_name'] : 'None';
$best_project_count = $best_project_data ? $best_project_data['good_practice_count'] : 0;

// Calculate percentage of good practices for the best project
$best_project_total_sql = "
    SELECT COUNT(*) as count 
    FROM daily_report dr
    LEFT JOIN project p ON dr.project = p.id
    WHERE p.project_name = ?
";
$stmt = $conn->prepare($best_project_total_sql);
if ($stmt && $best_project_name !== 'None') {
    $stmt->bind_param("s", $best_project_name);
    $stmt->execute();
    $res = $stmt->get_result();
    $best_project_total = $res ? $res->fetch_assoc()['count'] : 1;
    $stmt->close();
} else {
    $best_project_total = 1;
}

$best_project_percentage = $best_project_total > 0 ? round(($best_project_count / $best_project_total) * 100) : 0;

// New Chart Data: High Risk Monitoring (from dailyreport_analysis.php logic)
$total_hr_sql = "SELECT COUNT(*) as count FROM daily_report WHERE risk = 'عالية' OR risk = 'High'";
$res = $conn->query($total_hr_sql);
$total_hr_count = $res ? $res->fetch_assoc()['count'] : 0;

$overdue_hr_sql = "SELECT COUNT(*) as count FROM daily_report WHERE (risk = 'عالية' OR risk = 'High') AND (
    (closed_at IS NULL AND TIMESTAMPDIFF(HOUR, date, NOW()) > 8)
    OR
    (closed_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, date, closed_at) > 8)
)";
$res = $conn->query($overdue_hr_sql);
$overdue_hr_total = $res ? $res->fetch_assoc()['count'] : 0;

$hr_compliance_rate = $total_hr_count > 0 ? round((($total_hr_count - $overdue_hr_total) / $total_hr_count) * 100) : 100;

// Top Project with High Risk Overdue
$hr_project_sql = "
    SELECT pr.project_name, COUNT(*) as count 
    FROM daily_report dr
    LEFT JOIN project pr ON dr.project = pr.id
    WHERE (dr.risk = 'عالية' OR dr.risk = 'High') AND (
        (dr.closed_at IS NULL AND TIMESTAMPDIFF(HOUR, dr.date, NOW()) > 8)
        OR
        (dr.closed_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, dr.date, dr.closed_at) > 8)
    )
    GROUP BY dr.project
    ORDER BY count DESC
    LIMIT 1
";
$hr_project_res = $conn->query($hr_project_sql);
$top_hr_project = ($hr_project_res && $row = $hr_project_res->fetch_assoc()) ? $row['project_name'] : 'None';

// Top Department with High Risk Overdue
$hr_dept_sql = "
    SELECT d.department_name, COUNT(*) as count 
    FROM daily_report dr
    LEFT JOIN department d ON dr.department = d.id
    WHERE (dr.risk = 'عالية' OR dr.risk = 'High') AND (
        (dr.closed_at IS NULL AND TIMESTAMPDIFF(HOUR, dr.date, NOW()) > 8)
        OR
        (dr.closed_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, dr.date, dr.closed_at) > 8)
    )
    GROUP BY dr.department
    ORDER BY count DESC
    LIMIT 1
";
$hr_dept_res = $conn->query($hr_dept_sql);
$top_hr_dept = ($hr_dept_res && $row = $hr_dept_res->fetch_assoc()) ? $row['department_name'] : 'None';

// New Chart Data: Task Completion
$task_completion_rate = $stats['total_ptw'] > 0 ? round(($stats['completed_ptw'] / $stats['total_ptw']) * 100) : 0;

// HSE User Activity Metric (Daily)
// Count total HSE users (user_type = 2)
$total_hse_users_sql = "SELECT COUNT(*) as count FROM users WHERE user_type = 2";
$res = $conn->query($total_hse_users_sql);
$total_hse_users = $res ? $res->fetch_assoc()['count'] : 0;

// Count active HSE users TODAY (those who created PTW or daily reports today)
// Note: PTW doesn't store user_id directly, we need to match by username from session
// daily_report uses user_id field
$active_hse_users_sql = "
    SELECT COUNT(DISTINCT u.id) as count 
    FROM users u
    WHERE u.user_type = 2 
    AND (
        EXISTS (
            SELECT 1 FROM PTW p 
            WHERE p.editor_name = u.editor_name 
            AND DATE(p.permit_date) = CURDATE()
        )
        OR EXISTS (
            SELECT 1 FROM daily_report dr 
            WHERE dr.user_id = u.id 
            AND DATE(dr.date) = CURDATE()
        )
    )
";
$res = $conn->query($active_hse_users_sql);
$active_hse_users = $res ? $res->fetch_assoc()['count'] : 0;

// Calculate daily activity rate
$hse_activity_rate = $total_hse_users > 0 ? round(($active_hse_users / $total_hse_users) * 100) : 0;

// Get most active user TODAY
$most_active_user_sql = "
    SELECT u.editor_name, u.username,
    (
        (SELECT COUNT(*) FROM PTW WHERE editor_name = u.editor_name AND DATE(permit_date) = CURDATE()) +
        (SELECT COUNT(*) FROM daily_report WHERE user_id = u.id AND DATE(date) = CURDATE())
    ) as activity_count
    FROM users u
    WHERE u.user_type = 2
    HAVING activity_count > 0
    ORDER BY activity_count DESC
    LIMIT 1
";
$res = $conn->query($most_active_user_sql);
$most_active_user = ($res && $row = $res->fetch_assoc()) ? $row['editor_name'] : 'None';
$most_active_count = ($res && isset($row['activity_count'])) ? $row['activity_count'] : 0;

// 3. Recent Incidents
$recent_incidents = [];
$inc_sql = "SELECT dr.id, dr.date, dr.description, dr.risk, dr.report_status, p.project_name 
            FROM daily_report dr 
            LEFT JOIN project p ON dr.project = p.id 
            ORDER BY dr.date DESC LIMIT 5";
$inc_res = $conn->query($inc_sql);
if ($inc_res) {
    while ($row = $inc_res->fetch_assoc()) {
        $recent_incidents[] = $row;
    }
}

// 4. Chart Data (Monthly)
$monthly_data = [];
$months_display = [];
$counts_display = [];

for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_en = date('M', strtotime("-$i months"));
    
    $res = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE DATE_FORMAT(permit_date, '%Y-%m') = '$month'");
    $count = $res ? $res->fetch_assoc()['count'] : 0;
    
    // Use Translation for Month
    $month_label = $translations[$lang_code]['months'][$month_en] ?? $month_en;
    
    $monthly_data[] = ['month' => $month_label, 'count' => $count];
    $months_display[] = $month_label;
    $counts_display[] = $count;
}
?>

<!DOCTYPE html>
<html lang="<?= $lang_code ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('app_name') ?> - Dashboard</title>
    <link rel="stylesheet" href="custom/css/modern_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/chart.umd.min.js"></script>
    <style>
        /* Dynamic Direction Styles */
        body { 
            font-family: 'Cairo', 'Segoe UI', sans-serif; 
        }
        
        /* Modern Header specific override to keep it LTR */
        .modern-header {
            direction: ltr; /* Force LTR for this block layout */
        }

        /* Adjustments for RTL layout elsewhere */
        <?php if($dir === 'rtl'): ?>
        .incident-item {
            padding-right: 1rem;
            padding-left: 1rem;
        }
        .incident-severity {
            margin-right: 1rem;
            margin-left: 0;
        }
        .incident-info {
            margin-right: 0;
            margin-left: 1rem;
        }
        .section-title {
            gap: 0.75rem;
        }
        .section-title i {
            margin-left: 0.5rem;
        }
        
        /* Ensure the header text also respects the requested LTR layout inside the flex container */
        .header-title {
            text-align: left;
        }
        <?php endif; ?>
    </style>
</head>
<body>

<div class="dashboard-container">
    
    <!-- Modern Header (Kept LTR as requested) -->
    <header class="modern-header">
        <div class="header-title">
            <h1><?= __('app_name') ?></h1>
            <div class="header-subtitle"><?= __('subtitle') ?></div>
        </div>
        <div class="header-actions">
            <!-- Language Switcher -->
            <a href="?lang=<?= $lang_code === 'en' ? 'ar' : 'en' ?>" class="lang-toggle">
                <i class="fas fa-globe"></i>
                <?= $lang_code === 'en' ? 'العربية' : 'English' ?>
            </a>

            <div class="date-badge">
                <i class="far fa-calendar-alt"></i>
                <?= date('F j, Y') ?>
            </div>
            <div class="date-badge" style="background:var(--primary-color); color:white; border:none;">
                <i class="far fa-user"></i>
                <?= htmlspecialchars($loggedInUser) ?>
            </div>
        </div>
    </header>

    <div class="dashboard-grid">
        
        <!-- Stats Cards -->
        <div class="stat-card-modern">
            <div class="stat-header">
                <div class="stat-icon-wrapper bg-emerald-100">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-value"><?= $stats['completed_ptw'] ?></div>
            <div class="stat-label-modern"><?= __('completed_permits') ?></div>
        </div>

        <div class="stat-card-modern">
            <div class="stat-header">
                <div class="stat-icon-wrapper bg-blue-100">
                    <i class="fas fa-hard-hat"></i>
                </div>
            </div>
            <div class="stat-value"><?= $stats['active_ptw'] ?></div>
            <div class="stat-label-modern"><?= __('active_permits') ?></div>
        </div>

        <div class="stat-card-modern">
            <div class="stat-header">
                <div class="stat-icon-wrapper bg-orange-100">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-value"><?= $stats['pending_ptw'] ?></div>
            <div class="stat-label-modern"><?= __('pending_approval') ?></div>
        </div>

        <div class="stat-card-modern">
            <div class="stat-header">
                <div class="stat-icon-wrapper bg-orange-100">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
            <div class="stat-value"><?= $stats['today_reports'] ?></div>
            <div class="stat-label-modern"><?= __('todays_reports') ?></div>
        </div>

        <!-- Best Safety Practices -->
        <div class="compliance-section" style="grid-column: span 3;">
            <div class="section-title">
                <i class="fas fa-award" style="color: var(--primary-color);"></i>
                <?= __('best_safety_practices') ?>
            </div>
            <div class="compliance-ring-container">
                <canvas id="bestPracticesChart"></canvas>
                <div class="compliance-score">
                    <div class="score-value"><?= $best_project_percentage ?>%</div>
                    <div class="score-label"><?= __('good_practices_rate') ?></div>
                </div>
            </div>
            <div style="margin-top: 1rem; width: 100%;">
                <p class="stat-label-modern" style="margin-bottom: 0.25rem;">
                    <?= __('best_project') ?>: 
                    <span style="color: var(--primary-color); font-weight: 700;"><?= htmlspecialchars($best_project_name) ?></span>
                </p>
                <p class="stat-label-modern" style="font-size: 0.75rem;">
                    <?= __('best_practices_desc') ?>
                </p>
            </div>
        </div>

        <div class="compliance-section" style="grid-column: span 3;">
            <div class="section-title">
                <i class="fas fa-exclamation-circle" style="color: var(--danger-color);"></i>
                <?= __('high_risk_monitoring') ?>
            </div>
            <div class="compliance-ring-container">
                <canvas id="incidentResolutionChart"></canvas> <!-- Reusing ID for existing Chart logic -->
                <div class="compliance-score">
                    <div class="score-value"><?= $hr_compliance_rate ?>%</div>
                    <div class="score-label"><?= __('high_risk_performance') ?></div>
                </div>
            </div>
            <div style="margin-top: 1rem; width: 100%;">
                <p class="stat-label-modern" style="margin-bottom: 0.25rem;"><?= __('top_risk_project') ?>: <span style="color: var(--danger-color); font-weight: 700;"><?= htmlspecialchars($top_hr_project) ?></span></p>
                <p class="stat-label-modern" style="font-size: 0.75rem;"><?= __('high_risk_desc') ?></p>
            </div>
        </div>

        <div class="compliance-section" style="grid-column: span 3;">
            <div class="section-title">
                <i class="fas fa-building" style="color: var(--warning-color);"></i>
                <?= __('high_risk_monitoring') ?>
            </div>
            <div class="compliance-ring-container">
                <canvas id="deptRiskChart"></canvas>
                <div class="compliance-score">
                    <div class="score-value"><?= $hr_compliance_rate ?>%</div>
                    <div class="score-label"><?= __('high_risk_performance') ?></div>
                </div>
            </div>
            <div style="margin-top: 1rem; width: 100%;">
                <p class="stat-label-modern" style="margin-bottom: 0.25rem;"><?= __('top_risk_dept') ?>: <span style="color: var(--warning-color); font-weight: 700;"><?= htmlspecialchars($top_hr_dept) ?></span></p>
                <p class="stat-label-modern" style="font-size: 0.75rem;"><?= __('dept_risk_desc') ?></p>
            </div>
        </div>

        <div class="compliance-section" style="grid-column: span 3;">
            <div class="section-title">
                <i class="fas fa-users-cog" style="color: #4f7d8a;"></i>
                <?= __('hse_user_activity') ?>
            </div>
            <div class="compliance-ring-container">
                <canvas id="hseActivityChart"></canvas>
                <div class="compliance-score">
                    <div class="score-value"><?= $hse_activity_rate ?>%</div>
                    <div class="score-label"><?= __('activity_rate') ?></div>
                </div>
            </div>
            <div style="margin-top: 1rem; width: 100%;">
                <p class="stat-label-modern" style="margin-bottom: 0.25rem;">
                    <?= __('most_active_user') ?>: 
                    <span style="color: #4f7d8a; font-weight: 700;"><?= htmlspecialchars($most_active_user) ?></span>
                </p>
                <p class="stat-label-modern" style="font-size: 0.75rem;">
                    <?= __('hse_activity_desc') ?>
                </p>
            </div>
        </div>

        <!-- Workforce/Activity Chart -->
        <div class="chart-section" style="grid-column: span 12;">
            <div class="section-title">
                <i class="fas fa-chart-line" style="color: var(--secondary-color);"></i>
                <?= __('activity_monitoring') ?>
            </div>
            <div style="height: 300px; width: 100%;">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

        <!-- Recent Incidents -->
        <div class="incident-list-container">
            <div class="section-title">
                <i class="fas fa-clipboard-list" style="color: var(--danger-color);"></i>
                <?= __('recent_incidents') ?>
            </div>
            
            <?php if (count($recent_incidents) > 0): ?>
                <?php foreach ($recent_incidents as $inc): ?>
                    <?php 
                        $severityClass = 'severity-low';
                        if ($inc['risk'] == 'عالية' || $inc['risk'] == 'High') $severityClass = 'severity-high';
                        elseif ($inc['risk'] == 'متوسطة' || $inc['risk'] == 'Medium') $severityClass = 'severity-medium';
                        
                        $statusClass = ($inc['report_status'] == 1) ? 'status-closed' : 'status-open';
                        $statusText = ($inc['report_status'] == 1) ? __('status_resolved') : __('status_open');
                    ?>
                    <div class="incident-item">
                        <div class="incident-severity <?= $severityClass ?>" title="<?= __('risk_level') ?>: <?= $inc['risk'] ?>"></div>
                        <div class="incident-info">
                            <div class="incident-title"><?= htmlspecialchars($inc['description']) ?></div>
                            <div class="incident-meta">
                                <span><i class="far fa-building"></i> <?= htmlspecialchars($inc['project_name'] ?? 'N/A') ?></span>
                                <span><i class="far fa-calendar"></i> <?= date('Y-m-d', strtotime($inc['date'])) ?></span>
                            </div>
                        </div>
                        <span class="status-pill <?= $statusClass ?>"><?= $statusText ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding: 2rem; color: var(--text-secondary);"><?= __('no_incidents') ?></div>
            <?php endif; ?>
            
        </div>

    </div>

    <!-- Quick Floating Actions -->
    <div class="quick-actions-bar">
        <a href="ptw_overview.php" class="action-chip">
            <i class="fas fa-file-signature" style="color: var(--primary-color);"></i> <?= __('new_permit') ?>
        </a>
        <a href="dailyreport_overview.php" class="action-chip">
            <i class="fas fa-camera" style="color: var(--secondary-color);"></i> <?= __('new_report') ?>
        </a>
        <a href="user.php" class="action-chip">
            <i class="fas fa-users" style="color: var(--text-primary);"></i> <?= __('manage_users') ?>
        </a>
    </div>

</div>

<script>
    // Font setup
    Chart.defaults.font.family = "'Cairo', sans-serif";

    // 1. Best Safety Practices Chart
    const ctxBest = document.getElementById('bestPracticesChart').getContext('2d');
    new Chart(ctxBest, {
        type: 'doughnut',
        data: {
            labels: ['<?= __('good_practices') ?>', '<?= __('other_reports') ?>'],
            datasets: [{
                data: [<?= $best_project_percentage ?>, <?= 100 - $best_project_percentage ?>],
                backgroundColor: ['#1c4d8d', '#f1f5f9'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '85%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { 
                    rtl: <?= $lang_code === 'ar' ? 'true' : 'false' ?>,
                    bodyFont: { family: 'Cairo' },
                    titleFont: { family: 'Cairo' }
                }
            }
        }
    });

    // 1.2 High Risk Monitoring Chart
    const ctxInc = document.getElementById('incidentResolutionChart').getContext('2d');
    new Chart(ctxInc, {
        type: 'doughnut',
        data: {
            labels: ['<?= __('compliant') ?>', '<?= __('non_compliant') ?>'],
            datasets: [{
                data: [<?= $hr_compliance_rate ?>, <?= 100 - $hr_compliance_rate ?>],
                backgroundColor: ['#f43f5e', '#f1f5f9'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '85%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { 
                    rtl: <?= $lang_code === 'ar' ? 'true' : 'false' ?>,
                    bodyFont: { family: 'Cairo' },
                    titleFont: { family: 'Cairo' }
                }
            }
        }
    });

    // 1.25 Dept High Risk Monitoring Chart
    const ctxDept = document.getElementById('deptRiskChart').getContext('2d');
    new Chart(ctxDept, {
        type: 'doughnut',
        data: {
            labels: ['<?= __('compliant') ?>', '<?= __('non_compliant') ?>'],
            datasets: [{
                data: [<?= $hr_compliance_rate ?>, <?= 100 - $hr_compliance_rate ?>],
                backgroundColor: ['#f59e0b', '#f1f5f9'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '85%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { 
                    rtl: <?= $lang_code === 'ar' ? 'true' : 'false' ?>,
                    bodyFont: { family: 'Cairo' },
                    titleFont: { family: 'Cairo' }
                }
            }
        }
    });

    // 1.3 HSE User Activity Chart
    const ctxHSE = document.getElementById('hseActivityChart').getContext('2d');
    new Chart(ctxHSE, {
        type: 'doughnut',
        data: {
            labels: ['<?= __('active_users') ?>', '<?= __('inactive_users') ?>'],
            datasets: [{
                data: [<?= $hse_activity_rate ?>, <?= 100 - $hse_activity_rate ?>],
                backgroundColor: ['#4988c4', '#f1f5f9'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '85%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { 
                    rtl: <?= $lang_code === 'ar' ? 'true' : 'false' ?>,
                    bodyFont: { family: 'Cairo' },
                    titleFont: { family: 'Cairo' }
                }
            }
        }
    });

    // 2. Activity Chart
    const ctxAct = document.getElementById('activityChart').getContext('2d');
    
    let gradient = ctxAct.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(28, 77, 141, 0.4)'); 
    gradient.addColorStop(1, 'rgba(28, 77, 141, 0.0)');

    new Chart(ctxAct, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_reverse($months_display), JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                label: '<?= __('permits_trend') ?>',
                data: <?= json_encode(array_reverse($counts_display)) ?>,
                borderColor: '#4988c4',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#1c4d8d',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { 
                    rtl: <?= $lang_code === 'ar' ? 'true' : 'false' ?>,
                    bodyFont: { family: 'Cairo' },
                    titleFont: { family: 'Cairo' }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { color: '#94a3b8', font: { family: 'Cairo' } },
                    position: '<?= $lang_code === 'ar' ? 'right' : 'left' ?>'
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { family: 'Cairo' } }
                }
            }
        }
    });
</script>

</body>
</html>
