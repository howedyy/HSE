<?php
// Check authentication first
require_once "constants/auth_check.php";

require_once "constants/auth.php";
require_once "constants/dbconnect.php";
require_once "include/header.php";

date_default_timezone_set('Africa/Cairo');

// Filter logic
$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';
$operation = $_GET['operation'] ?? '';
$entries = $_GET['entries'] ?? 'all';
$highlight = $_GET['highlight'] ?? '';
$permitNumber = $_GET['permitNumber'] ?? '';
$selectedProject = $_GET['project'] ?? '';
$selectedDepartment = $_GET['department'] ?? '';

$filterConditions = [];

if ($startDate !== '') {
    $filterConditions[] = "p.permit_date >= '" . $conn->real_escape_string($startDate) . "'";
}
if ($endDate !== '') {
    $filterConditions[] = "p.permit_date <= '" . $conn->real_escape_string($endDate) . "'";
}
if ($operation !== '') {
    $filterConditions[] = "p.operation_type = '" . $conn->real_escape_string($operation) . "'";
}
if ($permitNumber !== '') {
    // Handle PTW numbers with or without underscores and leading zeros
    $cleanPermitNumber = $permitNumber;
    
    // Normalize the permit number format by:
    // 1. Remove any spaces
    $cleanPermitNumber = str_replace(' ', '', $cleanPermitNumber);
    
    // 2. Check if it's already in format PTW_XXX or PTWXXX
    if (preg_match('/^PTW_?(\d+)$/', $cleanPermitNumber, $matches)) {
        // Extract the numeric part
        $numericPart = $matches[1];
        
        // Generate permit numbers with different formats
        $number = intval($numericPart); // Convert to integer to remove leading zeros
        $format1 = 'PTW_' . $numericPart; // Original format with underscore
        $format2 = 'PTW' . $numericPart;  // No underscore
        $format3 = 'PTW_' . sprintf('%03d', $number); // With padding to 3 digits
        $format4 = 'PTW' . sprintf('%03d', $number);  // No underscore with padding
        
        // Build a query that matches all possible formats
        $filterConditions[] = "(p.permit_number = '" . $conn->real_escape_string($format1) . "' OR " .
                             "p.permit_number = '" . $conn->real_escape_string($format2) . "' OR " .
                             "p.permit_number = '" . $conn->real_escape_string($format3) . "' OR " .
                             "p.permit_number = '" . $conn->real_escape_string($format4) . "' OR " .
                             "p.permit_number LIKE '%" . $conn->real_escape_string($numericPart) . "%')";
    } else {
        // For other searches, use LIKE
        $filterConditions[] = "p.permit_number LIKE '%" . $conn->real_escape_string($cleanPermitNumber) . "%'";
    }
}
if ($selectedProject !== '') {
    // From the table structure, we know there's only project_name field
    // Get the actual project name from project table using the ID
    $projectIdQuery = "SELECT project_name FROM project WHERE id = " . intval($selectedProject);
    $projectResult = $conn->query($projectIdQuery);
    
    if ($projectResult && $projectResult->num_rows > 0) {
        // If we found a project name, use that for filtering
        $projectRow = $projectResult->fetch_assoc();
        $projectName = $projectRow['project_name'];
        $filterConditions[] = "p.project_name = '" . $conn->real_escape_string($projectName) . "'";
    } else {
        // Fallback - try searching across all projects for any that match the filter
        $filterConditions[] = "(p.project_name LIKE '%" . $conn->real_escape_string($selectedProject) . "%' OR 
                               p.project_name LIKE '%project " . intval($selectedProject) . "%' OR
                               p.project_name LIKE '%Project " . intval($selectedProject) . "%')";
    }
}
if ($selectedDepartment !== '') {
    $filterConditions[] = "p.department = " . intval($selectedDepartment);
}

$whereClause = count($filterConditions) ? 'WHERE ' . implode(' AND ', $filterConditions) : '';

// Use uppercase PTW table name to match actual database table
$sql = "
    SELECT p.*, d.department_name
    FROM PTW p
    LEFT JOIN department d ON p.department = d.id
    $whereClause
    ORDER BY p.id DESC
";

// Check if PTW table exists
$tablesQuery = "SHOW TABLES LIKE 'PTW'";
$tablesResult = $conn->query($tablesQuery);
$tableExists = ($tablesResult && $tablesResult->num_rows > 0);

