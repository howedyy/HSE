<?php
require_once "constants/dbconnect.php";
require_once "include/header.php";

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: user.php");
    exit();
}

$user_id = intval($_GET['id']);

// Fetch user details
$user_stmt = $conn->prepare("SELECT u.*, d.department_name FROM users u LEFT JOIN department d ON u.department_id = d.id WHERE u.id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    header("Location: user.php");
    exit();
}

$user = $user_result->fetch_assoc();

// Fetch user's current permissions
$perm_query = $conn->prepare("SELECT page, action FROM role_permissions WHERE user_id = ?");
$perm_query->bind_param("i", $user_id);
$perm_query->execute();
$perm_result = $perm_query->get_result();

$user_permissions = [];
while ($perm = $perm_result->fetch_assoc()) {
    $user_permissions[] = $perm['page'] . ':' . $perm['action'];
}

// Process form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update basic user information
    $username = $_POST['username'];
    $user_type = intval($_POST['user_type']);
    $department_id = intval($_POST['department_id']);
    $user_status = intval($_POST['user_status']);
    $editor_name = $_POST['editor_name'];
    $job_title = $_POST['job_title'];
    
    // Update password if provided
    $password_sql = '';
    $password_param = '';
    $stmt_types = 'sisisi';
    $stmt_params = [$username, $user_type, $editor_name, $job_title, $department_id, $user_status];
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ', password = ?';
        $stmt_types .= 's';
        $stmt_params[] = $password;
    }
    
    // Update user info
    $update_stmt = $conn->prepare("UPDATE users SET username = ?, user_type = ?, editor_name = ?, job_title = ?, department_id = ?, user_status = ?$password_sql WHERE id = ?");
    $stmt_types .= 'i';
    $stmt_params[] = $user_id;
    
    $update_stmt->bind_param($stmt_types, ...$stmt_params);
    $update_success = $update_stmt->execute();
    
    // Update permissions
    if ($update_success) {
        // Delete all existing permissions for the user
        $delete_stmt = $conn->prepare("DELETE FROM role_permissions WHERE user_id = ?");
        $delete_stmt->bind_param("i", $user_id);
        $delete_stmt->execute();
        
        // Insert new permissions
        $submitted_permissions = $_POST['permissions'] ?? [];
        
        if (!empty($submitted_permissions)) {
            $perm_stmt = $conn->prepare("INSERT INTO role_permissions (user_id, user_type, page, action) VALUES (?, ?, ?, ?)");
            
            foreach ($submitted_permissions as $perm) {
                [$page, $action] = array_pad(explode(':', $perm), 2, '');
                $perm_stmt->bind_param("iiss", $user_id, $user_type, $page, $action);
                $perm_stmt->execute();
            }
        }
        
        $message = '<div class="success-message">User updated successfully!</div>';
        
        // Refresh user data and permissions
        $user_stmt->execute();
        $user = $user_stmt->get_result()->fetch_assoc();
        
        $perm_query->execute();
        $perm_result = $perm_query->get_result();
        
        $user_permissions = [];
        while ($perm = $perm_result->fetch_assoc()) {
            $user_permissions[] = $perm['page'] . ':' . $perm['action'];
        }
    } else {
        $message = '<div class="error-message">Error updating user: ' . $conn->error . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="custom/css/style.css">
    <style>
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .form-container-6 {
            max-width: 900px;
        }
        
        .permission-group {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .page-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .page-section h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #495057;
        }
        
        .toggle-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .perm-label {
            flex: 1;
        }
        
        /* Toggle Switch Styles */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
        }
        
        input:checked + .slider {
            background-color: #2196F3;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .slider.round {
            border-radius: 24px;
        }
        
        .slider.round:before {
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <h2 class="h-6">Edit User: <?php echo htmlspecialchars($user['username']); ?></h2>
    
    <?php echo $message; ?>
    
    <div class="form-container-6">
        <form id="editUserForm" class="form-6" method="post">
            <table class="table-6">
                <tr class="TR-6">
                    <td class="TD-6"><label class="label-6" for="username">Username:</label></td>
                    <td class="TD-6"><input class="input-6" type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required></td>
                </tr>
                <tr class="TR-6">
                    <td class="TD-6"><label class="label-6" for="password">Password:</label></td>
                    <td class="TD-6"><input class="input-6" type="password" id="password" name="password" placeholder="Leave blank to keep current password"></td>
                </tr>
                <tr class="TR-6">
                    <td class="TD-6"><label class="label-6" for="user_type">User Type:</label></td>
                    <td class="TD-6">
                        <select id="user_type" name="user_type" class="user_type-6" required>
                            <option class="option-6" value="">Choose Type</option>
                            <option class="option-6" value="1" <?php echo $user['user_type'] == 1 ? 'selected' : ''; ?>>Admin</option>
                            <option class="option-6" value="2" <?php echo $user['user_type'] == 2 ? 'selected' : ''; ?>>HSE</option>
                            <option class="option-6" value="3" <?php echo $user['user_type'] == 3 ? 'selected' : ''; ?>>Operation</option>
                        </select>
                    </td>
                </tr>
                <tr class="TR-6">
                    <td class="TD-6"><label class="label-6" for="department_id">Department:</label></td>
                    <td class="TD-6">
                        <select id="department_id" name="department_id" class="user_type-6" required>
                            <option class="option-6" value="">Choose Department</option>
                            <?php
                            $dept_sql = "SELECT id, department_name FROM department WHERE department_status = 1";
                            $dept_result = $conn->query($dept_sql);
                            while ($dept = $dept_result->fetch_assoc()) {
                                $selected = ($dept['id'] == $user['department_id']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($dept['id']) . "' $selected>" . htmlspecialchars($dept['department_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr class="TR-6">
                    <td class="TD-6"><label class="label-6" for="user_status">Status:</label></td>
                    <td class="TD-6">
                        <select class="user_type-6" id="user_status" name="user_status" required>
                            <option class="option-6" value="">Choose</option>
                            <option class="option-6" value="1" <?php echo $user['user_status'] == 1 ? 'selected' : ''; ?>>Active</option>
                            <option class="option-6" value="0" <?php echo $user['user_status'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </td>
                </tr>
                <tr class="TR-6">
                    <td class="TD-6"><label class="label-6" for="editor_name">Full Name:</label></td>
                    <td class="TD-6"><input class="input-6" type="text" id="editor_name" name="editor_name" value="<?php echo htmlspecialchars($user['editor_name']); ?>" required></td>
                </tr>
                <tr class="TR-6">
                    <td class="TD-6"><label class="label-6" for="job_title">Job Title:</label></td>
                    <td class="TD-6"><input class="input-6" type="text" id="job_title" name="job_title" value="<?php echo htmlspecialchars($user['job_title']); ?>" required></td>
                </tr>
            </table>
            
            <legend>Customize Permissions</legend>

            <div class="permission-group" style="display: flex; flex-wrap: wrap; gap: 50px;">
                <?php
                $grouped = [];
                $permQuery = $conn->query("SELECT page_name, action, description FROM role_type ORDER BY page_name, action");

                while ($row = $permQuery->fetch_assoc()) {
                    $grouped[$row['page_name']][] = [
                        'label' => $row['description'] ?: ucfirst(pathinfo($row['page_name'], PATHINFO_FILENAME)) . ' (' . ucfirst($row['action']) . ')',
                        'value' => $row['page_name'] . ':' . $row['action']
                    ];
                }

                foreach ($grouped as $page => $permissions) {
                    echo "<div class='page-section'>";
                    echo "<h4>" . ucfirst(pathinfo($page, PATHINFO_FILENAME)) . "</h4>";
                    foreach ($permissions as $perm) {
                        $id = uniqid('perm_');
                        $checked = in_array($perm['value'], $user_permissions) ? 'checked' : '';
                        echo "<div class='toggle-wrapper'>";
                        echo "<span class='perm-label'>" . htmlspecialchars($perm['label']) . "</span>";
                        echo "<label class='switch'>";
                        echo "<input type='checkbox' id='$id' name='permissions[]' value='" . htmlspecialchars($perm['value']) . "' $checked>";
                        echo "<span class='slider round'></span>";
                        echo "</label>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
                ?>
            </div>

            <button type="submit" class="submit-btn-6">Update User</button>
        </form>
        
        <div style="margin-top: 20px;">
            <a href="user.php" class="btn btn-secondary">Back to Users List</a>
        </div>
    </div>

    <script>
    document.getElementById("user_type").addEventListener("change", function() {
        const selectedType = this.value;
        if (!selectedType) return;
        
        if (confirm("Do you want to load the default permissions for this user type? This will reset your current permission selections.")) {
            // Reset all checkboxes
            document.querySelectorAll("input[name='permissions[]']").forEach(cb => cb.checked = false);
            
            // Load default permissions for the selected role
            fetch("get_role_permissions.php?role_type=" + selectedType)
                .then(res => res.json())
                .then(permissions => {
                    permissions.forEach(perm => {
                        const checkbox = document.querySelector("input[name='permissions[]'][value='" + perm + "']");
                        if (checkbox) checkbox.checked = true;
                    });
                })
                .catch(err => console.error("Permission fetch error:", err));
        }
    });
    </script>
</body>
</html>