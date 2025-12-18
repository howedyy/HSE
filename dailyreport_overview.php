<?php
require_once "constants/dbconnect.php";
require_once "include/header.php";
require_once "constants/auth.php";
date_default_timezone_set('Africa/Cairo');

// Handle filters
$selectedProject = $_GET['project'] ?? '';
$selectedDepartment = $_GET['department'] ?? '';
$selectedCreatedBy = $_GET['created_by'] ?? '';
$selectedRisk = $_GET['risk'] ?? '';
$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';
$entries = $_GET['entries'] ?? '10';
$highlightReportId = $_GET['highlight'] ?? ''; // Capture highlight parameter

// Dynamic pagination settings based on entries selection
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

if ($entries == 'all') {
    $recordsPerPage = 30; // Show 30 records per page when "Show All" is selected
} else {
    $recordsPerPage = intval($entries);
}

$offset = ($currentPage - 1) * $recordsPerPage;

// Build filter conditions
$filterConditions = [];

if ($selectedProject !== '') {
    $filterConditions[] = "dr.project = " . intval($selectedProject);
}
if ($selectedDepartment !== '') {
    $filterConditions[] = "dr.department = " . intval($selectedDepartment);
}
if ($selectedCreatedBy !== '') {
    $filterConditions[] = "dr.user_id = " . intval($selectedCreatedBy);
}
if ($selectedRisk !== '') {
    $filterConditions[] = "dr.risk = '" . $conn->real_escape_string($selectedRisk) . "'";
}
if ($startDate !== '') {
    $filterConditions[] = "dr.date >= '" . $conn->real_escape_string($startDate) . " 00:00:00'";
}
if ($endDate !== '') {
    $filterConditions[] = "dr.date <= '" . $conn->real_escape_string($endDate) . " 23:59:59'";
}

$whereClause = count($filterConditions) > 0 ? 'WHERE ' . implode(' AND ', $filterConditions) : '';

// First, count total records for pagination
$countSql = "
  SELECT COUNT(*) as total
  FROM daily_report dr
  LEFT JOIN project pr ON dr.project = pr.id
  LEFT JOIN department dp ON dr.department = dp.id
  LEFT JOIN users u ON dr.user_id = u.id
  LEFT JOIN users u2 ON dr.closed_by = u2.id
  $whereClause
";

$countResult = $conn->query($countSql);
if (!$countResult) {
    die("Count query failed: " . $conn->error);
}
$totalRecords = $countResult->fetch_assoc()['total'];

// Calculate pagination for all cases
$totalPages = ceil($totalRecords / $recordsPerPage);
$limitClause = "LIMIT $recordsPerPage OFFSET $offset";

// Main query with conditional pagination
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
    dr.image_upload,
    dr.user_id,
    dr.closure_notes,
    dr.closure_image,
    dr.closed_by,
    u.username,
    u2.username as closed_by_username
  FROM daily_report dr
  LEFT JOIN project pr ON dr.project = pr.id
  LEFT JOIN department dp ON dr.department = dp.id
  LEFT JOIN users u ON dr.user_id = u.id
  LEFT JOIN users u2 ON dr.closed_by = u2.id
  $whereClause
  ORDER BY dr.date DESC
  $limitClause
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
  <link rel="stylesheet" href="custom/css/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="assests/jquery/jquery-3.6.0.min.js"></script>
  <style>
      .ptw-header {
        background: linear-gradient(135deg, #1976D2, #2196F3);
        color: white;
        padding: 15px;
        margin-top: 25px;
        margin-bottom: 25px;
        margin-left: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        inline-size: 95.6%;
      }
      
      .ptw-header span {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 18px;
        margin-left: 10px;
        vertical-align: middle;
      }
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
      flex: 0 0 400px;
      text-align: center;
    }
    
    .image-section {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
}

