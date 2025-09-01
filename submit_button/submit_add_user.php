<?php
include_once(__DIR__ . '/../constants/dbconnect.php');
require_once(__DIR__ . '/../constants/auth.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $plainPassword = $_POST['password'];
  $password = password_hash($plainPassword, PASSWORD_DEFAULT); // Properly hashed password
  $user_type = intval($_POST['user_type']);
  $editor_name = $_POST['editor_name'];
  $job_title = $_POST['job_title'];
  $department_id = intval($_POST['department_id']);
  $user_status = intval($_POST['user_status']);
  $formPermissions = $_POST['permissions'] ?? [];

  $stmt = $conn->prepare("INSERT INTO users (username, password, user_type, editor_name, job_title, department_id, user_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssii", $username, $password, $user_type, $editor_name, $job_title, $department_id, $user_status);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;


// Fetch default permissions from role_type_permissions table
$defaultPermissions = [];
$roleStmt = $conn->prepare("SELECT page, action FROM role_type_permissions WHERE role_type = ?");
$roleStmt->bind_param("i", $user_type);
$roleStmt->execute();
$res = $roleStmt->get_result();
while ($row = $res->fetch_assoc()) {
  $defaultPermissions[] = "{$row['page']}:{$row['action']}";
}
$roleStmt->close();


 
    // Merge role-based defaults and UI checkbox selections
    $allPermissions = array_unique(array_merge($defaultPermissions, $formPermissions));

    // Insert each permission as user-specific access
    $permStmt = $conn->prepare("INSERT INTO role_permissions (user_id, user_type, page, action) VALUES (?, ?, ?, ?)");
    if (!$permStmt) {
      die("❌ Permission prepare failed: " . $conn->error);
    }

    foreach ($allPermissions as $perm) {
      [$page, $action] = array_pad(explode(':', $perm), 2, '');
      $permStmt->bind_param("iiss", $user_id, $user_type,  $page, $action);
      $permStmt->execute();
    }

    echo "✅ User '{$username}' added successfully with personalized permissions.";
    $permStmt->close();
  } else {
    echo "❌ Error: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();
}
?>