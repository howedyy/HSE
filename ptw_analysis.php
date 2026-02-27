<?php
// Check authentication first
require_once "constants/auth_check.php";

require_once "constants/dbconnect.php";
require_once "include/header.php";
mysqli_set_charset($conn, "utf8mb4");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Filters
$selectedProject = $_GET['project'] ?? '';
$selectedDepartment = $_GET['department'] ?? '';
$selectedOperation = $_GET['operation'] ?? '';

if (isset($_GET['reset'])) {
    header("Location: ptw_analysis.php");
    exit;
}

$filterConditions = [];
if ($selectedProject !== '') {
    // The PTW table stores actual project names, not IDs
    // Get the project name from the ID
    $projectQuery = "SELECT project_name FROM project WHERE id = " . intval($selectedProject);
    $projectResult = $conn->query($projectQuery);
    if ($projectResult && $projectRow = $projectResult->fetch_assoc()) {
        $projectName = $projectRow['project_name'];
        $filterConditions[] = "p.project_name = '" . $conn->real_escape_string($projectName) . "'";
    }
}
if ($selectedDepartment !== '') {
    $filterConditions[] = "p.department = " . intval($selectedDepartment);
}
if ($selectedOperation !== '') {
    $filterConditions[] = "p.operation_type = '" . $conn->real_escape_string($selectedOperation) . "'";
}
$whereClause = count($filterConditions) ? "WHERE " . implode(" AND ", $filterConditions) : '';

function runQuery($conn, $sql, $label) {
    $result = $conn->query($sql);
    if (!$result) {
        echo "<h3 style='color:red;'>$label Query Failed:</h3><pre>{$conn->error}</pre><pre>$sql</pre>";
        exit;
    }
    return $result;
}

