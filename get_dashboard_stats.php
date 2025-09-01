<?php
// Check authentication first
require_once "constants/auth_check.php";
require_once "constants/dbconnect.php";

// Set JSON header
header('Content-Type: application/json');

// Get dashboard statistics
$stats = [
    'total_ptw' => 0,
    'pending_ptw' => 0,
    'completed_ptw' => 0,
    'today_reports' => 0
];

try {
    // Total PTWs
    $result = $conn->query("SELECT COUNT(*) as count FROM PTW");
    if ($result) {
        $stats['total_ptw'] = (int)$result->fetch_assoc()['count'];
    }

    // Pending PTWs (status = 0)
    $result = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE ptw_status = 0");
    if ($result) {
        $stats['pending_ptw'] = (int)$result->fetch_assoc()['count'];
    }

    // Completed PTWs (status = 2)
    $result = $conn->query("SELECT COUNT(*) as count FROM PTW WHERE ptw_status = 2");
    if ($result) {
        $stats['completed_ptw'] = (int)$result->fetch_assoc()['count'];
    }

    // Today's daily reports
    $result = $conn->query("SELECT COUNT(*) as count FROM daily_report WHERE DATE(date) = CURDATE()");
    if ($result) {
        $stats['today_reports'] = (int)$result->fetch_assoc()['count'];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'فشل في جلب الإحصائيات'
    ]);
}

$conn->close();
?> 