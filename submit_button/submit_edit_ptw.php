<?php
// Check authentication first
require_once(__DIR__ . '/../constants/auth_check.php');

include_once(__DIR__ . '/../constants/dbconnect.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data safely
    $permit_number = trim($_POST['permit_number']);
    $safety_manager = trim($_POST['safety_manager']);
    $safety_signature = trim($_POST['safety_signature']);

    // Debugging step - Confirm permit number received
    if (empty($permit_number)) {
        die("Error: Permit number missing.");
    }

    // Check if permit_number exists before updating
    $check_sql = "SELECT permit_number FROM PTW WHERE permit_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $permit_number);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // Debugging step - Permit exists, proceed with update
        #echo "Updating row with permit_number: " . $permit_number . "<br>";

        // Ensure update applies only to the existing row
        $sql = "UPDATE PTW SET 
                    safety_manager = ?, 
                    safety_signature = ?, 
                    ptw_status =1
                WHERE permit_number = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $safety_manager, $safety_signature, $permit_number);

        if ($stmt->execute()) {
            $username = $_SESSION['username'] ?? 'Unknown';
            $log_notes = "Approved by: $safety_manager";
            $log_sql = "INSERT INTO ptw_history (permit_number, action, action_by, action_date, notes) 
                        VALUES (?, 'Approved', ?, NOW(), ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("sss", $permit_number, $username, $log_notes);
            $log_stmt->execute();
            $log_stmt->close();
            echo "success";
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: Permit number not found.";
    }

    // Close connections
    $check_stmt->close();
    $conn->close();
}
?>