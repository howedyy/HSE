<?php
// Check authentication first
require_once(__DIR__ . '/../constants/auth_check.php');

include_once(__DIR__ . '/../constants/dbconnect.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $permit_number = trim($_POST['permit_number'] ?? '');
    #$work_status = intval($_POST['work_status'] ?? -1);
    $cancellation_reason = trim($_POST['cancellation_reason'] ?? '');
    $completion_date = trim($_POST['completion_date'] ?? '');
    $admin_signature_3 = trim($_POST['admin_signature_3'] ?? '');
    $safety_signature_3 = trim($_POST['safety_signature_3'] ?? '');
     $work_status = ($cancellation_reason !== '') ? 3 : 2;  // 3 = Not Completed, 2 = Finished
    $ptw_status = $work_status;



    if (empty($permit_number)) {
        die("Error: Permit number missing.");
    }

    // Debugging step: Print received POST data
    #echo "<pre>";
    #print_r($_POST);
    #echo "</pre>";

    // Check if permit_number exists before updating
    $check_sql = "SELECT permit_number FROM PTW WHERE permit_number = ?";
    $check_stmt = $conn->prepare($check_sql);

    if (!$check_stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $check_stmt->bind_param("s", $permit_number);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $sql = "UPDATE PTW SET 
                    ptw_status = ?,
                    work_status = ?, 
                    cancellation_reason = ?, 
                    completion_date = ?,  
                    admin_signature_3 = ?, 
                    safety_signature_3 = ? 
                WHERE permit_number = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
$stmt->bind_param("iisssss", $work_status, $work_status, $cancellation_reason, $completion_date, $admin_signature_3, $safety_signature_3, $permit_number);
        if (!$stmt) {
            die("Query preparation failed: " . $conn->error);
        }

$stmt->bind_param("iisssss", $work_status, $work_status, $cancellation_reason, $completion_date, $admin_signature_3, $safety_signature_3, $permit_number);

        if ($stmt->execute()) {
            $username = $_SESSION['username'] ?? 'Unknown';
            $action = ($work_status == 2) ? 'Finished' : 'Not Completed';
            $log_notes = ($work_status == 3) ? "Not completed reason: $cancellation_reason" : "Completed on: $completion_date";
            $log_sql = "INSERT INTO ptw_history (permit_number, action, action_by, action_date, notes) 
                        VALUES (?, ?, ?, NOW(), ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("ssss", $permit_number, $action, $username, $log_notes);
            $log_stmt->execute();
            $log_stmt->close();
            echo "success";
            #echo "PTW completed successfully!";
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: Permit number not found.";
    }

    $check_stmt->close();
    $conn->close();
}
?>