// Departments
$deptResult = runQuery($conn, "
  SELECT d.department_name, COUNT(*) AS count
  FROM ptw p
  LEFT JOIN department d ON p.department = d.id
  $whereClause
  GROUP BY d.department_name
", "Department");
$departments = $deptCounts = [];
while ($row = $deptResult->fetch_assoc()) {
    $departments[] = $row['department_name'] ?? '_';
    $deptCounts[] = $row['count'];
}

// Types
$typeResult = runQuery($conn, "
  SELECT p.operation_type, COUNT(*) AS count
  FROM ptw p
  $whereClause
  GROUP BY p.operation_type
", "Operation Type");
$types = $typeCounts = [];
while ($row = $typeResult->fetch_assoc()) {
    $types[] = $row['operation_type'];
    $typeCounts[] = $row['count'];
}

// Timeline
$dateResult = runQuery($conn, "
  SELECT p.permit_date, COUNT(*) AS count
  FROM ptw p
  $whereClause
  GROUP BY p.permit_date
  ORDER BY p.permit_date
", "Date");
$dates = $dateCounts = [];
while ($row = $dateResult->fetch_assoc()) {
    $dates[] = $row['permit_date'];
    $dateCounts[] = $row['count'];
}

// Overdue
$overdueCondition = "
  p.ptw_status IN (0, 1, 3)
  AND p.permit_date < CURDATE()
";
$finalClause = $whereClause ? "$whereClause AND $overdueCondition" : "WHERE $overdueCondition";

$overdueResult = runQuery($conn, "
  SELECT p.id, d.department_name, p.operation_type, p.permit_date, p.ptw_status
  FROM ptw p
  LEFT JOIN department d ON p.department = d.id
  $finalClause
", "Overdue");

$overduePTWs = [];
while ($row = $overdueResult->fetch_assoc()) {
    $overduePTWs[] = $row;
}
$overdueCount = count($overduePTWs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PTW Analysis</title>
  <script src="js/chart.umd.min.js"></script>
<style>
    body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 20px; }
    h2 { text-align: center; margin-top:50px; }
    .dashboard-wrapper { display: flex; gap: 20px; flex-wrap: wrap; }
    .charts-area { flex: 1; min-width: 600px; }
    
    .chart-container {
  background: #fff;
  padding: 10px;               
  border-radius: 8px;          
  box-shadow: 0 0 6px rgba(0,0,0,0.05);
  margin-bottom: 20px;         
  max-width: 600px;          
  margin: 0 auto;              
  transform: scale(0.9);      
}

    .sidebar {
      background: #fff; padding: 20px; border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05); margin-bottom: 30px;
    }
    .filter-form { 
    text-align: center;
     margin-bottom: 30px;
      }
    select { padding: 6px 10px; margin: 0 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    thead { background: #ffe792; }
    .alert-box { background: #fff3cd; border-left: 5px solid #ffc107; padding: 10px; margin-bottom: 10px; color: #856404; }
  </style>
</head>
<body>

<h2>📋 PTW Analysis Dashboard</h2>

<form method="GET" class="filter-form" id="filterForm">
  <select name="project" onchange="document.getElementById('filterForm').submit();">
    <option value="">All Projects</option>
    <?php
    $projects = $conn->query("SELECT id, project_name FROM project WHERE project_status = 1 ORDER BY project_name");
    while ($row = $projects->fetch_assoc()) {
      $sel = ($selectedProject == $row['id']) ? 'selected' : '';
      echo "<option value='{$row['id']}' $sel>" . htmlspecialchars($row['project_name']) . "</option>";
    }
    ?>
  </select>
  
  <select name="department" onchange="document.getElementById('filterForm').submit();">
    <option value="">All Departments</option>
    <?php
    $departmentsOpt = $conn->query("SELECT id, department_name FROM department WHERE department_status = 1 ORDER BY department_name");
    while ($row = $departmentsOpt->fetch_assoc()) {
      $sel = ($selectedDepartment == $row['id']) ? 'selected' : '';
      echo "<option value='{$row['id']}' $sel>" . htmlspecialchars($row['department_name']) . "</option>";
    }
    ?>
  </select>
  <select name="operation" onchange="document.getElementById('filterForm').submit();">
    <option value="">All Operations</option>
    <?php
    $operations = [
      "أعمال حفر", "أعمال لحام كهربي", "العمل على ارتفاع سبايدر", "أعمال رفع أحمال بمعدات ثقيلة",
      "العمل علي سقالة", "العمل على السلم المفصلى", "أعمال نقل بمعدات ثقيلة",
      "العمل بداخل الغرف المغلقة", "العمل على السلم الهيدروليكي", "إعمال كهرباء الجهد المتوسط", "العمل بالمواد الخطرة"
    ];
    foreach ($operations as $op) {
      $selected = ($selectedOperation === $op) ? 'selected' : '';
      echo "<option value=\"" . htmlspecialchars($op) . "\" $selected>$op</option>";
    }
    ?>
  </select>
<!--
  <button type="submit">Apply Filters</button>
  <button type="submit" name="reset" value="1">Reset</button>
  -->
</form>

<div class="dashboard-wrapper">
  <div class="charts-area">
    <div class="chart-container">
      <canvas id="ptwByDepartment"></canvas>
    </div>
    <div class="chart-container">
      <canvas id="ptwByType"></canvas>
    </div>
    <div class="chart-container">
      <canvas id="ptwOverTime"></canvas>
    </div>
  </div>

  <div class="sidebar">
    <h3>⚠️ Overdue PTWs (<?= $overdueCount ?>)</h3>
      <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 15px;">
        <button class="toggle-button" onclick="toggleSidebar()">Detailed List</button>
        <?php if (hasAccess('ptw_analysis.php', 'export_excel') || hasAccess('ptw_analysis.php', 'export') || hasAccess('ptw_analysis.php', 'view')): ?>
          <a href="export_ptw_overdue_excel.php?project=<?= urlencode($selectedProject) ?>&department=<?= urlencode($selectedDepartment) ?>&operation=<?= urlencode($selectedOperation) ?>" class="toggle-button" style="text-decoration: none; background: #28a745; color: white; display: inline-block;">
             📥 Export to Excel
          </a>
        <?php endif; ?>
      </div>

    <?php if ($overdueCount > 0): ?>
      <div class="alert-box">PTWs not finished and scheduled before today:</div>
      <table>
        <thead><tr><th>ID</th><th>Department</th><th>Type</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($overduePTWs as $r): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['department_name'] ?? '_') ?></td>
              <td><?= htmlspecialchars($r['operation_type']) ?></td>
              <td><?= $r['permit_date'] ?></td>
              <td>
                <?= $r['ptw_status'] == 0 ? "⏳ Not Approved" :
                   ($r['ptw_status'] == 1 ? "✅ Approved" :
                   ($r['ptw_status'] == 2 ? "✔ Finished" : "❌ Not Completed")) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p style="color: green;">🎉 No overdue PTWs!</p>
    <?php endif; ?>
  </div>
</div>

<script>

  function toggleSidebar() {
  const sidebar = document.getElementById('sidebarMenu');
  if (sidebar.style.display === 'none' || sidebar.style.display === '') {
    sidebar.style.display = 'block';
  } else {
    sidebar.style.display = 'none';
  }
}


document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('filterForm');
  const selects = form.querySelectorAll('select');
  selects.forEach(select => {
    select.addEventListener('change', function () {
      form.submit();
    });
  });
});


const deptLabels = <?= json_encode($departments) ?>;
const deptData = <?= json_encode($deptCounts) ?>;
const typeLabels = <?= json_encode($types) ?>;
const typeData = <?= json_encode($typeCounts) ?>;
const timeLabels = <?= json_encode($dates) ?>;
const timeData = <?= json_encode($dateCounts) ?>;

// Department Chart
new Chart(document.getElementById("ptwByDepartment"), {
  type: 'bar',
  data: {
    labels: deptLabels,
    datasets: [{
      label: 'PTWs by Department',
      data: deptData,
      backgroundColor: '#4A90E2'
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});

// Type Chart
new Chart(document.getElementById("ptwByType"), {
  type: 'pie',
  data: {
    labels: typeLabels,
    datasets: [{
      data: typeData,
      backgroundColor: [
        '#FF6384','#36A2EB','#FFCE56','#4BC0C0',
        '#9966FF','#FF9F40','#7ED6DF','#E84393',
        '#6C5CE7','#00B894','#D63031'
      ]
    }]
  },
  options: { responsive: true }
});

// Timeline Chart
new Chart(document.getElementById("ptwOverTime"), {
  type: 'line',
  data: {
    labels: timeLabels,
    datasets: [{
      label: 'PTWs Over Time',
      data: timeData,
      fill: true,
      borderColor: '#36A2EB',
      backgroundColor: 'rgba(54,162,235,0.2)',
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    scales: {
      x: { ticks: { maxRotation: 90, minRotation: 45 } },
      y: { beginAtZero: true }
    }
  }
});
</script>

</body>
</html>
