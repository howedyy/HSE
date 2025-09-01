<?php
// Check authentication first
require_once "constants/auth_check.php";
require_once "include/header.php";
require_once "constants/dbconnect.php";
require_once "constants/auth.php";

$loggedInUser = $_SESSION['username'] ?? '(not set)';
$userType = $_SESSION['user_type'] ?? 0;

// Get dashboard statistics
$stats = [
    'total_ptw' => 0,
    'pending_ptw' => 0,
    'completed_ptw' => 0,
    'today_reports' => 0
];

// Total PTWs
$result = $conn->query("SELECT COUNT(*) as count FROM PTW");
if ($result) {
    $stats['total_ptw'] = $result->fetch_assoc()['count'];
}

// Pending PTWs (status = 0)
$result = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE ptw_status = 0");
if ($result) {
    $stats['pending_ptw'] = $result->fetch_assoc()['count'];
}

// Completed PTWs (status = 2)
$result = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE ptw_status = 2");
if ($result) {
    $stats['completed_ptw'] = $result->fetch_assoc()['count'];
}

// Today's daily reports
$result = $conn->query("SELECT COUNT(*) as count FROM daily_report WHERE DATE(date) = CURDATE()");
if ($result) {
    $stats['today_reports'] = $result->fetch_assoc()['count'];
}

// Recent activities
$recent_activities = [];
$result = $conn->query("
    SELECT permit_number, action, action_by, action_date 
    FROM ptw_history 
    ORDER BY action_date DESC 
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}

// Monthly PTW data for chart
$monthly_data = [];
$months_ar = [
    'Jan' => 'يناير', 'Feb' => 'فبراير', 'Mar' => 'مارس',
    'Apr' => 'أبريل', 'May' => 'مايو', 'Jun' => 'يونيو',
    'Jul' => 'يوليو', 'Aug' => 'أغسطس', 'Sep' => 'سبتمبر',
    'Oct' => 'أكتوبر', 'Nov' => 'نوفمبر', 'Dec' => 'ديسمبر'
];

