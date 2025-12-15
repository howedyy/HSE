<?php
require_once "dbconnect.php";
require_once "header.php";
require_once "auth.php";
date_default_timezone_set('Africa/Cairo');

// Handle filters
$selectedProject = $_GET['project'] ?? '';
$selectedDepartment = $_GET['department'] ?? '';
$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';
$entries = $_GET['entries'] ?? 'all';

// Build filter conditions
$filterConditions = [];

if ($selectedProject !== '') {
    $filterConditions[] = "dr.project = " . intval($selectedProject);
}
if ($selectedDepartment !== '') {
    $filterConditions[] = "dr.department = " . intval($selectedDepartment);
}
if ($startDate !== '') {
    $filterConditions[] = "dr.date >= '" . $conn->real_escape_string($startDate) . "'";
}
if ($endDate !== '') {
    $filterConditions[] = "dr.date <= '" . $conn->real_escape_string($endDate) . "'";
}

$whereClause = count($filterConditions) > 0 ? 'WHERE ' . implode(' AND ', $filterConditions) : '';

$sql = "
  SELECT 
    dr.id,
    dr.date,
    pr.project_name,
    dp.department_name,
    dr.work_type,
    dr.risk,
    dr.observation_description,
    dr.description,
    dr.observation,
    dr.operation_corrective,
    dr.report_status,
    dr.closed_at,
    dr.image_upload
  FROM daily_report dr
  LEFT JOIN project pr ON dr.project = pr.id
  LEFT JOIN department dp ON dr.department = dp.id
  $whereClause
  ORDER BY dr.date DESC
";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Daily Report Overview</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container">
  <h2>Daily Report Overview</h2>

  <!-- Unified Filter Form -->
  <form id="filterForm" method="GET" style="margin-bottom: 20px;">
    <div class="filter-section">
      <div class="date-filter-group">
        <input type="date" name="startDate" class="date-input" value="<?= htmlspecialchars($startDate) ?>">
        <input type="date" name="endDate" class="date-input" value="<?= htmlspecialchars($endDate) ?>">
      </div>

      <label>Project:
        <select name="project">
          <option value="">All Projects</option>
          <?php
          $projects = $conn->query("SELECT id, project_name FROM project WHERE project_status = 1 ORDER BY project_name");
          while ($row = $projects->fetch_assoc()) {
            $selected = ($selectedProject == $row['id']) ? 'selected' : '';
            echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['project_name']) . "</option>";
          }
          ?>
        </select>
      </label>

      <label style="margin-left: 15px;">Department:
        <select name="department">
          <option value="">All Departments</option>
          <?php
          $departments = $conn->query("SELECT id, department_name FROM department WHERE department_status = 1 ORDER BY department_name");
          while ($row = $departments->fetch_assoc()) {
            $selected = ($selectedDepartment == $row['id']) ? 'selected' : '';
            echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['department_name']) . "</option>";
          }
          ?>
        </select>
      </label>

      <label style="margin-left: 15px;">Entries:
        <select name="entries" id="entriesFilter">
          <option value="all" <?= $entries == 'all' ? 'selected' : '' ?>>Show All</option>
          <option value="5" <?= $entries == '5' ? 'selected' : '' ?>>5</option>
          <option value="10" <?= $entries == '10' ? 'selected' : '' ?>>10</option>
          <option value="20" <?= $entries == '20' ? 'selected' : '' ?>>20</option>
        </select>
      </label>

      <div class="button-row" style="margin-top: 10px;">
        <button type="submit" class="tiny-button">Apply</button>
        <button type="button" class="tiny-button" onclick="resetFilters()">Reset</button>
      </div>
    </div>
  </form>

  <?php if (hasAccess('dailyreport_overview.php', 'export')): ?>
    <form method="POST" action="export_daily_report_all.php">
      <button type="submit">📥 Export All Reports to Excel</button>
    </form>
  <?php endif; ?>

  <table class="styled-table_1">
    <thead>
      <tr>
        <th>Report Date</th>
        <th>Project</th>
        <th>Department</th>
        <th>Work Type</th>
        <th>Risk Level</th>
        <th>Observation</th>
        <th>Observation Description</th>
        <th>Corrective Action</th>
        <th>Description</th>
        <th>Status</th>
        <th>Closed At</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $rowCount = 0;
    $maxRows = ($entries === 'all') ? PHP_INT_MAX : intval($entries);

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        if (++$rowCount > $maxRows) break;
        $status_class = ($row['report_status'] == 0) ? "red" : "green";
        $status_text = ($row['report_status'] == 0) ? "Open" : "Closed";
        $report_id = $row['id'];
        $disabled = ($row['report_status'] == 1) ? "disabled" : "";
        $closedAt = ($row['closed_at']) ? date("Y-m-d H:i", strtotime($row['closed_at'])) : "—";
        ?>
          <tr id="row_<?= $report_id ?>" data-image="<?= htmlspecialchars($row['image_upload']) ?>">        
          <td><?= htmlspecialchars($row['date']) ?></td>
          <td><?= htmlspecialchars($row['project_name'] ?? '_') ?></td>
          <td><?= htmlspecialchars($row['department_name'] ?? '_') ?></td>
          <td><?= htmlspecialchars($row['work_type']) ?></td>
          <td><?= htmlspecialchars($row['risk']) ?></td>
          <td><?= htmlspecialchars($row['observation']) ?></td>
          <td><?= htmlspecialchars($row['observation_description']) ?></td>
          <td><?= htmlspecialchars($row['operation_corrective']) ?></td>
          <td><?= htmlspecialchars($row['description']) ?></td>
          <td><span id="status_<?= $report_id ?>" class="<?= $status_class ?>"><?= $status_text ?></span></td>
          <td id="closedAt_<?= $report_id ?>"><?= $closedAt ?></td>
          <td>
            <?php if (hasAccess('dailyreport_overview.php', 'submit')): ?>
              <button class='close-btn' onclick='closeObservation(<?= $report_id ?>)' <?= $disabled ?>>Close</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php
      }
    } else {
      echo "<tr><td colspan='12'>No reports found</td></tr>";
    }
    ?>
    </tbody>
  </table>