.image-section img {
  max-width: 200px;
  max-height: 250px;
  border: 2px solid #ddd;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.image-section img:hover {
  transform: scale(2.1);
  box-shadow: 0 4px 10px rgba(0,0,0,0.4);
  z-index: 2;
}
 @media (max-width: 600px) {
  .image-section {
    gap: 8px;
    padding: 5px;
  }

  .image-section img {
    max-width: 100%;
    max-height: 180px;
    border-width: 1px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
  }

  .image-section img:hover {
    transform: scale(1.05);
    box-shadow: 0 3px 6px rgba(0,0,0,0.3);
  }
}
   
    .image-section h5 {
      margin-top: 0;
      margin-bottom: 10px;
      color: #333;
      font-weight: bold;
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
    }
    
    .note-content {
      white-space: pre-wrap;
      line-height: 1.6;
      color: #333;
    }
    
    .no-image {
      color: #999;
      font-style: italic;
      padding: 20px;
      text-align: center;
      background: #f5f5f5;
      border: 2px dashed #ddd;
      border-radius: 5px;
    }

    /* Close Modal Styles */
    .close-modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }
    
    .close-modal-content {
      background-color: #fefefe;
      margin: 5% auto;
      padding: 20px;
      border: none;
      border-radius: 10px;
      width: 90%;
      max-width: 600px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    
    .close-modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #eee;
    }
    
    .close-modal-header h3 {
      margin: 0;
      color: #333;
    }
    
    .close-btn-x {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #999;
      padding: 0;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .close-btn-x:hover {
      color: #333;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #555;
    }
    
    .form-group textarea {
      width: 100%;
      min-height: 100px;
      padding: 10px;
      border: 2px solid #ddd;
      border-radius: 5px;
      resize: vertical;
      font-family: Arial, sans-serif;
      font-size: 14px;
    }
    
    .form-group textarea:focus {
      border-color: #4CAF50;
      outline: none;
    }
    
    .form-group input[type="file"] {
      width: 100%;
      padding: 8px;
      border: 2px solid #ddd;
      border-radius: 5px;
      background-color: #f9f9f9;
    }
    
    .modal-buttons {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin-top: 20px;
    }
    
    .modal-btn {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      transition: background-color 0.3s;
    }
    
    .modal-btn-cancel {
      background-color: #6c757d;
      color: white;
    }
    
    .modal-btn-cancel:hover {
      background-color: #5a6268;
    }
    
    .modal-btn-submit {
      background-color: #28a745;
      color: white;
    }
    
    .modal-btn-submit:hover {
      background-color: #218838;
    }
    
    .loading {
      opacity: 0.6;
      pointer-events: none;
    }

    /* Table styling - separated from pagination */
    .styled-table_1 {
      border-radius: 8px !important;
      margin-bottom: 0 !important;
      display: table !important;
      width: 100% !important;
      clear: both !important;
      float: none !important;
      position: relative !important;
      z-index: 1 !important;
    }
    
    /* Force pagination to new line after table */
    .styled-table_1 + div,
    .styled-table_1 + * {
      clear: both !important;
      display: block !important;
    }
    
    /* Table styling when showing all entries (no pagination) */
    .show-all .styled-table_1 {
      border-radius: 8px !important;
      margin-bottom: 30px !important;
    }
    
    /* Modern Table Pagination Styles - Matching Filter Form */
    .table-pagination {
      background: #99999f !important;
      border-radius: 8px !important;
      padding: 20px !important;
      margin: 20px auto 0 auto !important;
      width: 95% !important;
      max-width: 1200px !important;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
      display: flex !important;
      flex-wrap: wrap !important;
      gap: 15px !important;
      justify-content: center !important;
      align-items: center !important;
      position: static !important;
      visibility: visible !important;
      opacity: 1 !important;
      height: auto !important;
      clear: both !important;
      float: none !important;
      box-sizing: border-box !important;
    }
    
    /* Override any potential flex/grid issues */
    .data-container {
      display: block !important;
      flex-direction: column !important;
    }
    
    .data-container .table-pagination {
      order: 999 !important;
      flex: none !important;
    }
    
    /* Force line break before pagination */
    .table-pagination::before {
      content: "" !important;
      display: block !important;
      width: 100% !important;
      height: 0 !important;
      clear: both !important;
    }
    
    .table-pagination.simple {
      background: #27ae60;
      justify-content: center;
    }
    
    .pagination-info {
      display: flex !important;
      flex-direction: column !important;
      gap: 8px !important;
      align-items: center !important;
      //background: white !important;
      padding: 12px 20px !important;
      border-radius: 6px !important;
      //border: 2px solid #007bff !important;
      //box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
      min-width: 200px !important;
    }
    
    .page-indicator {
      display: inline-flex !important;
      align-items: center;
      justify-content: center !important;
      gap: 8px;
      color: #333;
      font-size: 16px;
      font-weight: 600;
    }
    
    .page-text {
      color: #666;
      font-size: 14px;
    }
    
    .page-current {
      background: #007bff;
      color: white;
      padding: 4px 10px;
      border-radius: 4px;
      font-weight: bold;
      min-width: 30px;
      text-align: center;
      font-size: 14px;
    }
    
    .page-divider {
      color: #666;
      font-size: 14px;
    }
    
    .page-total {
      color: #333;
      font-weight: 600;
      font-size: 14px;
    }
    
    .records-info {
      color: #666;
      font-size: 12px;
      text-align: center !important;
      display: block !important;
    }
    
    .records-info strong {
      color: #333;
      font-weight: 600;
    }
    
    .pagination-nav {
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 10px !important;
      flex-wrap: wrap !important;
    }
    
    .nav-btn {
      display: inline-flex !important;
      align-items: center;
      justify-content: center;
      padding: 8px 12px !important;
      background: #0b2eda !important;
      border: 1px solid #ccc !important;
      border-radius: 4px !important;
      color: white !important;
      text-decoration: none !important;
      font-size: 14px !important;
      font-weight: 500 !important;
      min-width: 40px !important;
      height: auto !important;
      transition: background-color 0.3s ease !important;
      cursor: pointer !important;
    }
    
    .nav-btn:hover {
      background: #0e0258 !important;
      color: white !important;
      text-decoration: none !important;
      transform: none !important;
      box-shadow: none !important;
    }
    
    .nav-btn.first-btn,
    .nav-btn.last-btn {
      background: #007bff !important;
      border-color: #007bff !important;
    }
    
    .nav-btn.first-btn:hover,
    .nav-btn.last-btn:hover {
      background: #0056b3 !important;
    }
    
    .nav-icon {
      font-size: 16px;
      font-style: normal;
    }
    
    .page-numbers {
      display: inline-flex !important;
      align-items: center;
      gap: 6px;
      margin: 0 10px;
    }
    
    .page-num {
      display: inline-flex !important;
      align-items: center;
      justify-content: center;
      min-width: 32px;
      height: 32px;
      padding: 6px 8px !important;
      background: white !important;
      border: 2px solid #007bff !important;
      border-radius: 4px !important;
      color: #007bff !important;
      text-decoration: none !important;
      font-size: 13px !important;
      font-weight: 500 !important;
      transition: all 0.3s ease !important;
    }
    
    .page-num:hover {
      background: #007bff !important;
      color: white !important;
      text-decoration: none !important;
      border-color: #007bff !important;
    }
    
    .page-num.active {
      background: #007bff !important;
      border-color: #007bff !important;
      color: white !important;
      font-weight: bold !important;
    }
    
    /* Disabled navigation button styling */
    .nav-btn.disabled {
      background: #aeb0b2 !important;
      color: #fff !important;
      cursor: not-allowed !important;
      pointer-events: none !important;
    }
    
    .nav-btn.disabled:hover {
      background: #aeb0b2 !important;
      color: #fff !important;
    }
    
    /* Simple footer for "Show All" */
    .simple-info {
      display: flex;
      align-items: center;
      gap: 12px;
      color: white;
      font-size: 16px;
      font-weight: 500;
      background: white;
      padding: 12px 20px;
      border-radius: 6px;
      border: 2px solid #27ae60;
    }
    
    .simple-info .info-icon {
      font-size: 18px;
    }
    
    .simple-info {
      color: #333 !important;
    }
    
    .simple-info strong {
      color: #27ae60 !important;
      font-weight: 600;
    }

    /* Mobile responsive styles */
    @media (max-width: 768px) {
      .table-pagination {
        width: 95% !important;
        padding: 15px !important;
        flex-direction: column !important;
        gap: 15px !important;
      }
      
      .page-indicator {
        font-size: 16px;
      }
      
      .page-current {
        padding: 5px 10px;
        font-size: 14px;
      }
      
      .records-info {
        font-size: 13px;
      }
      
      .pagination-nav {
        gap: 6px;
      }
      
      .nav-btn {
        width: 36px;
        height: 36px;
      }
      
      .nav-icon {
        font-size: 14px;
      }
      
      .page-numbers {
        margin: 0 10px;
        gap: 4px;
      }
      
      .page-num {
        width: 32px;
        height: 32px;
        font-size: 13px;
      }
      
      .simple-info {
        font-size: 15px;
        justify-content: center;
      }
    }

    @media (max-width: 480px) {
      .filter-section {
        padding: 10px;
      }

      .data-container {
        padding: 0;
      }
      
      .table-pagination {
        width: 98%;
        padding: 18px 12px;
        gap: 15px;
        border-radius: 0 0 8px 8px;
      }
      
      .page-indicator {
        font-size: 14px;
        gap: 6px;
      }
      
      .page-text,
      .page-divider {
        font-size: 13px;
      }
      
      .page-current {
        padding: 4px 8px;
        font-size: 13px;
        min-width: 32px;
      }
      
      .records-info {
        font-size: 12px;
      }
      
      .nav-btn {
        width: 32px;
        height: 32px;
      }
      
      .nav-icon {
        font-size: 12px;
      }
      
      .page-numbers {
        margin: 0 8px;
        gap: 3px;
      }
      
      .page-num {
        width: 28px;
        height: 28px;
        font-size: 12px;
      }
      
      .simple-info {
        font-size: 14px;
        gap: 8px;
      }
      
      .simple-info .info-icon {
        font-size: 16px;
      }
    }

    /* Highlight row styling */
    tr.highlight-row {
      background-color: #fff3cd !important;
      animation: pulse-highlight 2s ease-in-out 3;
      border: 2px solid #ffc107 !important;
    }

    tr.highlight-row td {
      background-color: #fff3cd !important;
    }

    @keyframes pulse-highlight {
      0%, 100% {
        background-color: #fff3cd;
      }
      50% {
        background-color: #ffe082;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="ptw-header">
    Daily Report Overview <span>Safety First</span>
    <?php if ($totalPages > 1): ?>
      <div style="font-size: 16px; margin-top: 10px; opacity: 0.8;">
        Page <?= $currentPage ?> of <?= $totalPages ?>
      </div>
    <?php endif; ?>
  </div>
  <form method="GET" id="filterForm" class="filter-section">
    <div class="date-filter-group">
      <input type="date" name="startDate" id="startDate" class="date-input" value="<?= htmlspecialchars($startDate) ?>">
      <input type="date" name="endDate" id="endDate" class="date-input" value="<?= htmlspecialchars($endDate) ?>">
    </div>

    <select name="project" id="projectFilter">
      <option value="">All Projects</option>
      <?php
      $projectQuery = "SELECT id, project_name FROM project ORDER BY project_name";
      $projectResult = $conn->query($projectQuery);
      if ($projectResult && $projectResult->num_rows > 0) {
        while ($row = $projectResult->fetch_assoc()) {
          $selected = ($selectedProject == $row['id']) ? 'selected' : '';
          echo "<option value=\"" . $row['id'] . "\" $selected>" . $row['project_name'] . "</option>";
        }
      }
      ?>
    </select>

    <select name="department" id="departmentFilter">
      <option value="">All Departments</option>
      <?php
      $departmentQuery = "SELECT id, department_name FROM department ORDER BY department_name";
      $departmentResult = $conn->query($departmentQuery);
      if ($departmentResult && $departmentResult->num_rows > 0) {
        while ($row = $departmentResult->fetch_assoc()) {
          $selected = ($selectedDepartment == $row['id']) ? 'selected' : '';
          echo "<option value=\"" . $row['id'] . "\" $selected>" . $row['department_name'] . "</option>";
        }
      }
      ?>
    </select>

    <select name="created_by" id="createdByFilter">
      <option value="">All HSE Users</option>
      <?php
      $userQuery = "SELECT id, username FROM users WHERE user_type = 2 ORDER BY username";
      $userResult = $conn->query($userQuery);
      if ($userResult && $userResult->num_rows > 0) {
        while ($row = $userResult->fetch_assoc()) {
          $selected = ($selectedCreatedBy == $row['id']) ? 'selected' : '';
          echo "<option value=\"" . $row['id'] . "\" $selected>" . htmlspecialchars($row['username']) . "</option>";
        }
      }
      ?>
    </select>

    <select name="risk" id="riskFilter">
      <option value="">All Risk Levels</option>
      <option value="عالية" <?= $selectedRisk == 'عالية' ? 'selected' : '' ?>>عالية (High)</option>
      <option value="متوسطة" <?= $selectedRisk == 'متوسطة' ? 'selected' : '' ?>>متوسطة (Medium)</option>
      <option value="منخفضة" <?= $selectedRisk == 'منخفضة' ? 'selected' : '' ?>>منخفضة (Low)</option>
    </select>

    <select name="entries" id="entriesFilter">
      <option value="5" <?= $entries == '5' ? 'selected' : '' ?>>5</option>
      <option value="10" <?= $entries == '10' ? 'selected' : '' ?>>10</option>
      <option value="20" <?= $entries == '20' ? 'selected' : '' ?>>20</option>
      <option value="30" <?= $entries == '30' ? 'selected' : '' ?>>30</option>
      <option value="all" <?= $entries == 'all' ? 'selected' : '' ?>>Show All</option>
    </select>

    <div class="button-row">
      <button type="submit" class="small-button">Apply</button>
      <button type="button" onclick="resetFilters()" class="tiny-button">Reset</button>
    </div>
  </form>
  
  <form method="post" action="export_daily_report_all.php" class="export-form">
    <button type="submit" class="export-excel-btn">📥 Export All Reports to Excel</button>
  </form>

  <div class="data-container">
    <table class="styled-table_1">
      <thead>
        <tr>
          <th>Details</th>
          <th>ID</th>
          <th>Date</th>
          <th>Project</th>
          <th>Department</th>
          <th>Work Type</th>
          <th>Risk</th>
          <th>Observation Description</th>
          <th>Observation</th>
          <th>Action</th>
          <th>Created By</th>
          <th>Status</th>
          <th>Closed At</th>
          <th>Closed By</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
    <?php
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $status_class = ($row['report_status'] == 0) ? "red" : "green";
        $status_text = ($row['report_status'] == 0) ? "Open" : "Closed";
        $report_id = $row['id'];
        $disabled = ($row['report_status'] == 1) ? "disabled" : "";
        $closedAt = ($row['closed_at']) ? date("Y-m-d H:i", strtotime($row['closed_at'])) : "—";
        $closedBy = ($row['closed_by_username']) ? htmlspecialchars($row['closed_by_username']) : "—";
        
        // Check if this row should be highlighted
        $highlightClass = ($highlightReportId && $report_id == $highlightReportId) ? ' highlight-row' : '';
        ?>
          <tr id="row_<?= $report_id ?>" 
              class="<?= $highlightClass ?>"
              data-image="<?= htmlspecialchars($row['image_upload']) ?>"
              data-closure-image="<?= htmlspecialchars($row['closure_image']) ?>"
              data-closure-notes="<?= htmlspecialchars($row['closure_notes']) ?>">>
          <td>
            <button class="expand-btn" onclick="toggleDetails(<?= $report_id ?>)" id="btn_<?= $report_id ?>">+</button>
          </td>
          <td><?= htmlspecialchars($row['id']) ?></td>        
          <td><?= htmlspecialchars($row['date']) ?></td>
          <td><?= htmlspecialchars($row['project_name'] ?? '_') ?></td>
          <td><?= htmlspecialchars($row['department_name'] ?? '_') ?></td>
          <td><?= htmlspecialchars($row['work_type']) ?></td>
          <td><?= htmlspecialchars($row['risk']) ?></td>
          <td><?= htmlspecialchars($row['observation_description']) ?></td>
          <td><?= htmlspecialchars($row['observation']) ?></td>
          <td><?= htmlspecialchars($row['operation_corrective']) ?></td>
          <td><?= htmlspecialchars($row['username'] ?? 'Legacy Report') ?></td>
          <td><span id="status_<?= $report_id ?>" class="<?= $status_class ?>"><?= $status_text ?></span></td>
          <td id="closedAt_<?= $report_id ?>"><?= $closedAt ?></td>
          <td id="closedBy_<?= $report_id ?>"><?= $closedBy ?></td>
          <td>
            <?php if (hasAccess('dailyreport_overview.php', 'submit')): ?>
              <button class='close-btn' onclick='openCloseModal(<?= $report_id ?>)' <?= $disabled ?>>Close</button>
            <?php endif; ?>
          </td>
        </tr>
        
        <!-- Details Row -->
        <tr class="details-row" id="details_<?= $report_id ?>">
          <td colspan="16">
            <div class="details-content">
              <div class="details-images">
                <!-- Original Image -->
                <div class="image-section">
  <h5>📸 Original Observation Images</h5>
  <?php
  if (!empty($row['image_upload'])) {
      $images = json_decode($row['image_upload'], true); // decode JSON array
      
      if (is_array($images)) {
          foreach ($images as $imgPath) {
              // Ensure the path starts from root
              $webPath = ltrim($imgPath, '/');
              echo '<img src="assests/uploads/' . htmlspecialchars($webPath) . '" alt="Observation Image" style="max-width: 200px; margin: 5px; border: 1px solid #ccc;" />';
          }
      } else {
          // Ensure the path starts from root
          $webPath = ltrim($row['image_upload'], '/');
          echo '<img src="assests/uploads/' . htmlspecialchars($webPath) . '" alt="Observation Image" style="max-width: 200px; margin: 5px; border: 1px solid #ccc;" />';
      }
  } else {
      echo '<div class="no-image">No original images available</div>';
  }
  ?>
</div>
                <!-- Closure Images (only show if observation is closed) -->
                <?php if ($row['report_status'] == 1): ?>
                  <div class="image-section">
                    <h5>✅ Closure Images</h5>
                    <?php if (!empty($row['closure_image'])): ?>
                      <?php
                      $closure_images = json_decode($row['closure_image'], true); // Try to decode as JSON array
                      
                      if (is_array($closure_images)) {
                          // New format: JSON array of filenames
                          foreach ($closure_images as $imgPath) {
                              $webPath = ltrim($imgPath, '/');
                              echo '<img src="assests/uploads/closures/' . htmlspecialchars($webPath) . '" alt="Closure Image" style="max-width: 200px; margin: 5px; border: 1px solid #ccc;" />';
                          }
                      } else {
                          // Old format: single path string (for backward compatibility)
                          $closureWebPath = ltrim($row['closure_image'], '/');
                          if (strpos($closureWebPath, 'assests/uploads/closures/') === 0) {
                              // Already has full path
                              echo '<img src="' . htmlspecialchars($closureWebPath) . '" alt="Closure Image" style="max-width: 200px; margin: 5px; border: 1px solid #ccc;" />';
                          } else {
                              // Add the path prefix
                              echo '<img src="assests/uploads/closures/' . htmlspecialchars($closureWebPath) . '" alt="Closure Image" style="max-width: 200px; margin: 5px; border: 1px solid #ccc;" />';
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
                <h4>📋 Full Observation Details</h4>
                
                <div class="note-item">
                  <div class="note-label">🔍 Complete Observation:</div>
                  <div class="note-content"><?= htmlspecialchars($row['description']) ?></div>
                </div>
                
                <!-- Closure Notes (only show if observation is closed) -->
                <?php if ($row['report_status'] == 1 && !empty($row['closure_notes'])): ?>
                  <div class="note-item closure">
                    <div class="note-label">🔒 Closure Notes:</div>
                    <div class="note-content"><?= htmlspecialchars($row['closure_notes']) ?></div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </td>
        </tr>
        <?php
      }
    } else {
      echo "<tr><td colspan='16'>No reports found</td></tr>";
    }
    ?>
    </tbody>
  </table>

<!-- Force line break and pagination below table -->
<div style="clear: both; width: 100%; height: 1px; display: block;"></div>

<!-- Modern Pagination Under Table -->
<?php if ($totalPages > 1): ?>
<div class="table-pagination" style="display: block !important; width: 100% !important; clear: both !important; float: none !important; position: relative !important; margin-top: 20px !important;">
      <?php
      // Generate pagination URL with current filters
      function getPaginationUrl($page) {
        $params = $_GET;
        $params['page'] = $page;
        return '?' . http_build_query($params);
      }
      
      $showingStart = (($currentPage - 1) * $recordsPerPage) + 1;
      $showingEnd = min($currentPage * $recordsPerPage, $totalRecords);
      ?>
      
      <div class="pagination-info">
        <div class="page-indicator">
          <span class="page-text">Page</span>
          <span class="page-current"><?= $currentPage ?></span>
          <span class="page-divider">of</span>
          <span class="page-total"><?= $totalPages ?></span>
        </div>
        <div class="records-info">
          Showing <strong><?= $showingStart ?>-<?= $showingEnd ?></strong> of <strong><?= $totalRecords ?></strong> reports
        </div>
      </div>
      
      <div class="pagination-nav">
        <!-- First page -->
        <?php if ($currentPage > 1): ?>
          <a href="<?= getPaginationUrl(1) ?>" class="nav-btn first-btn" title="First page">
            <i class="nav-icon">⇤</i>
          </a>
        <?php else: ?>
          <span class="nav-btn disabled" title="Already on first page">
            <i class="nav-icon">⇤</i>
          </span>
        <?php endif; ?>
        
        <!-- Previous page -->
        <?php if ($currentPage > 1): ?>
          <a href="<?= getPaginationUrl($currentPage - 1) ?>" class="nav-btn prev-btn" title="Previous page">
            <i class="nav-icon">‹</i>
          </a>
        <?php else: ?>
          <span class="nav-btn disabled" title="First page">
            <i class="nav-icon">‹</i>
          </span>
        <?php endif; ?>
        
        <!-- Page numbers -->
        <div class="page-numbers">
          <?php
          $startPage = max(1, $currentPage - 2);
          $endPage = min($totalPages, $currentPage + 2);
          
          for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $currentPage) {
              echo '<span class="page-num active">' . $i . '</span>';
            } else {
              echo '<a href="' . getPaginationUrl($i) . '" class="page-num" title="Go to page ' . $i . '">' . $i . '</a>';
            }
          }
          ?>
        </div>
        
        <!-- Next page -->
        <?php if ($currentPage < $totalPages): ?>
          <a href="<?= getPaginationUrl($currentPage + 1) ?>" class="nav-btn next-btn" title="Next page">
            <i class="nav-icon">›</i>
          </a>
        <?php else: ?>
          <span class="nav-btn disabled" title="Last page">
            <i class="nav-icon">›</i>
          </span>
        <?php endif; ?>
        
        <!-- Last page -->
        <?php if ($currentPage < $totalPages): ?>
          <a href="<?= getPaginationUrl($totalPages) ?>" class="nav-btn last-btn" title="Last page">
            <i class="nav-icon">⇥</i>
          </a>
        <?php else: ?>
          <span class="nav-btn disabled" title="Already on last page">
            <i class="nav-icon">⇥</i>
          </span>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
  
  <!-- Info when showing exactly one page -->
  <?php if ($totalPages <= 1): ?>
    <div class="table-pagination simple">
      <div class="simple-info">
        <i class="info-icon">📋</i>
        <span>Displaying all <strong><?= $totalRecords ?></strong> reports</span>
      </div>
    </div>
  <?php endif; ?>
  
  </div>
</div>

<!-- Close Observation Modal -->
<div id="closeModal" class="close-modal">
  <div class="close-modal-content">
    <div class="close-modal-header">
      <h3>🔒 Close Observation</h3>
      <button class="close-btn-x" onclick="closeModal()">&times;</button>
    </div>
    
    <form id="closeObservationForm" enctype="multipart/form-data">
      <input type="hidden" id="modalReportId" name="report_id" value="">
      
      <div class="form-group">
        <label for="closureNotes">📝 Closure Notes *</label>
        <textarea id="closureNotes" name="closure_notes" placeholder="Please describe what happened and why this observation is being closed..." required></textarea>
      </div>
      
      <div class="form-group">
        <label for="closureImage">📷 Closure Images (Optional)</label>
        <input type="file" id="closureImage" name="closure_image[]" accept="image/*" multiple>
        <small style="color: #666; display: block; margin-top: 5px;">Upload images showing the resolution (JPG, PNG max 10MB each, up to 10 files)</small>
      </div>
      
      <div class="modal-buttons">
        <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
        <button type="submit" class="modal-btn modal-btn-submit">Close Observation</button>
      </div>
    </form>
  </div>
</div>

<script>
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

function openCloseModal(reportId) {
  document.getElementById('modalReportId').value = reportId;
  document.getElementById('closeModal').style.display = 'block';
  
  // Clear any previous form data
  document.getElementById('closureNotes').value = '';
  document.getElementById('closureImage').value = '';
}

function closeModal() {
  document.getElementById('closeModal').style.display = 'none';
  
  // Clear form data
  document.getElementById('closureNotes').value = '';
  document.getElementById('closureImage').value = '';
}

// Handle form submission
document.getElementById('closeObservationForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const reportId = document.getElementById('modalReportId').value;
  
  // Add report_status to formData
  formData.append('report_status', 1);
  
  // Add loading state
  const submitBtn = this.querySelector('.modal-btn-submit');
  const originalText = submitBtn.textContent;
  submitBtn.textContent = 'Closing...';
  submitBtn.disabled = true;
  
  fetch('close_observation.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(data => {
    console.log('Server response:', data);
    
    if (data.trim() === 'success') {
      alert('Observation closed successfully!');
      
      // Update the UI
      document.getElementById('status_' + reportId).className = 'green';
      document.getElementById('status_' + reportId).innerText = 'Closed';
      
      // Disable the close button
      const closeButton = document.querySelector(`button[onclick='openCloseModal(${reportId})']`);
      if (closeButton) closeButton.disabled = true;
      
      // Update closed at timestamp
      const now = new Date();
      const formatted = now.getFullYear() + '-' +
        String(now.getMonth() + 1).padStart(2, '0') + '-' +
        String(now.getDate()).padStart(2, '0') + ' ' +
        String(now.getHours()).padStart(2, '0') + ':' +
        String(now.getMinutes()).padStart(2, '0');
      document.getElementById('closedAt_' + reportId).innerText = formatted;
      
      // Update closed by column (you'll need to get current user info)
      // For now, we'll just put "Current User" - you should pass actual username
      document.getElementById('closedBy_' + reportId).innerText = 'Current User';
      
      // Store closure data in row for future detail expansions
      const row = document.getElementById('row_' + reportId);
      row.setAttribute('data-closure-notes', document.getElementById('closureNotes').value);
      
      // Close the modal
      closeModal();
      
      // If details are currently expanded, refresh them to show closure info
      const detailsRow = document.getElementById('details_' + reportId);
      if (detailsRow.style.display === 'table-row') {
        // Collapse and re-expand to refresh content
        toggleDetails(reportId);
        setTimeout(() => toggleDetails(reportId), 100);
      }
    } else {
      alert('Error: ' + data);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error closing observation. Please try again.');
  })
  .finally(() => {
    // Reset button state
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  });
});