for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $result = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE DATE_FORMAT(permit_date, '%Y-%m') = '$month'");
    if ($result) {
        $month_key = date('M', strtotime("-$i months"));
        $monthly_data[] = [
            'month' => $month_key,
            'month_ar' => $months_ar[$month_key] ?? $month_key,
            'count' => $result->fetch_assoc()['count']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - نظام الصحة والسلامة المهنية</title>
    <link rel="stylesheet" href="assests/font-awesome/css/font-awesome.min.css">
    <script src="js/chart.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            direction: rtl;
        }

        /* Import Arabic font */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap');

        /* RTL improvements */
        .welcome-header h1 {
            text-align: center;
        }

        .actions-grid {
            text-align: center;
        }

        .action-btn {
            text-align: center;
        }

        .activity-item {
            text-align: right;
        }

        .activity-time {
            text-align: left;
        }

        /* Better spacing for Arabic text */
        .stat-label, .activity-action, .activity-details {
            line-height: 1.6;
        }

        /* Improve button spacing for Arabic */
        .action-btn span {
            white-space: nowrap;
        }

        /* Improve Arabic text rendering */
        .welcome-header h1, 
        .stat-label, 
        .action-btn span,
        .activity-action,
        .activity-details,
        .quick-actions h3,
        .chart-container h3,
        .recent-activity h3 {
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* Better number display for Arabic */
        .stat-number {
            font-family: 'Cairo', Arial, sans-serif;
            font-weight: 700;
        }

        /* Improve datetime display */
        #datetime {
            font-family: 'Cairo', Arial, sans-serif;
            font-weight: 500;
        }

        /* Touch-friendly improvements */
        .stat-card, .action-btn {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        /* Smooth scrolling for better UX */
        html {
            scroll-behavior: smooth;
        }

        /* Prevent text selection on interactive elements */
        .stat-card, .action-btn, .activity-item {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .welcome-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: slideDown 0.6s ease-out;
        }

        .welcome-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .welcome-header .user-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 5px 15px;
            border-radius: 25px;
            font-size: 0.8em;
            font-weight: normal;
        }

        .datetime-display {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.6s ease-out 0.2s both;
        }

        .datetime-display #datetime {
            font-size: 1.5em;
            color: #34495e;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            animation: fadeInUp 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-icon {
            font-size: 3em;
            margin-bottom: 15px;
            color: #667eea;
        }

        .stat-card.pending .stat-icon { color: #f39c12; }
        .stat-card.completed .stat-icon { color: #27ae60; }
        .stat-card.reports .stat-icon { color: #e74c3c; }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .stat-number.loading {
            opacity: 0.5;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 0.8; }
        }

        /* Loading spinner for stats refresh */
        .refresh-indicator {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(102, 126, 234, 0.9);
            color: white;
            padding: 10px 15px;
            border-radius: 25px;
            font-size: 0.9em;
            display: none;
            align-items: center;
            gap: 8px;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .refresh-indicator i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 1.1em;
            font-weight: 500;
        }

        .quick-actions {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: slideLeft 0.6s ease-out 0.4s both;
        }

        .quick-actions h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px 20px;
            border-radius: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .action-btn i {
            font-size: 1.2em;
        }

        .dashboard-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: slideRight 0.6s ease-out 0.6s both;
            overflow-x: auto;
        }

        .chart-wrapper {
            min-width: 300px;
            height: 300px;
            position: relative;
        }

        .recent-activity {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: slideLeft 0.6s ease-out 0.8s both;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            background: rgba(102, 126, 234, 0.05);
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateX(10px);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9em;
        }

        .activity-content {
            flex: 1;
        }

        .activity-action {
            font-weight: 600;
            color: #2c3e50;
        }

        .activity-details {
            font-size: 0.9em;
            color: #7f8c8d;
            margin-top: 2px;
        }

        .activity-time {
            font-size: 0.8em;
            color: #95a5a6;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tablet responsiveness */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 15px;
            }
            
            .dashboard-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .welcome-header h1 {
                font-size: 2.2em;
            }
        }

        /* Mobile landscape & small tablets */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 12px;
            }
            
            .welcome-header {
                padding: 20px 15px;
                margin-bottom: 20px;
            }
            
            .welcome-header h1 {
                font-size: 1.8em;
                flex-direction: column;
                gap: 8px;
            }
            
            .welcome-header .user-badge {
                font-size: 0.7em;
                padding: 4px 12px;
            }
            
            .datetime-display {
                padding: 15px;
                margin-bottom: 20px;
            }
            
            .datetime-display #datetime {
                font-size: 1.2em;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 12px;
            }
            
            .stat-card {
                padding: 20px 15px;
            }
            
            .stat-icon {
                font-size: 2.5em;
                margin-bottom: 10px;
            }
            
            .stat-number {
                font-size: 2em;
            }
            
            .stat-label {
                font-size: 1em;
            }
            
            .quick-actions {
                padding: 20px 15px;
                margin-bottom: 20px;
            }
            
            .quick-actions h3 {
                font-size: 1.3em;
                margin-bottom: 15px;
            }
            
            .actions-grid {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 10px;
            }
            
            .action-btn {
                padding: 12px 15px;
                font-size: 0.9em;
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
            
            .action-btn i {
                font-size: 1.5em;
            }
            
            .chart-container, .recent-activity {
                padding: 20px 15px;
            }
            
            .chart-container h3, .recent-activity h3 {
                font-size: 1.3em;
                margin-bottom: 15px;
            }
            
            .activity-item {
                padding: 12px;
                gap: 12px;
            }
            
            .activity-icon {
                width: 35px;
                height: 35px;
                font-size: 0.8em;
            }
            
            .activity-content {
                flex: 1;
                min-width: 0;
            }
            
            .activity-action {
                font-size: 0.9em;
            }
            
            .activity-details {
                font-size: 0.8em;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .activity-time {
                font-size: 0.75em;
                text-align: right;
                min-width: 60px;
            }
        }

                 /* Mobile portrait */
         @media (max-width: 480px) {
             .refresh-indicator {
                 top: 10px;
                 left: 10px;
                 padding: 8px 12px;
                 font-size: 0.8em;
             }
            .dashboard-container {
                padding: 10px;
            }
            
            .welcome-header {
                padding: 15px 10px;
                margin-bottom: 15px;
            }
            
            .welcome-header h1 {
                font-size: 1.5em;
                line-height: 1.3;
            }
            
            .welcome-header .user-badge {
                font-size: 0.65em;
                padding: 3px 10px;
            }
            
            .datetime-display {
                padding: 12px;
                margin-bottom: 15px;
            }
            
            .datetime-display #datetime {
                font-size: 1em;
                line-height: 1.4;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            
            .stat-card {
                padding: 15px 10px;
            }
            
            .stat-icon {
                font-size: 2em;
                margin-bottom: 8px;
            }
            
            .stat-number {
                font-size: 1.8em;
                margin-bottom: 3px;
            }
            
            .stat-label {
                font-size: 0.85em;
                line-height: 1.2;
            }
            
            .quick-actions {
                padding: 15px 10px;
                margin-bottom: 15px;
            }
            
            .quick-actions h3 {
                font-size: 1.2em;
                margin-bottom: 12px;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .action-btn {
                padding: 12px;
                font-size: 0.85em;
                flex-direction: row;
                text-align: left;
                gap: 10px;
            }
            
            .action-btn i {
                font-size: 1.2em;
            }
            
            .dashboard-row {
                gap: 15px;
            }
            
            .chart-container, .recent-activity {
                padding: 15px 10px;
            }
            
            .chart-container h3, .recent-activity h3 {
                font-size: 1.1em;
                margin-bottom: 12px;
            }
            
            .activity-item {
                padding: 10px;
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .activity-icon {
                width: 30px;
                height: 30px;
                font-size: 0.7em;
                flex-shrink: 0;
            }
            
            .activity-content {
                flex: 1;
                min-width: 150px;
            }
            
            .activity-action {
                font-size: 0.85em;
                font-weight: 600;
            }
            
            .activity-details {
                font-size: 0.75em;
                margin-top: 1px;
                white-space: normal;
                overflow: visible;
                text-overflow: initial;
            }
            
            .activity-time {
                font-size: 0.7em;
                width: 100%;
                text-align: left;
                margin-top: 5px;
                color: #bdc3c7;
            }
        }

        /* Very small screens */
        @media (max-width: 360px) {
            .dashboard-container {
                padding: 8px;
            }
            
            .welcome-header h1 {
                font-size: 1.3em;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 6px;
            }
            
            .stat-card {
                padding: 12px;
                display: flex;
                align-items: center;
                text-align: left;
                gap: 15px;
            }
            
            .stat-content {
                flex: 1;
            }
            
            .stat-icon {
                font-size: 2.5em;
                margin-bottom: 0;
                flex-shrink: 0;
            }
            
            .stat-number {
                font-size: 1.5em;
                margin-bottom: 2px;
            }
            
            .stat-label {
                font-size: 0.8em;
            }
            
            .action-btn {
                padding: 10px;
                font-size: 0.8em;
            }
        }

        /* Landscape orientation fixes */
        @media (max-width: 812px) and (orientation: landscape) {
            .welcome-header {
                padding: 15px;
            }
            
            .welcome-header h1 {
                font-size: 1.8em;
                flex-direction: row;
                gap: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .actions-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .dashboard-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* High resolution displays */
        @media (min-width: 1400px) {
            .dashboard-container {
                max-width: 1600px;
                padding: 30px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 25px;
            }
            
            .welcome-header h1 {
                font-size: 3em;
            }
            
            .stat-icon {
                font-size: 3.5em;
            }
            
            .stat-number {
                font-size: 3em;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Welcome Header -->
        <div class="welcome-header">
            <h1>
                <i class="fa fa-tachometer"></i>
                أهلاً وسهلاً بعودتك، <?= htmlspecialchars($loggedInUser) ?>!
                <span class="user-badge">
                    <i class="fa fa-user"></i> 
                    <?= $userType == 1 ? 'مدير' : ($userType == 2 ? 'مشرف' : 'مستخدم') ?>
                </span>
            </h1>
        </div>

        <!-- Date & Time -->
        <div class="datetime-display">
            <div id="datetime"></div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card" onclick="window.location.href='ptw_overview.php'">
                <i class="fa fa-clipboard stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-number" id="total-ptw"><?= $stats['total_ptw'] ?></div>
                    <div class="stat-label">إجمالي تصاريح العمل</div>
                </div>
            </div>
            
            <div class="stat-card pending" onclick="window.location.href='ptw_overview.php?status=0'">
                <i class="fa fa-clock-o stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-number" id="pending-ptw"><?= $stats['pending_ptw'] ?></div>
                    <div class="stat-label">التصاريح المعلقة</div>
                </div>
            </div>
            
            <div class="stat-card completed" onclick="window.location.href='ptw_overview.php?status=2'">
                <i class="fa fa-check-circle stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-number" id="completed-ptw"><?= $stats['completed_ptw'] ?></div>
                    <div class="stat-label">التصاريح المكتملة</div>
                </div>
            </div>
            
            <div class="stat-card reports" onclick="window.location.href='dailyreport_overview.php'">
                <i class="fa fa-file-text-o stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-number" id="today-reports"><?= $stats['today_reports'] ?></div>
                    <div class="stat-label">تقارير اليوم</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3><i class="fa fa-bolt"></i> الإجراءات السريعة</h3>
            <div class="actions-grid">
                <?php if (hasAccess('ptw.php', 'submit')): ?>
                <a href="PTW.php" class="action-btn">
                    <i class="fa fa-plus"></i>
                    <span>تصريح عمل جديد</span>
                </a>
                <?php endif; ?>

                <?php if (hasAccess('ptw_overview.php', 'view')): ?>
                <a href="ptw_overview.php" class="action-btn">
                    <i class="fa fa-list"></i>
                    <span>عرض التصاريح</span>
                </a>
                <?php endif; ?>

                <?php if (hasAccess('dailyreport.php', 'view')): ?>
                <a href="dailyreport.php" class="action-btn">
                    <i class="fa fa-file-text-o"></i>
                    <span>التقرير اليومي</span>
                </a>
                <?php endif; ?>

                <?php if (hasAccess('ptw_analysis.php', 'view')): ?>
                <a href="ptw_analysis.php" class="action-btn">
                    <i class="fa fa-bar-chart"></i>
                    <span>التحليلات</span>
                </a>
                <?php endif; ?>

                <?php if ($userType == 1): // Admin only ?>
                <a href="user.php" class="action-btn">
                    <i class="fa fa-users"></i>
                    <span>إدارة المستخدمين</span>
                </a>
                
                <a href="add_user.php" class="action-btn">
                    <i class="fa fa-user-plus"></i>
                    <span>إضافة مستخدم</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dashboard Row: Chart + Recent Activity -->
        <div class="dashboard-row">
            <!-- Chart Container -->
            <div class="chart-container">
                <h3><i class="fa fa-line-chart"></i> اتجاهات تصاريح العمل (آخر 6 أشهر)</h3>
                <div class="chart-wrapper">
                    <canvas id="ptwChart"></canvas>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h3><i class="fa fa-history"></i> الأنشطة الحديثة</h3>
                <div class="activity-list">
                    <?php if (empty($recent_activities)): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fa fa-info"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-action">لا توجد أنشطة حديثة</div>
                                <div class="activity-details">ابدأ بإنشاء تصريح عمل جديد</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fa fa-<?= $activity['action'] == 'Approved' ? 'check' : ($activity['action'] == 'Finished' ? 'flag' : 'edit') ?>"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-action">
                                    <?php
                                    $action_ar = $activity['action'];
                                    switch($activity['action']) {
                                        case 'Approved': $action_ar = 'تم الموافقة'; break;
                                        case 'Finished': $action_ar = 'تم الانتهاء'; break;
                                        case 'Created': $action_ar = 'تم الإنشاء'; break;
                                        case 'Updated': $action_ar = 'تم التحديث'; break;
                                        case 'Not Completed': $action_ar = 'لم يكتمل'; break;
                                        default: $action_ar = htmlspecialchars($activity['action']);
                                    }
                                    echo $action_ar;
                                    ?>
                                </div>
                                <div class="activity-details">
                                    تصريح: <?= htmlspecialchars($activity['permit_number']) ?> 
                                    بواسطة <?= htmlspecialchars($activity['action_by']) ?>
                                </div>
                            </div>
                            <div class="activity-time">
                                <?php
                                $date = new DateTime($activity['action_date']);
                                $months_ar = [
                                    'Jan' => 'يناير', 'Feb' => 'فبراير', 'Mar' => 'مارس',
                                    'Apr' => 'أبريل', 'May' => 'مايو', 'Jun' => 'يونيو',
                                    'Jul' => 'يوليو', 'Aug' => 'أغسطس', 'Sep' => 'سبتمبر',
                                    'Oct' => 'أكتوبر', 'Nov' => 'نوفمبر', 'Dec' => 'ديسمبر'
                                ];
                                $month_en = $date->format('M');
                                $month_ar = $months_ar[$month_en] ?? $month_en;
                                echo $date->format('j') . ' ' . $month_ar . '، ' . $date->format('H:i');
                                ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading indicator for stats refresh -->
    <div class="refresh-indicator" id="refreshIndicator">
        <i class="fa fa-refresh"></i>
        <span>جاري تحديث الإحصائيات...</span>
    </div>

    <script>
        // Update date and time
        function updateDateTime() {
            const now = new Date();
            
            // Arabic day names
            const days_ar = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
            const months_ar = [
                'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
            ];
            
            const day = days_ar[now.getDay()];
            const month = months_ar[now.getMonth()];
            const date = now.getDate();
            const year = now.getFullYear();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            
            const arabicDateTime = `${day}، ${date} ${month} ${year} - ${hours}:${minutes}:${seconds}`;
            
            document.getElementById('datetime').textContent = arabicDateTime;
        }
        
        setInterval(updateDateTime, 1000);
        updateDateTime();

        // Animate numbers counting up
        function animateNumber(element, target) {
            const start = 0;
            const duration = 1000;
            const increment = target / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 16);
        }

        // Animate stat numbers on page load
        window.addEventListener('load', () => {
            animateNumber(document.getElementById('total-ptw'), <?= $stats['total_ptw'] ?>);
            animateNumber(document.getElementById('pending-ptw'), <?= $stats['pending_ptw'] ?>);
            animateNumber(document.getElementById('completed-ptw'), <?= $stats['completed_ptw'] ?>);
            animateNumber(document.getElementById('today-reports'), <?= $stats['today_reports'] ?>);
        });

        // PTW Trend Chart
        const ctx = document.getElementById('ptwChart').getContext('2d');
        const monthlyData = <?= json_encode($monthly_data) ?>;
        
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.month_ar || item.month),
                datasets: [{
                    label: 'تصاريح العمل المُنشأة',
                    data: monthlyData.map(item => item.count),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                }
            }
        });

        // Add hover effects to stat cards (desktop) and touch effects (mobile)
        document.querySelectorAll('.stat-card').forEach(card => {
            // Desktop hover effects
            card.addEventListener('mouseenter', function() {
                if (window.innerWidth > 768) {
                    this.style.transform = 'translateY(-10px) scale(1.02)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                if (window.innerWidth > 768) {
                    this.style.transform = 'translateY(0) scale(1)';
                }
            });

            // Touch effects for mobile
            card.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
                this.style.opacity = '0.8';
            });

            card.addEventListener('touchend', function() {
                this.style.transform = 'scale(1)';
                this.style.opacity = '1';
            });
        });

        // Touch effects for action buttons
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
                this.style.opacity = '0.8';
            });

            btn.addEventListener('touchend', function() {
                this.style.transform = 'scale(1)';
                this.style.opacity = '1';
            });
        });

        // Optimize chart for mobile
        function optimizeChartForMobile() {
            const chart = Chart.getChart('ptwChart');
            if (chart && window.innerWidth <= 768) {
                chart.options.plugins.legend.display = true;
                chart.options.plugins.legend.position = 'bottom';
                chart.options.scales.x.ticks.maxRotation = 45;
                chart.options.scales.x.ticks.minRotation = 45;
                chart.update();
            }
        }

        // Call on resize
        window.addEventListener('resize', optimizeChartForMobile);

        // Function to refresh stats
        function refreshStats() {
            const indicator = document.getElementById('refreshIndicator');
            const statNumbers = document.querySelectorAll('.stat-number');
            
            // Show loading indicator
            indicator.style.display = 'flex';
            statNumbers.forEach(stat => stat.classList.add('loading'));
            
            fetch('get_dashboard_stats.php')
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        const data = result.data;
                        
                        // Animate number changes
                        animateNumberChange(document.getElementById('total-ptw'), data.total_ptw);
                        animateNumberChange(document.getElementById('pending-ptw'), data.pending_ptw);
                        animateNumberChange(document.getElementById('completed-ptw'), data.completed_ptw);
                        animateNumberChange(document.getElementById('today-reports'), data.today_reports);
                    }
                })
                .catch(error => {
                    console.log('Stats refresh failed:', error);
                    // Show error briefly
                    indicator.querySelector('span').textContent = 'فشل التحديث';
                    setTimeout(() => {
                        indicator.querySelector('span').textContent = 'جاري تحديث الإحصائيات...';
                    }, 2000);
                })
                .finally(() => {
                    // Hide loading indicator after delay
                    setTimeout(() => {
                        indicator.style.display = 'none';
                        statNumbers.forEach(stat => stat.classList.remove('loading'));
                    }, 1000);
                });
        }

        // Function to animate number changes
        function animateNumberChange(element, newValue) {
            const currentValue = parseInt(element.textContent);
            if (currentValue !== newValue) {
                const duration = 500;
                const steps = 10;
                const stepValue = (newValue - currentValue) / steps;
                let current = currentValue;
                
                const timer = setInterval(() => {
                    current += stepValue;
                    if ((stepValue > 0 && current >= newValue) || (stepValue < 0 && current <= newValue)) {
                        current = newValue;
                        clearInterval(timer);
                    }
                    element.textContent = Math.round(current);
                }, duration / steps);
            }
        }

        // Auto-refresh stats every 30 seconds
        setInterval(refreshStats, 30000);

        // Manual refresh on click (for development/debugging)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                refreshStats();
            }
        });
    </script>
</body>
</html>