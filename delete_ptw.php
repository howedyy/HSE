<?php
require_once "constants/auth_check.php";
require_once "constants/dbconnect.php";

// Check permissions
if (!hasAccess('ptw_overview.php', 'delete')) {
    header("Location: unauthorized.php");
    exit();
}

if (isset($_GET['permit_number'])) {
    $permit_number = $_GET['permit_number'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM PTW WHERE permit_number = ?");
    $stmt->bind_param("s", $permit_number);

    if ($stmt->execute()) {
        // Redirect back with success message
        header("Location: ptw_overview.php?msg=deleted");
    } else {
        // Handle error gracefully
        header("Location: ptw_overview.php?msg=error&error=" . urlencode($conn->error));
    }
    $stmt->close();
} else {
    header("Location: ptw_overview.php");
}
$conn->close();
?>
