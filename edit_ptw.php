<?php
require_once "include/header.php"; 
require_once "constants/dbconnect.php"; 

// if (isset($_GET['permit_number']) && $_GET['action'] === 'approve') {
//     $permit_number = $_GET['permit_number'];
//     $new_status = 1; // Approved

//     $update_sql = "UPDATE PTW SET ptw_status = ? WHERE permit_number = ?";
//     $update_stmt = $conn->prepare($update_sql);

//     if ($update_stmt) {
//         $update_stmt->bind_param("is", $new_status, $permit_number);
//         $update_stmt->execute();
//         $update_stmt->close();
//     }
// }

// Now fetch permit data
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
} else {
    die("No permit selected.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit PTW</title>
    <link rel="stylesheet" href="custom/css/style.css">
</head>
<body>

<h2 class="h-5">Edit PTW Safety Details</h2>

<form id="my_form" method="POST" action="submit_button/submit_edit_ptw.php" class="form-5">
    <input type="hidden" class="input-5" name="permit_number" value="<?php echo htmlspecialchars($row['permit_number']); ?>">

    <p class="P-5"><strong class="S-5">Project Name:</strong> <?php echo htmlspecialchars($row['project_name']); ?></p>
    <p class="P-5"><strong class="S-5">Work Location:</strong> <?php echo htmlspecialchars($row['work_location']); ?></p>
    <p class="P-5"><strong class="S-5">Permit Date:</strong> <?php echo htmlspecialchars($row['permit_date']); ?></p>
    <p class="P-5"><strong class="S-5">Operation Type:</strong> <?php echo htmlspecialchars($row['operation_type']); ?></p>

    <label class="label-5">Safety Manager:</label>
    <input class="input-5" type="text" name="safety_manager" value="<?php echo htmlspecialchars($row['safety_manager']); ?>" required>

    <label class="label-5">Safety Signature:</label>
    <input class="input-5" type="text" name="safety_signature" value="<?php echo htmlspecialchars($row['safety_signature']); ?>" required>

    <button class="button-5" type="submit_3">Update Safety Details</button>
</form>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const myForm = document.getElementById("my_form");
    if (myForm) {
        myForm.addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent default submission

            let formData = new FormData(this);

            fetch("submit_button/submit_edit_ptw.php", {
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