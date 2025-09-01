<?php
require_once "constants/dbconnect.php";
require_once "include/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add User</title>
  <link rel="stylesheet" href="custom/css/style.css">
</head>
<body>

  <h2 class="h-6">Add User</h2>
  <div class="form-container-6">

  <form id="userForm" class="form-6" action="submit_button/submit_add_user.php">
    <table class="table-6">
      <tr class="TR-6">
        <td class="TD-6"><label class="label-6" for="username">Username:</label></td>
        <td class="TD-6"><input class="input-6" type="text" id="username" name="username" required></td>
      </tr>
      <tr class="TR-6">
        <td class="TD-6"><label class="label-6" for="password">Password:</label></td>
        <td class="TD-6"><input class="input-6" type="password" id="password" name="password" required></td>
      </tr>
      <tr class="TR-6">
        <td class="TD-6"><label class="label-6" for="user_type">User Type:</label></td>
        <td class="TD-6">
          <select id="user_type" name="user_type" class="user_type-6" required>
                        <option class="option-6" value="">Choose Type</option>
            <option class="option-6" value="1">Admin</option>
            <option class="option-6" value="2">HSE</option>
            <option class="option-6" value="3">Operation</option>
          </select>
        </td>
      </tr>
      <tr class="TR-6">
        <td class="TD-6"><label class="label-6" for="department_id">Department:</label></td>
        <td class="TD-6">
          <select id="department_id" name="department_id" class="user_type-6" required>
            <option class="option-6" value="">Choose Department</option>
            <?php
            $sql = "SELECT id, department_name FROM department WHERE department_status = 1";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
              echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['department_name']) . "</option>";
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
            <option class="option-6" value="1">Active</option>
            <option class="option-6" value="0">Inactive</option>
          </select>
        </td>
      </tr>
      <tr class="TR-6">
        <td class="TD-6"><label class="label-6" for="editor_name">Full Name:</label></td>
        <td class="TD-6"><input class="input-6" type="text" id="editor_name" name="editor_name" required></td>
      </tr>
      <tr class="TR-6">
        <td class="TD-6"><label class="label-6" for="job_title">Job Title:</label></td>
        <td class="TD-6"><input class="input-6" type="text" id="job_title" name="job_title" required></td>
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
        echo "<div class='toggle-wrapper'>";
        echo "<span class='perm-label'>" . htmlspecialchars($perm['label']) . "</span>";
        echo "<label class='switch'>";
        echo "<input type='checkbox' id='$id' name='permissions[]' value='" . htmlspecialchars($perm['value']) . "'>";
        echo "<span class='slider round'></span>";
        echo "</label>";
        echo "</div>";
    }
    echo "</div>";
}
  ?>
</div>

    <button type="submit" class="submit-btn-6">Add User</button>
  </form>

  <div id="response"></div>
</div>

<script>
document.getElementById("userForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch("submit_button/submit_add_user.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    document.getElementById("response").innerHTML = data;
    document.getElementById("userForm").reset();
  })
  .catch(err => {
    document.getElementById("response").innerHTML = "An error occurred.";
    console.error(err);
  });
});

document.getElementById("user_type").addEventListener("change", function () {
  const selectedType = this.value;

  document.querySelectorAll("input[name='permissions[]']").forEach(cb => cb.checked = false);

  if (!selectedType) return;

  fetch("get_role_permissions.php?role_type=" + selectedType)
    .then(res => res.json())
    .then(permissions => {
      permissions.forEach(perm => {
        const checkbox = document.querySelector("input[name='permissions[]'][value='" + perm + "']");
        if (checkbox) checkbox.checked = true;
      });
    })
    .catch(err => console.error("Permission fetch error:", err));
});


</script>

</body>
</html>