// Close modal when clicking outside of it
window.onclick = function(event) {
  const modal = document.getElementById('closeModal');
  if (event.target === modal) {
    closeModal();
  }
}

function resetFilters() {
  window.location.href = window.location.pathname;
}

// Modern Table Pagination Enhancements
document.addEventListener('DOMContentLoaded', function() {
  const tablePagination = document.querySelector('.table-pagination');
  
  if (tablePagination && !tablePagination.classList.contains('simple')) {
    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
      // Only handle keyboard navigation if no input is focused
      if (document.activeElement.tagName === 'INPUT' || 
          document.activeElement.tagName === 'TEXTAREA' || 
          document.activeElement.tagName === 'SELECT') {
        return;
      }
      
      const prevBtn = tablePagination.querySelector('.prev-btn');
      const nextBtn = tablePagination.querySelector('.next-btn');
      const firstBtn = tablePagination.querySelector('.first-btn');
      const lastBtn = tablePagination.querySelector('.last-btn');
      
      switch(e.key) {
        case 'ArrowLeft':
          e.preventDefault();
          if (prevBtn) {
            addPageTransition(() => {
              window.location.href = prevBtn.href;
            });
          }
          break;
          
        case 'ArrowRight':
          e.preventDefault();
          if (nextBtn) {
            addPageTransition(() => {
              window.location.href = nextBtn.href;
            });
          }
          break;
          
        case 'Home':
          e.preventDefault();
          if (firstBtn) {
            addPageTransition(() => {
              window.location.href = firstBtn.href;
            });
          }
          break;
          
        case 'End':
          e.preventDefault();
          if (lastBtn) {
            addPageTransition(() => {
              window.location.href = lastBtn.href;
            });
          }
          break;
      }
    });
    
    // Add smooth transition effect to all navigation links
    const navLinks = tablePagination.querySelectorAll('a[href]');
    navLinks.forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        addPageTransition(() => {
          window.location.href = this.href;
        });
      });
    });
    
    // Add loading animation on page change
    function addPageTransition(callback) {
      const container = document.querySelector('.data-container');
      const paginationContainer = document.querySelector('.table-pagination');
      
      // Add loading state
      if (container) {
        container.style.opacity = '0.6';
        container.style.transition = 'opacity 0.4s ease';
        container.style.transform = 'translateY(10px)';
      }
      
      if (paginationContainer) {
        paginationContainer.style.opacity = '0.8';
        paginationContainer.style.transition = 'opacity 0.4s ease';
      }
      
      // Add loading spinner to footer
      const loadingSpinner = document.createElement('div');
      loadingSpinner.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 40px;
        height: 40px;
        border: 3px solid rgba(52, 152, 219, 0.3);
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        z-index: 1000;
      `;
      
      // Add spinner animation if not already defined
      if (!document.querySelector('#spinner-style')) {
        const style = document.createElement('style');
        style.id = 'spinner-style';
        style.textContent = `
          @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
          }
        `;
        document.head.appendChild(style);
      }
      
      document.body.appendChild(loadingSpinner);
      
      // Execute callback after a short delay for visual effect
      setTimeout(() => {
        document.body.removeChild(loadingSpinner);
        callback();
      }, 600);
    }
    
    // Show helpful keyboard shortcuts on first visit
    if (!sessionStorage.getItem('footer-help-shown')) {
      setTimeout(() => {
        const helpDiv = document.createElement('div');
        helpDiv.style.cssText = `
          position: fixed;
          bottom: 100px;
          right: 20px;
          background: linear-gradient(135deg, #3498db, #2980b9);
          color: white;
          padding: 15px 20px;
          border-radius: 10px;
          font-size: 13px;
          line-height: 1.4;
          box-shadow: 0 4px 15px rgba(0,0,0,0.2);
          z-index: 1000;
          max-width: 250px;
          animation: slideIn 0.5s ease;
        `;
        
        helpDiv.innerHTML = `
          <strong>💡 Quick Navigation:</strong><br>
          • Use ← → arrow keys<br>
          • Home/End for first/last page<br>
          • Click any page number<br>
          <small style="opacity: 0.8; margin-top: 8px; display: block;">This tip won't show again</small>
        `;
        
        // Add slide animation
        const slideStyle = document.createElement('style');
        slideStyle.textContent = `
          @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
          }
        `;
        document.head.appendChild(slideStyle);
        
        document.body.appendChild(helpDiv);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
          helpDiv.style.animation = 'slideIn 0.5s ease reverse';
          setTimeout(() => {
            if (document.body.contains(helpDiv)) {
              document.body.removeChild(helpDiv);
            }
          }, 500);
        }, 5000);
        
        sessionStorage.setItem('footer-help-shown', 'true');
      }, 2000);
    }
  }
});
</script>


</body>
</html>