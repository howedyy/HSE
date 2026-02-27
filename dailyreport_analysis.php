<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "include/header.php";
require_once "constants/dbconnect.php";
mysqli_set_charset($conn, "utf8mb4");

//  Filters
$selectedProject = $_GET['project'] ?? '';
$selectedDepartment = $_GET['department'] ?? '';

$filterConditions = [];
if ($selectedProject !== '') {
  $filterConditions[] = "project = " . intval($selectedProject);
}
if ($selectedDepartment !== '') {
  $filterConditions[] = "department = " . intval($selectedDepartment);
}
$whereClause = count($filterConditions) ? "WHERE " . implode(' AND ', $filterConditions) : '';

function runQuery($conn, $sql, $label)
{
  $result = $conn->query($sql);
  if (!$result) {
    die("$label Query Failed: " . $conn->error . "<br><code>$sql</code>");
  }
  return $result;
}

//  Reports by Department
$deptResult = runQuery($conn, "
  SELECT d.department_name, COUNT(*) as count
  FROM daily_report dr
  LEFT JOIN department d ON dr.department = d.id
  $whereClause
  GROUP BY d.department_name
  ORDER BY count DESC
", "Department");

$departments = $deptCounts = [];
while ($row = $deptResult->fetch_assoc()) {
  $departments[] = $row['department_name'] ?? '_';
  $deptCounts[] = $row['count'];
}

//  Risks
$riskResult = runQuery($conn, "
  SELECT risk, COUNT(*) as count
  FROM daily_report $whereClause
  GROUP BY risk
", "Risk");

$risks = $riskCounts = [];
while ($row = $riskResult->fetch_assoc()) {
  $risks[] = $row['risk'];
  $riskCounts[] = $row['count'];
}
$totalRisks = array_sum($riskCounts);
$riskPercentages = $totalRisks > 0 ? array_map(function ($count) use ($totalRisks) {
  return round(($count / $totalRisks) * 100, 2);
}, $riskCounts) : [];

//  Dates
$dateResult = runQuery($conn, "
  SELECT date, COUNT(*) as count
  FROM daily_report $whereClause
  GROUP BY date
  ORDER BY date
", "Date");

$dates = $dateCounts = [];
while ($row = $dateResult->fetch_assoc()) {
  $dates[] = $row['date'];
  $dateCounts[] = $row['count'];
}

// ⏰ Overdue Reports 
$overdueCondition = "
(
    (dr.closed_at IS NULL AND TIMESTAMPDIFF(HOUR, dr.date, NOW()) >
        CASE
            WHEN dr.risk = 'عالية' THEN 8
            WHEN dr.risk = 'متوسطه' THEN 12
            WHEN dr.risk = 'منخفضة' THEN 24
        END
    )
    OR
    (dr.closed_at IS NOT NULL AND TIMESTAMPDIFF(HOUR, dr.date, dr.closed_at) >
        CASE
            WHEN dr.risk = 'عالية' THEN 8
            WHEN dr.risk = 'متوسطه' THEN 12
            WHEN dr.risk = 'منخفضة' THEN 24
        END
    )
)
";

$finalClause = $whereClause
  ? $whereClause . " AND $overdueCondition"
  : "WHERE $overdueCondition";

$overdueDetailResult = runQuery($conn, "
    SELECT 
        dr.id,
        dr.date,
        pr.project_name,
        d.department_name,
        dr.work_type,
        dr.risk,
        dr.observation_description,
        dr.description,
        dr.observation,
        dr.operation_corrective,
        dr.report_status,
        dr.closed_at,
        dr.image_upload,
        dr.user_id,
        dr.closure_notes,
        dr.closure_image,
        dr.closed_by,
        u.username,
        u2.username as closed_by_username
    FROM daily_report dr
    LEFT JOIN project pr ON dr.project = pr.id
    LEFT JOIN department d ON dr.department = d.id
    LEFT JOIN users u ON dr.user_id = u.id
    LEFT JOIN users u2 ON dr.closed_by = u2.id
    $finalClause
    ORDER BY dr.date DESC
", "Overdue Detail");

$overdueReports = [];
while ($row = $overdueDetailResult->fetch_assoc()) {
  $risk = $row['risk'];
  $submitted = strtotime($row['date']);
  $isClosed = !empty($row['closed_at']);
  $closed = $isClosed ? strtotime($row['closed_at']) : time();


  $delaySeconds = $closed - $submitted;
  $hoursDiff = round($delaySeconds / 3600);

  switch ($risk) {
    case 'عالية':
      $threshold = 8;
      break;
    case 'متوسطه':
      $threshold = 12;
      break;
    case 'منخفضة':
      $threshold = 24;
      break;
    default:
      $threshold = 48;
  }


  $status = 'Overdue';

  if ($risk === 'عالية') {
    $tag = '🔴 High';
  } elseif ($risk === 'متوسطه') {
    $tag = '🟡 Medium';
  } elseif ($risk === 'منخفضة') {
    $tag = '🟢 Low';
  } else {
    $tag = '🔵 Unknown';
  }
  $row['status'] = $status;
  $row['delay'] = $hoursDiff;
  $row['tag'] = $tag;
  $row['exceeded_by'] = max(0, $hoursDiff - $threshold);

  $overdueReports[] = $row;

}
$overdueCount = count($overdueReports);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daily Report Dashboard</title>
  <script src="js/chart.umd.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f8;
      margin: 0;
      padding: 20px;
    }

    h2 {
      text-align: center;
      color: #333;
      margin-top: 70px;
    }

    .dashboard-wrapper {
      display: flex;
      gap: 20px;
      align-items: flex-start;
    }

    .charts-area {
      flex: 1;
      min-width: 600px;
    }

    /*
    .chart-container {
       background: #fff;
        padding: 20px;
         border-radius: 10px;
          box-shadow: 0 0 10px rgba(0,0,0,0.05);
           margin-bottom: 30px;
           }
           */
    .chart-container {
      background: #fff;
      padding: 10px;
      border-radius: 8px;
      box-shadow: 0 0 6px rgba(0, 0, 0, 0.05);
      margin-bottom: 20px;
      max-width: 600px;
      margin: 0 auto;
      transform: scale(0.9);
    }



    .sidebar {
      width: 800px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
      padding: 20px;
      overflow-x: auto;
    }

    .toggle-button {
      display: block;
      margin: 0 auto 15px;
      padding: 10px 15px;
      background: #ffca2c;
      color: #333;
      border: none;
      font-weight: bold;
      border-radius: 5px;
      cursor: pointer;
    }

    .hidden {
      display: none;
    }

    .alert-box {
      background: #fff3cd;
      color: #856404;
      border-left: 5px solid #ffca2c;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
      font-size: 15px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      font-size: 12px;
      table-layout: auto;
    }

    th,
    td {
      border: 1px solid #ccc;
      padding: 6px 4px;
      text-align: center;
      word-wrap: break-word;
    }

    thead {
      background: #ffe792;
    }

    tbody tr:nth-child(even) {
      background: #f9f9f9;
    }

    .filter-form {
      text-align: center;
      margin: 20px auto;
    }

    .filter-form select {
      padding: 6px 10px;
      margin: 0 10px;
    }

    /* Expand/Collapse Button Styles */
    .expand-btn {
      background: #4CAF50;
      border: none;
      color: white;
      padding: 5px 10px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-size: 14px;
      margin: 2px 2px;
      cursor: pointer;
      border-radius: 3px;
      transition: background-color 0.3s;
    }
    
    .expand-btn:hover {
      background-color: #45a049;
    }
    
    .expand-btn.expanded {
      background: #f44336;
    }
    
    .expand-btn.expanded:hover {
      background-color: #da190b;
    }
    
    .details-row {
      display: none;
      background-color: #f9f9f9;
      border-top: 1px solid #ddd;
    }
    
    .details-content {
      padding: 20px;
      display: flex;
      gap: 20px;
      align-items: flex-start;
    }
    
    .details-images {
      flex: 0 0 300px;
      text-align: center;
    }
    
    .image-section {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
      margin-bottom: 15px;
    }

    .image-section img {
      max-width: 150px;
      max-height: 180px;
      border: 2px solid #ddd;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .image-section img:hover {
      transform: scale(1.8);
      box-shadow: 0 4px 10px rgba(0,0,0,0.4);
      z-index: 2;
    }
    
    .image-section h5 {
      margin-top: 0;
      margin-bottom: 10px;
      color: #333;
      font-weight: bold;
      font-size: 12px;
    }
    
    .details-notes {
      flex: 1;
      background: white;
      padding: 15px;
      border-radius: 5px;
      border: 1px solid #ddd;
    }
    
    .details-notes h4 {
      margin-top: 0;
      color: #333;
      border-bottom: 1px solid #eee;
      padding-bottom: 5px;
      font-size: 14px;
    }
    
    .note-item {
      margin-bottom: 15px;
      padding: 10px;
      background: #f8f8f8;
      border-left: 3px solid #4CAF50;
      border-radius: 3px;
    }
    
    .note-item.closure {
      border-left-color: #2196F3;
    }
    
    .note-label {
      font-weight: bold;
      color: #555;
      margin-bottom: 5px;
      font-size: 12px;
    }
    
    .note-content {
      white-space: pre-wrap;
      line-height: 1.6;
      color: #333;
      font-size: 12px;
    }
    
    .no-image {
      color: #999;
      font-style: italic;
      padding: 15px;
      text-align: center;
      background: #f5f5f5;
      border: 2px dashed #ddd;
      border-radius: 5px;
      font-size: 12px;
    }
    
    .email-btn {
      background: #2196F3;
      border: none;
      color: white;
      padding: 4px 8px;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      font-size: 11px;
      cursor: pointer;
      border-radius: 3px;
      transition: background-color 0.3s;
      white-space: nowrap;
    }
    
    .email-btn:hover {
      background-color: #0b7dda;
    }
    
    .email-btn:disabled {
      background-color: #9E9E9E;
      cursor: not-allowed;
    }

    /* ================================================
       MOBILE RESPONSIVE DESIGN - Media Queries
       ================================================ */
    
    /* Tablet and below (768px) */
    @media screen and (max-width: 768px) {
      body {
        padding: 10px;
      }

      h2 {
        font-size: 20px;
        margin-top: 60px;
      }

      .dashboard-wrapper {
        flex-direction: column;
        gap: 15px;
      }

      .charts-area {
        min-width: unset;
        width: 100%;
      }

      .chart-container {
        max-width: 100%;
        padding: 15px;
        transform: scale(1);
      }

      .sidebar {
        width: 100%;
        padding: 15px;
      }

      /* Make table scrollable horizontally on mobile */
      .sidebar {
        overflow-x: auto;
      }

      table {
        min-width: 700px; /* Ensure table doesn't get too compressed */
        font-size: 11px;
      }

      th, td {
        padding: 4px 3px;
        font-size: 10px;
      }

      .toggle-button {
        font-size: 14px;
        padding: 8px 12px;
      }

      .filter-form {
        margin: 15px auto;
      }

      .filter-form label {
        display: block;
        margin-bottom: 10px;
      }

      .filter-form select {
        width: 100%;
        max-width: 300px;
        padding: 8px;
        margin: 5px 0;
      }
    }

    /* Mobile phones (480px and below) */
    @media screen and (max-width: 480px) {
      body {
        padding: 5px;
      }

      h2 {
        font-size: 18px;
        margin-top: 50px;
        padding: 0 5px;
      }

      .chart-container {
        padding: 10px;
        margin-bottom: 15px;
      }

      .sidebar {
        padding: 10px;
      }

      .toggle-button {
        font-size: 12px;
        padding: 6px 10px;
      }

      .alert-box {
        font-size: 13px;
        padding: 10px;
      }

      table {
        font-size: 9px;
        min-width: 650px;
      }

      th, td {
        padding: 3px 2px;
        font-size: 9px;
      }

      .expand-btn {
        padding: 3px 6px;
        font-size: 12px;
      }

      .email-btn {
        padding: 3px 6px;
        font-size: 9px;
      }

      /* Make details view more mobile-friendly */
      .details-content {
        flex-direction: column;
        padding: 10px;
      }

      .details-images {
        flex: 1;
        width: 100%;
      }

      .image-section img {
        max-width: 120px;
        max-height: 150px;
      }

      .details-notes {
        padding: 10px;
      }

      .note-item {
        padding: 8px;
        margin-bottom: 10px;
      }

      .note-label {
        font-size: 11px;
      }

      .note-content {
        font-size: 11px;
      }

      .details-notes h4 {
        font-size: 13px;
      }

      .image-section h5 {
        font-size: 11px;
      }

      .no-image {
        font-size: 11px;
        padding: 10px;
      }

      .filter-form select {
        width: 100%;
        max-width: 100%;
      }
    }

    /* Extra small devices (360px and below) */
    @media screen and (max-width: 360px) {
      h2 {
        font-size: 16px;
      }

      .toggle-button {
        font-size: 11px;
        padding: 5px 8px;
        white-space: normal;
        line-height: 1.3;
      }

      table {
        min-width: 600px;
      }

      th, td {
        font-size: 8px;
        padding: 2px 1px;
      }

      .expand-btn,
      .email-btn {
        font-size: 8px;
        padding: 2px 4px;
      }

      .image-section img {
        max-width: 100px;
        max-height: 120px;
      }
    }
  
  </style>
</head>

<body>

  <h2>📊 Daily Report Analytics</h2>

  <form method="GET" class="filter-form">
    <label>Project:
      <select name="project" onchange="this.form.submit()">
        <option value="">All Projects</option>
        <?php
        $projects = $conn->query("SELECT id, project_name FROM project WHERE project_status = 1 ORDER BY project_name");
        while ($row = $projects->fetch_assoc()) {
          $sel = ($selectedProject == $row['id']) ? 'selected' : '';
          echo "<option value='{$row['id']}' $sel>" . htmlspecialchars($row['project_name']) . "</option>";
        }
        ?>
      </select>
    </label>

    <label>Department:
      <select name="department" onchange="this.form.submit()">
        <option value="">All Departments</option>
        <?php
        $departmentsOpt = $conn->query("SELECT id, department_name FROM department WHERE department_status = 1 ORDER BY department_name");
        while ($row = $departmentsOpt->fetch_assoc()) {
          $sel = ($selectedDepartment == $row['id']) ? 'selected' : '';
          echo "<option value='{$row['id']}' $sel>" . htmlspecialchars($row['department_name']) . "</option>";
        }
        ?>
      </select>
    </label>
  </form>

  <div class="dashboard-wrapper">
    <div class="charts-area">
      <div class="chart-container">
        <canvas id="reportsByDepartment"></canvas>
      </div>
      <div class="chart-container">
        <canvas id="risksByType"></canvas>
      </div>
      <div class="chart-container">
        <canvas id="reportsOverTime"></canvas>
      </div>
    </div>

    <?php $displayCount = isset($overdueCount) ? $overdueCount : 0; ?>

    <?php $displayCount = $overdueCount ?? 0; ?>

    <div class="sidebar">
      <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 15px;">
        <button class="toggle-button" style="margin: 0;" onclick="toggleSidebar()">⚠️ Overdue Reports (<?= $displayCount ?>)</button>
        <?php if (hasAccess('dailyreport_analysis.php', 'export_excel') || hasAccess('dailyreport_analysis.php', 'export') || hasAccess('dailyreport_analysis.php', 'view')): ?>
        <a href="export_overdue_excel.php?project=<?= urlencode($selectedProject) ?>&department=<?= urlencode($selectedDepartment) ?>" class="toggle-button" style="text-decoration: none; background: #28a745; color: white; margin: 0;">
           📥 Export to Excel
        </a>
        <?php endif; ?>
      </div>
      <div id="overdueContent">
        <?php if (!empty($overdueReports)): ?>
          <div class="alert-box">
            There are <strong><?= $displayCount ?></strong> report(s) overdue
          </div>

          <table>
            <thead>
              <tr>
                <th>Details</th>
                <th>ID</th>
                <th>Department</th>
                <th>Risk</th>
                <th>Reported</th>
                <th>Closed</th>
                <th>Delay (hrs)</th>
                <th>Overdue By</th>
                <th>Tag</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($overdueReports as $r): 
                $report_id = $r['id'];
                $closedBy = ($r['closed_by_username']) ? htmlspecialchars($r['closed_by_username']) : "—";
              ?>
                <tr id="row_<?= $report_id ?>" 
                    data-image="<?= htmlspecialchars($r['image_upload']) ?>"
                    data-closure-image="<?= htmlspecialchars($r['closure_image']) ?>"
                    data-closure-notes="<?= htmlspecialchars($r['closure_notes']) ?>">
                  <td>
                    <button class="expand-btn" onclick="toggleDetails(<?= $report_id ?>)" id="btn_<?= $report_id ?>">+</button>
                  </td>
                  <td><?= htmlspecialchars($r['id']) ?></td>
                  <td><?= htmlspecialchars($r['department_name'] ?? '—') ?></td>
                  <td><?= htmlspecialchars($r['risk']) ?></td>
                  <td><?= htmlspecialchars($r['date']) ?></td>
                  <td><?= $r['closed_at'] ? htmlspecialchars($r['closed_at']) : '❌ Open' ?></td>
                  <td><?= htmlspecialchars($r['delay']) ?></td>
                  <td><?= htmlspecialchars($r['exceeded_by']) ?> hrs late</td>
                  <td><?= htmlspecialchars($r['tag']) ?></td>
                  <td>
                    <?php if (hasAccess('dailyreport_analysis.php', 'send_email')): ?>
                    <button class="email-btn" onclick="sendOverdueEmail(<?= $report_id ?>)" 
                            data-report-id="<?= $report_id ?>"
                            id="email_btn_<?= $report_id ?>">
                      📧 Send Email
                    </button>
                    <?php endif; ?>
                  </td>
                </tr>
                
                <!-- Details Row -->
                <tr class="details-row" id="details_<?= $report_id ?>">
                  <td colspan="9">
                    <div class="details-content">
                      <div class="details-images">
                        <!-- Original Image -->
                        <div class="image-section">
                          <h5>📸 Original Observation Images</h5>
                          <?php
                          if (!empty($r['image_upload'])) {
                              $images = json_decode($r['image_upload'], true);
                              
                              if (is_array($images)) {
                                  foreach ($images as $imgPath) {
                                      $webPath = ltrim($imgPath, '/');
                                      echo '<img src="assests/uploads/' . htmlspecialchars($webPath) . '" alt="Observation Image" />';
                                  }
                              } else {
                                  $webPath = ltrim($r['image_upload'], '/');
                                  echo '<img src="assests/uploads/' . htmlspecialchars($webPath) . '" alt="Observation Image" />';
                              }
                          } else {
                              echo '<div class="no-image">No original images available</div>';
                          }
                          ?>
                        </div>
                        
                        <!-- Closure Images (only show if observation is closed) -->
                        <?php if ($r['report_status'] == 1): ?>
                          <div class="image-section">
                            <h5>✅ Closure Images</h5>
                            <?php if (!empty($r['closure_image'])): ?>
                              <?php
                              $closure_images = json_decode($r['closure_image'], true);
                              
                              if (is_array($closure_images)) {
                                  foreach ($closure_images as $imgPath) {
                                      $webPath = ltrim($imgPath, '/');
                                      echo '<img src="assests/uploads/closures/' . htmlspecialchars($webPath) . '" alt="Closure Image" />';
                                  }
                              } else {
                                  $closureWebPath = ltrim($r['closure_image'], '/');
                                  if (strpos($closureWebPath, 'assests/uploads/closures/') === 0) {
                                      echo '<img src="' . htmlspecialchars($closureWebPath) . '" alt="Closure Image" />';
                                  } else {
                                      echo '<img src="assests/uploads/closures/' . htmlspecialchars($closureWebPath) . '" alt="Closure Image" />';
                                  }
                              }
                              ?>
                            <?php else: ?>
                              <div class="no-image">No closure images available</div>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>
                      </div>
                      
                      <div class="details-notes">
                        <h4>📋 Full Report Details</h4>
                        
                        <div class="note-item">
                          <div class="note-label">📍 Project:</div>
                          <div class="note-content"><?= htmlspecialchars($r['project_name'] ?? 'N/A') ?></div>
                        </div>
                        
                        <div class="note-item">
                          <div class="note-label">🔧 Work Type:</div>
                          <div class="note-content"><?= htmlspecialchars($r['work_type']) ?></div>
                        </div>
                        
                        <div class="note-item">
                          <div class="note-label">👁 Observation Description:</div>
                          <div class="note-content"><?= htmlspecialchars($r['observation_description']) ?></div>
                        </div>
                        
                        <div class="note-item">
                          <div class="note-label">🔍 Complete Observation:</div>
                          <div class="note-content"><?= htmlspecialchars($r['description']) ?></div>
                        </div>
                        
                        <div class="note-item">
                          <div class="note-label">⚡ Corrective Action:</div>
                          <div class="note-content"><?= htmlspecialchars($r['operation_corrective']) ?></div>
                        </div>
                        
                        <div class="note-item">
                          <div class="note-label">👤 Created By:</div>
                          <div class="note-content"><?= htmlspecialchars($r['username'] ?? 'Legacy Report') ?></div>
                        </div>
                        
                        <div class="note-item">
                          <div class="note-label">👤 Closed By:</div>
                          <div class="note-content"><?= $closedBy ?></div>
                        </div>
                        
                        <!-- Closure Notes (only show if observation is closed) -->
                        <?php if ($r['report_status'] == 1 && !empty($r['closure_notes'])): ?>
                          <div class="note-item closure">
                            <div class="note-label">🔒 Closure Notes:</div>
                            <div class="note-content"><?= htmlspecialchars($r['closure_notes']) ?></div>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p style="color: green;">🎉 No overdue reports!</p>
        <?php endif; ?>
      </div>
    </div>
    <script>
      const departments = <?= json_encode($departments, JSON_HEX_TAG) ?>;
      const deptCounts = <?= json_encode($deptCounts, JSON_HEX_TAG) ?>;
      const risks = <?= json_encode($risks, JSON_HEX_TAG) ?>;
      const riskPercentages = <?= json_encode($riskPercentages, JSON_HEX_TAG) ?>;
      const dates = <?= json_encode($dates, JSON_HEX_TAG) ?>;
      const dateCounts = <?= json_encode($dateCounts, JSON_HEX_TAG) ?>;

      new Chart(document.getElementById("reportsByDepartment"), {
        type: 'bar',
        data: {
          labels: departments,
          datasets: [{
            label: 'Reports by Department',
            data: deptCounts,
            backgroundColor: '#4A90E2'
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } }
        }
      });

      new Chart(document.getElementById("risksByType"), {
        type: 'pie',
        data: {
          labels: risks.map((r, i) => `${r} (${riskPercentages[i]}%)`),
          datasets: [{
            label: 'Risk Distribution (%)',
            data: riskPercentages,
            backgroundColor: ['#c92145', '#FFCE56', '#4BC0C0', '#9966FF', '#FFA07A']
          }]
        },
        options: { responsive: true }
      });

      new Chart(document.getElementById("reportsOverTime"), {
        type: 'line',
        data: {
          labels: dates,
          datasets: [{
            label: 'Reports Over Time',
            data: dateCounts,
            borderColor: '#36A2EB',
            backgroundColor: 'rgba(54,162,235,0.2)',
            fill: true,
            tension: 0.3
          }]
        },
        options: {
          responsive: true,
          scales: {
            x: {
              ticks: {
                maxRotation: 90,
                minRotation: 45
              }
            },
            y: {
              beginAtZero: true
            }
          }
        }
      });
    </script>
    <script>
      const riskCounts = {
        عالية: 0,
        متوسطه: 0,
        منخفضة: 0
      };
      <?php foreach ($overdueReports as $r): ?>
        riskCounts["<?= $r['risk'] ?>"] = (riskCounts["<?= $r['risk'] ?>"] || 0) + 1;
      <?php endforeach; ?>
    </script>
    <script>
      function toggleSidebar() {
        const content = document.getElementById("overdueContent");
        const button = document.querySelector(".toggle-button");

        const isHidden = content.classList.toggle("hidden");

        const high = riskCounts["عالية"] || 0;
        const medium = riskCounts["متوسطه"] || 0;
        const low = riskCounts["منخفضة"] || 0;

        button.textContent = isHidden
          ? "⚠️ Show Overdue Reports"
          : `⚠️ Hide Overdue Reports — 🔴 ${high} | 🟡 ${medium} | 🟢 ${low}`;
      }

      // Toggle details functionality for overdue reports
      function toggleDetails(reportId) {
        const detailsRow = document.getElementById('details_' + reportId);
        const button = document.getElementById('btn_' + reportId);
        
        if (detailsRow.style.display === 'none' || detailsRow.style.display === '') {
          detailsRow.style.display = 'table-row';
          button.textContent = '−';
          button.classList.add('expanded');
        } else {
          detailsRow.style.display = 'none';
          button.textContent = '+';
          button.classList.remove('expanded');
        }
      }
      
      // Send overdue email notification
      function sendOverdueEmail(reportId) {
        const button = document.getElementById('email_btn_' + reportId);
        button.disabled = true;
        button.textContent = '⏳ Sending...';
        
        fetch('submit_button/send_overdue_email.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'report_id=' + reportId
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            button.textContent = '✅ Sent!';
            button.style.backgroundColor = '#4CAF50';
            setTimeout(() => {
              button.textContent = '📧 Send Email';
              button.style.backgroundColor = '';
              button.disabled = false;
            }, 3000);
          } else {
            alert('Error: ' + (data.message || 'Failed to send email'));
            button.textContent = '❌ Failed';
            button.style.backgroundColor = '#f44336';
            setTimeout(() => {
              button.textContent = '📧 Send Email';
              button.style.backgroundColor = '';
              button.disabled = false;
            }, 3000);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Network error. Please try again.');
          button.textContent = '📧 Send Email';
          button.disabled = false;
        });
      }
    </script>
</body>

</html>