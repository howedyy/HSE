<?php
// Check authentication first
require_once "constants/auth_check.php";

require_once "constants/dbconnect.php";
date_default_timezone_set('Africa/Cairo');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'], $_POST['report_status'])) {
    $report_id = (int)$_POST['report_id'];
    $status = (int)$_POST['report_status'];

    if ($status === 1) {
        // Set closed_at to Cairo local time
        $now = date("Y-m-d H:i:s");
        $sql = "UPDATE daily_report SET report_status = 1, closed_at = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $now, $report_id);
    } else {
        $sql = "UPDATE daily_report SET report_status = 0, closed_at = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $report_id);
    }

    if ($stmt->execute()) {
        echo "Status updated";
    } else {
        http_response_code(500);
        echo "Failed to update: " . $stmt->error;
    }
} else {
    http_response_code(400);
    echo "Invalid request";
}



?>