</div>

<script>
function closeObservation(reportId) {
  if (!confirm("Are you sure you want to close this observation?")) return;

  let formData = new FormData();
  formData.append("report_id", reportId);
  formData.append("report_status", 1);

  fetch("update_status.php", {
    method: "POST",
    body: formData
  })
  .then(response => response.text())
  .then(data => {
    alert("Observation closed successfully!");
    document.getElementById("status_" + reportId).className = "green";
    document.getElementById("status_" + reportId).innerText = "Closed";
    let button = document.querySelector("button[onclick='closeObservation(" + reportId + ")']");
    if (button) button.disabled = true;

    const now = new Date();
    const formatted = now.getFullYear() + '-' +
      String(now.getMonth() + 1).padStart(2, '0') + '-' +
      String(now.getDate()).padStart(2, '0') + ' ' +
      String(now.getHours()).padStart(2, '0') + ':' +
      String(now.getMinutes()).padStart(2, '0'); document.getElementById("closedAt_" + reportId).innerText = formatted; }) .catch(error => console.error("Error:", error)); }
function resetFilters() { 
  window.location.href = window.location.pathname; 
 }

 document.querySelectorAll("tbody tr").forEach(row => {
  row.addEventListener("click", function () {
    const imagePath = this.getAttribute("data-image");

    if (imagePath && imagePath.trim() !== "") {
      const imgWindow = window.open("", "_blank");
     imgWindow.document.write(`
  <html>
    <head><title>Observation Image</title></head>
    <body style="margin:0; padding:0; display:flex; justify-content:center; align-items:center; height:100vh; background:#111;">
      <img src="${'http://localhost/Edara-HSE111/' + imagePath}" alt="Observation Image" style="max-width:90%; max-height:90%; box-shadow:0 0 15px rgba(0,0,0,0.6);" />
    </body>
  </html>
`);
    } else {
      alert("No image associated with this observation.");
    }
  });
});
  </script>
</body>
</html>


