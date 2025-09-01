<?php
// Check authentication first
require_once "constants/auth_check.php";

require_once "constants/auth.php";
require_once "constants/dbconnect.php";
require_once "include/header.php";

date_default_timezone_set('Africa/Cairo');

if (!isset($_GET['permit_number'])) {
    header("Location: ptw_overview.php");
    exit();
}

$permit_number = $_GET['permit_number'];

$sql = "SELECT p.*, d.department_name FROM ptw p 
        LEFT JOIN department d ON p.department = d.id
        WHERE p.permit_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $permit_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "PTW not found";
    exit();
}

$ptw = $result->fetch_assoc();

if ($ptw['ptw_status'] != 1) {
    echo "This PTW cannot be marked as non-compliance at this time.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Non Compliance - PTW <?php echo htmlspecialchars($permit_number); ?></title>
    <link rel="stylesheet" href="custom/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .non-compliance-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 2px solid #3333ff;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        
        .form-header {
            color: #3333ff;
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e1e1e1;
            margin-bottom: 20px;
        }
        
        .form-row {
            margin-bottom: 15px;
        }
        
        .form-row label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        .read-only-field {
            background-color: #f0f0f0;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        .submit-button {
            background-color: #3333ff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            margin: 20px auto 0;
        }
        
        .submit-button:hover {
            background-color: #2222bb;
        }
        
        .cancel-link {
            text-align: center;
            display: block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1 class="form-header">Non Compliance PTW Details</h1>
    
    <div class="non-compliance-container">
        <form action="submit_button/submit_noncompliance_ptw.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="permit_number" value="<?php echo htmlspecialchars($permit_number); ?>">
            
            <div class="form-row">
                <label>Project Name:</label>
                <div class="read-only-field"><?php echo htmlspecialchars($ptw['project_name']); ?></div>
            </div>
            
            <div class="form-row">
                <label>Work Location:</label>
                <div class="read-only-field"><?php echo htmlspecialchars($ptw['work_location']); ?></div>
            </div>
            
            <div class="form-row">
                <label>Permit Date:</label>
                <div class="read-only-field"><?php echo htmlspecialchars($ptw['permit_date']); ?></div>
            </div>
            
            <div class="form-row">
                <label>Operation Type:</label>
                <div class="read-only-field"><?php echo htmlspecialchars($ptw['operation_type']); ?></div>
            </div>
            
            <div class="form-row">
                <label for="noncompliance_reason">Non Compliance Reason:</label>
                <textarea name="noncompliance_reason" id="noncompliance_reason" class="form-control" required></textarea>
            </div>
            
            <div class="form-row">
                <label for="safety_officer">Safety Officer Name:</label>
                <input type="text" name="safety_officer" id="safety_officer" class="form-control" value="<?php echo htmlspecialchars($_SESSION['fullname'] ?? ''); ?>" required>
            </div>
            
            <div class="form-row">
                <label for="corrective_actions">Corrective Actions Required:</label>
                <textarea name="corrective_actions" id="corrective_actions" class="form-control" required></textarea>
            </div>
            
            <div class="form-row">
                <label for="noncompliance_images">Upload Images (Optional):</label>
                <input type="file" name="noncompliance_images[]" id="noncompliance_images" class="form-control" multiple accept="image/*">
                <small style="color: #666; display: block; margin-top: 5px;">Upload images showing the non-compliance (JPG, PNG max 10MB each)</small>
            </div>
            
            <button type="submit" class="submit-button">Mark as Non Compliance</button>
            <a href="ptw_overview.php" class="cancel-link">Cancel</a>
        </form>
    </div>
</body>
</html> 