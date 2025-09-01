<?php
// Check authentication first
require_once "constants/auth_check.php";

require_once "constants/dbconnect.php";
require_once "include/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Users</title>
  <link rel="stylesheet" href="custom/css/style.css">
  <style>
    .edit-btn {
      display: inline-block;
      padding: 6px 12px;
      background-color: #4CAF50;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-size: 14px;
    }
    
    .edit-btn:hover {
      background-color: #45a049;
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2>All Users</h2>

  <table class="styled-table_1">
    <thead>
      <tr>
        <th>ID</th>
        <th>Username</th>
        <th>User Type</th>
        <th>Department</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Convert user_type numeric values to readable names
function getUserTypeName($typeCode) {
    switch ($typeCode) {
        case 1: return 'Admin';
        case 2: return 'HSE';
        case 3: return 'Operation';
        default: return 'Unknown';
    }
}

$sql = "SELECT users.id, users.username, users.user_type, department.department_name, users.user_status 
        FROM users 
        LEFT JOIN department ON users.department_id = department.id 
        ORDER BY users.id ASC";
      $result = $conn->query($sql);
      $i = 1;

      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $username = htmlspecialchars($row['username']);
          $userType = htmlspecialchars(getUserTypeName($row['user_type']));
          $department = htmlspecialchars($row['department_name'] ?? 'N/A');
          $status = ($row['user_status'] == 1) ? 'Active' : 'Inactive';

          echo '<tr>';
          $userId = $row['id'];
          echo '<td>' . $i++ . '</td>';
          echo '<td>' . $username . '</td>';
          echo '<td>' . $userType . '</td>';
          echo '<td>' . $department . '</td>';
          echo '<td>' . $status . '</td>';
          echo '<td><a href="edit_user.php?id=' . $userId . '" class="edit-btn">Edit User</a></td>';
          echo '</tr>';
        }
      } else {
        echo '<tr><td colspan="6">No users found.</td></tr>';
      }
      ?>
    </tbody>
  </table>
</div>

</body>
</html>