// Execute the actual query
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PTW Overview</title>
  <link rel="stylesheet" href="custom/css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    
    /* Button styles */
    .btn-action {
      padding: 4px 8px !important; /* Consistent padding */
      font-size: 12px !important; /* Consistent font size */
      min-width: 80px !important; /* Fixed minimum width for alignment */
      height: 32px !important; /* Fixed height for uniformity */
      line-height: 1.2 !important;
      margin: 2px !important;
      border-radius: 6px !important; /* Softer corners */
      display: inline-block !important;
      text-decoration: none !important;
      vertical-align: middle !important;
      transition: all 0.3s ease !important;
      border: none !important; /* Remove default borders */
      cursor: pointer !important;
      font-weight: 500 !important; /* Slightly bold text */
      text-align: center !important;
    }
    .action-icon {
      font-size: 12px !important;
      margin-right: 4px !important;
    }
    .action-buttons-container {
      display: flex !important;
      flex-wrap: wrap !important;
      gap: 6px !important;
      justify-content: center !important;
      align-items: center !important;
    }

    /* Unique and appealing colors for action buttons */
    .btn-edit {
      background: linear-gradient(135deg, #4CAF50, #66BB6A) !important; /* Green gradient */
      color: white !important;
    }
    .btn-edit:hover {
      background: linear-gradient(135deg, #45a049, #58a65e) !important;
      transform: translateY(-2px) !important;
    }

    .btn-finish {
      background: linear-gradient(135deg, #2196F3, #42A5F5) !important; /* Blue gradient */
      color: white !important;
    }
    .btn-finish:hover {
      background: linear-gradient(135deg, #1976D2, #2e8de5) !important;
      transform: translateY(-2px) !important;
    }

    .btn-warning {
      background: linear-gradient(135deg, #F4511E, #FF7043) !important; /* Orange gradient */
      color: white !important;
    }
    .btn-warning:hover {
      background: linear-gradient(135deg, #d83b0e, #e05d2e) !important;
      transform: translateY(-2px) !important;
    }

    .btn-pdf {
      background: linear-gradient(135deg, #E53935, #EF5350) !important; /* Red gradient */
      color: white !important;
    }
    .btn-pdf:hover {
      background: linear-gradient(135deg, #C62828, #d32f2f) !important;
      transform: translateY(-2px) !important;
    }

    .expand-btn {
      background: linear-gradient(135deg, #4CAF50, #66BB6A) !important; /* Green for expand */
      color: white !important;
      cursor: pointer !important;
    }
    .expand-btn:hover {
      background: linear-gradient(135deg, #45a049, #58a65e) !important;
    }
    .expand-btn.expanded {
      background: linear-gradient(135deg, #F4511E, #FF7043) !important; /* Red for collapse */
    }
    .expand-btn.expanded:hover {
      background: linear-gradient(135deg, #d83b0e, #e05d2e) !important;
    }

    /* Header styles */
    .ptw-header {
      background: linear-gradient(135deg, #1976D2, #2196F3);
      color: white;
      padding: 20px;
      margin-top: 80px;
      margin-bottom: 30px;
      margin-right : 50px;
      border-radius: 10px;
      box-shadow: 0 6px 12px rgba(0,0,0,0.2);
      text-align: center;
      font-size: 28px;
      font-weight: bold;
      position: relative;
      overflow: hidden;
      width: 102%;
    }
    .ptw-header span {
      display: inline-block;
      background: rgba(255,255,255,0.3);
      padding: 5px 15px;
      border-radius: 25px;
      font-size: 20px;
      margin-left: 15px;
      vertical-align: middle;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }

    /* Enhanced filter form */
    .filter-section-ptw {
      background:#99999f;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      margin-bottom: 30px;
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: center;
      justify-content: center;
    }
    .date-input, select {
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 14px;
      background: white;
      transition: border-color 0.3s ease;
    }
    .date-input:focus, select:focus {
      border-color: #1976D2;
      outline: none;
    }
    .button-row {
      display: flex;
      gap: 10px;
    }
    .small-button, .tiny-button {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.3s ease;
    }
    .small-button {
      background: #1976D2;
      color: white;
    }
    .small-button:hover {
      background: #1565C0;
    }
    .tiny-button {
      background: #757575;
      color: white;
    }
    .tiny-button:hover {
      background: #616161;
    }

    /* Table styles */
    .styled-table_1 {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    .styled-table_1 th, .styled-table_1 td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    .styled-table_1 th {
      background-color: #1976D2;
      color: white;
    }
    .styled-table_1 tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    .styled-table_1 tr:hover {
      background-color: #f1f1f1;
    }
    .highlight-row {
      background-color: #ffeb3b !important;
      animation: highlight-fade 6s forwards;
    }
    @keyframes highlight-fade {
      0% { background-color: #ffeb3b; }
      70% { background-color: #ffeb3b; }
      100% { background-color: inherit; }
    }

    /* Details row styles */
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
    .details-notes {
      flex: 1;
      background: white;
      padding: 15px;
      border-radius: 5px;
      border: 1px solid #ddd;
    }
    .details-images {
      flex: 0 0 400px;
      background: white;
      padding: 15px;
      border-radius: 5px;
      border: 1px solid #ddd;
      text-align: center;
    }
    .image-section {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
    }
    .image-section img {
      max-width: 150px;
      max-height: 150px;
      border: 2px solid #ddd;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
    }
    .image-section img:hover {
      transform: scale(2.5);
      box-shadow: 0 4px 10px rgba(0,0,0,0.4);
    }
    .no-images {
      color: #999;
      font-style: italic;
      padding: 20px;
      text-align: center;
      background: #f5f5f5;
      border: 2px dashed #ddd;
      border-radius: 5px;
    }
    .details-notes h4 {
      margin-top: 0;
      color: #333;
      border-bottom: 1px solid #eee;
      padding-bottom: 5px;
    }
    .note-item {
      margin-bottom: 15px;
      padding: 10px;
      background: #f8f8f8;
      border-left: 3px solid #4CAF50;
      border-radius: 3px;
    }
    .note-label {
      font-weight: bold;
      color: #555;
      margin-bottom: 5px;
    }
    .note-content {
      white-space: pre-wrap;
      line-height: 1.6;
      color: #333;
    }
    </style>
</head>

<body>
<div class="container">
  <div class="ptw-header">
    Permit To Work Overview <span>Safety First</span>
  </div>

  <form method="GET" id="filterForm" class="filter-section-ptw">
    <input type="text" name="permitNumber" class="date-input" value="<?= htmlspecialchars($permitNumber) ?>" placeholder="Permit Number">
    
    <select name="project" id="projectFilter" class="date-input">
      <option value="">All Projects</option>
      <?php
        $projects = $conn->query("SELECT project_name FROM project WHERE project_status = 1 ORDER BY project_name");
        while ($proj = $projects->fetch_assoc()) {
          $selected = ($selectedProject == $proj['project_name']) ? 'selected' : '';
          echo "<option value='" . htmlspecialchars($proj['project_name']) . "' $selected>" . htmlspecialchars($proj['project_name']) . "</option>";
        }
      
      ?>
    </select>
    
    <select name="department" id="departmentFilter" class="date-input">
      <option value="">All Departments</option>
      <?php
      $departments = $conn->query("SELECT id, department_name FROM department WHERE department_status = 1 ORDER BY department_name");
      while ($dept = $departments->fetch_assoc()) {
        $selected = ($selectedDepartment == $dept['id']) ? 'selected' : '';
        echo "<option value='{$dept['id']}'" . $selected . ">" . htmlspecialchars($dept['department_name']) . "</option>";
      }
      ?>
    </select>
    
    <input type="date" name="startDate" class="date-input" value="<?= htmlspecialchars($startDate) ?>" placeholder="Start Date">
    <input type="date" name="endDate" class="date-input" value="<?= htmlspecialchars($endDate) ?>" placeholder="End Date">
    <select name="operation" id="operationFilter" class="date-input">
      <option value="">All Operations</option>
      <?php
      $operations = [
        "أعمال حفر", "أعمال لحام كهربي", "العمل على ارتفاع سبايدر", "أعمال رفع أحمال بمعدات ثقيلة",
        "العمل علي سقالة", "العمل على السلم المفصلى", "أعمال نقل بمعدات ثقيلة",
        "العمل بداخل الغرف المغلقة", "العمل على السلم الهيدروليكي", "إعمال كهرباء الجهد المتوسط", "العمل بالمواد الخطرة"
      ];
      foreach ($operations as $op) {
        $selected = ($operation === $op) ? 'selected' : '';
        echo "<option value=\"" . htmlspecialchars($op) . "\" $selected>$op</option>";
      }
      ?>
    </select>
    <select name="entries" id="entriesFilter" class="date-input">
      <option value="all" <?= $entries == 'all' ? 'selected' : '' ?>>Show All</option>
      <option value="5" <?= $entries == '5' ? 'selected' : '' ?>>5</option>
      <option value="10" <?= $entries == '10' ? 'selected' : '' ?>>10</option>
      <option value="20" <?= $entries == '20' ? 'selected' : '' ?>>20</option>
      <option value="30" <?= $entries == '30' ? 'selected' : '' ?>>30</option>
    </select>
    <div class="button-row">
      <button type="submit" class="small-button">Apply</button>
      <button type="button" onclick="resetFilters()" class="tiny-button">Reset</button>
      <!--<button type="button" onclick="showAllRecords()" class="small-button" style="background: #e74c3c;">Show All Records</button>-->
    </div>
    
    <script>
    function showAllRecords() {
      // Clear all filters and submit the form
      var filterForm = document.getElementById('filterForm');
      var inputs = filterForm.querySelectorAll('input, select');
      for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].name !== 'entries') { // Keep entries setting
          if (inputs[i].tagName.toLowerCase() === 'select') {
            inputs[i].selectedIndex = 0;
          } else {
            inputs[i].value = '';
          }
        }
      }
      filterForm.submit();
    }
    </script>
  </form>

  <div class="data-container">
    <table class="styled-table_1">
      <thead>
        <tr>
          <th>History</th>
          <th>Permit Number</th>
          <th>Department</th>
          <th>Project Name</th>
          <th>Work Location</th>
          <th>Permit Date</th>
          <th>Operation Type</th>
          <th>Safety Measures</th>
          <th>Safety Manager</th>
          <th>Safety Signature</th>
          <th>Admin Signature</th>
          <th>PTW Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $rowCount = 0;
      $maxRows = ($entries === 'all') ? PHP_INT_MAX : intval($entries);

      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          if (++$rowCount > $maxRows) break;

          $permit = htmlspecialchars($row['permit_number']);
          $status = (int)$row['ptw_status'];
          switch ($status) {
            case 0:
              $statusText = "⏳ Not Approved";
              break;
            case 1:
              $statusText = "✅ Approved";
              break;
            case 2:
              $statusText = "✔ Finished";
              break;
            case 3:
              $statusText = "❌ Not Completed";
              break;
            case 4:
              $statusText = "⚠️ Non Compliance";
              break;
          }

          $editDisabled = ($status !== 0) ? "style='pointer-events: none; opacity: 0.6;'" : "";
          $finishDisabled = ($status !== 1) ? "style='pointer-events: none; opacity: 0.6;'" : "";
          $nonComplianceDisabled = ($status !== 1) ? "style='pointer-events: none; opacity: 0.6;'" : "";

          $highlightClass = ($highlight === $permit) ? 'highlight-row' : '';
          echo "<tr class='$highlightClass'>";
          echo "<td>";
          echo "<button class='btn-action expand-btn' id='btn_{$permit}' onclick='toggleDetails(\"{$permit}\")'>+</button>";
          echo "</td>";
          echo "<td>{$permit}</td>";
          echo "<td>" . htmlspecialchars($row['department_name'] ?? '—') . "</td>";
          echo "<td>{$row['project_name']}</td>";
          echo "<td>{$row['work_location']}</td>";
          echo "<td>{$row['permit_date']}</td>";
          echo "<td>{$row['operation_type']}</td>";
          echo "<td>{$row['safety_measures']}</td>";
          echo "<td>{$row['safety_manager']}</td>";
          echo "<td>{$row['safety_signature']}</td>";
          echo "<td>{$row['admin_signature_3']}</td>";
          echo "<td>$statusText</td>";
          echo "<td>";
          echo "<div class='action-buttons-container'>";

          if (hasAccess('ptw_overview.php', 'export')) {
            echo "<a href='export_ptw_pdf.php?permit_number={$permit}' class='btn-action btn-pdf' target='_blank'>
                    <i class='action-icon'>📄</i> PDF</a>";
          }

          if (hasAccess('ptw_overview.php', 'edit')) {
            echo "<a href='edit_ptw.php?permit_number={$permit}' class='btn-action btn-edit' 
                   id='approve_{$permit}' onclick='disableLink(\"approve_{$permit}\");' {$editDisabled}><i class='action-icon'>✓</i> Approve</a>";
          }

          if (hasAccess('ptw_overview.php', 'finish')) {
            echo "<a href='finish_ptw.php?permit_number={$permit}&action=finish' class='btn-action btn-finish' 
                   id='finish_{$permit}' onclick='disableLink(\"finish_{$permit}\");' {$finishDisabled}><i class='action-icon'>✅</i> Finish</a>";
          }
          
          if (hasAccess('ptw_overview.php', 'edit')) {
            echo "<a href='noncompliance_ptw.php?permit_number={$permit}&action=noncompliance' class='btn-action btn-warning' 
                   id='noncompliance_{$permit}' onclick='disableLink(\"noncompliance_{$permit}\");' {$nonComplianceDisabled}><i class='action-icon'>⚠️</i> Non Compliance</a>";
          }
          echo "</div>";
          echo "</td>";
          echo "</tr>";
          echo "<tr class='details-row' id='details_{$permit}'>";
          echo "<td colspan='13'>";
          echo "<div class='details-content'>";
          echo "<div class='details-notes'>";
          echo "<h4>📋 PTW History</h4>";
          echo "<div class='note-item' id='history_content_{$permit}'>";
          echo "<p>Loading history...</p>";
          echo "</div>";
          echo "</div>";
          echo "<div class='details-images'>";
          echo "<h4>📷 Non-Compliance Images</h4>";
          echo "<div class='image-section' id='images_content_{$permit}'>";
          echo "<p>Loading images...</p>";
          echo "</div>";
          echo "</div>";
          echo "</div>";
          echo "</td>";
          echo "</tr>";
        }
      } else {
        echo "<tr><td colspan='13'>No data available</td></tr>";
      }
      ?>
    </tbody>
  </table>
  </div>
</div>

<script>
function disableLink(id) {
  const link = document.getElementById(id);
  if (link) {
    link.style.pointerEvents = "none";
    link.style.opacity = "0.6";
  }
}

function resetFilters() {
  window.location.href = window.location.pathname;
}

// Scroll to highlighted row on page load
document.addEventListener('DOMContentLoaded', function() {
  const highlightedRow = document.querySelector('.highlight-row');
  if (highlightedRow) {
    setTimeout(function() {
      highlightedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 300); // Small delay to ensure page is fully loaded
  }
});

function toggleDetails(permit) {
  const detailsRow = document.getElementById('details_' + permit);
  const button = document.getElementById('btn_' + permit);
  
  if (detailsRow.style.display === 'none' || detailsRow.style.display === '') {
    // Fetch history data if not already loaded
    if (!detailsRow.dataset.loaded) {
      // Load PTW history
      $.ajax({
        url: 'get_ptw_history.php',
        type: 'GET',
        data: { permit_number: permit },
        success: function(response) {
          const content = document.getElementById('history_content_' + permit);
          content.innerHTML = response;
        },
        error: function() {
          const content = document.getElementById('history_content_' + permit);
          content.innerHTML = '<p>Error loading history.</p>';
        }
      });
      
      // Load PTW images
      $.ajax({
        url: 'get_ptw_images.php',
        type: 'GET',
        data: { permit_number: permit },
        success: function(response) {
          const imagesContent = document.getElementById('images_content_' + permit);
          imagesContent.innerHTML = response;
        },
        error: function() {
          const imagesContent = document.getElementById('images_content_' + permit);
          imagesContent.innerHTML = '<div class="no-images">Error loading images.</div>';
        }
      });
      
      detailsRow.dataset.loaded = 'true';
    }
    detailsRow.style.display = 'table-row';
    button.textContent = '−';
    button.classList.add('expanded');
  } else {
    detailsRow.style.display = 'none';
    button.textContent = '+';
    button.classList.remove('expanded');
  }
}
</script>
</body>
</html>
<?php $conn->close(); ?>