<?php
require_once "constants/auth_check.php";
require_once "constants/dbconnect.php";

// Check permissions
if (!hasAccess('dailyreport_overview.php', 'delete')) {
    header("Location: unauthorized.php");
    exit();
}

if (isset($_GET['id'])) {
    $report_id = intval($_GET['id']);

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM daily_report WHERE id = ?");
    $stmt->bind_param("i", $report_id);

    if ($stmt->execute()) {
        // Redirect back with success message
        header("Location: dailyreport_overview.php?msg=deleted");
    } else {
        // Handle error gracefully
        header("Location: dailyreport_overview.php?msg=error&error=" . urlencode($conn->error));
    }
    $stmt->close();
} else {
    header("Location: dailyreport_overview.php");
}
$conn->close();
?>
