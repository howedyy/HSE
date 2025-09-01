<?php
require_once "include/header.php";
require_once "constants/dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $permit_number = trim($_POST['permit_number'] ?? '');
    $cancellation_reason = trim($_POST['cancellation_reason'] ?? '');
    $completion_date = trim($_POST['completion_date'] ?? '');
    $admin_signature_3 = trim($_POST['admin_signature_3'] ?? '');
    $safety_signature_3 = trim($_POST['safety_signature_3'] ?? '');

    $work_status = (!empty($cancellation_reason)) ? 3 : 2; 

    $sql = "UPDATE PTW SET 
                ptw_status = ?, 
                work_status = ?, 
                cancellation_reason = ?, 
                completion_date = ?, 
                admin_signature_3 = ?, 
                safety_signature_3 = ? 
            WHERE permit_number = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssss", $work_status, $work_status, $cancellation_reason, $completion_date, $admin_signature_3, $safety_signature_3, $permit_number);
/*
    if ($stmt->execute()) {
        echo "<script>alert('تم إنهاء التصريح بنجاح'); window.location.href = 'ptw_dashboard.php';</script>";
    } else {
        echo "Error updating PTW: " . $stmt->error;
    }
*/
    $stmt->close();
    $conn->close();
    exit;
}

// If GET: fetch permit info
if (isset($_GET['permit_number'])) {
    $permit_number = $_GET['permit_number'];
    $sql = "SELECT * FROM PTW WHERE permit_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $permit_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        die("Invalid permit number.");
    }
    $stmt->close();
} else {
    die("No permit selected.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finish PTW</title>
    <link rel="stylesheet" href="custom/css/style.css">
</head>
<body>

<h2 class="h-5">Complete & Finalize PTW</h2>

<form id="finish_form" class="form-5" action="submit_button/submit_finish_ptw.php" method="POST">
    <input type="hidden" class="input-5" name="permit_number" value="<?php echo htmlspecialchars($row['permit_number']); ?>">

    <p class="P-5"><strong class="S-5">Project Name:</strong> <?php echo htmlspecialchars($row['project_name']); ?></p>
    <p class="P-5"><strong class="S-5">Work Location:</strong> <?php echo htmlspecialchars($row['work_location']); ?></p>
    <p class="P-5"><strong class="S-5">Permit Date:</strong> <?php echo htmlspecialchars($row['permit_date']); ?></p>
    <p class="P-5"><strong class="S-5">Operation Type:</strong> <?php echo htmlspecialchars($row['operation_type']); ?></p>
<!--
    <label>Completion Date:</label>
    <input type="date" name="completion_date" required>
-->
    <label class="label-5">Admin Signature:</label>
    <input class="input-5" type="text" name="admin_signature_3" required>
<!---
    <label>Safety Signature:</label>
    <input type="text" name="safety_signature_3" required>
--->
    <label class="label-5">Cancellation Reason (if work not completed):</label>
    <textarea class="textarea-5" name="cancellation_reason" placeholder="أدخل السبب فقط إذا لم يكتمل العمل"></textarea>

    <button class="button-5" type="submit">Finalize PTW</button>
</form>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const myForm = document.getElementById("finish_form");

    if (myForm) {
        myForm.addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent default form submission

            let formData = new FormData(this);

            fetch("submit_button/submit_finish_ptw.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log("Server Response:", data); // Debugging output

                if (data.trim() === "success") {
                    window.location.href = "ptw_overview.php"; // Redirect after successful update
                } else {
                    alert("حدث خطأ أثناء الإرسال:\n" + data);
                }
            })
            .catch(error => {
                console.error("حدث خطأ:", error);
            });
        });
    }
});
</script>
</body>
</html>