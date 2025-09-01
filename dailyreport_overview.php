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
$entries = $_GET['entries'] ?? 'all';

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

    @media (max-width: 480px) {
      .filter-section {
        padding: 10px;
      }

      .data-container {
        padding: 0;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="ptw-header">
    Daily Report Overview <span>Safety First</span>
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
      <option value="all" <?= $entries == 'all' ? 'selected' : '' ?>>Show All</option>
      <option value="5" <?= $entries == '5' ? 'selected' : '' ?>>5</option>
      <option value="10" <?= $entries == '10' ? 'selected' : '' ?>>10</option>
      <option value="20" <?= $entries == '20' ? 'selected' : '' ?>>20</option>
      <option value="30" <?= $entries == '30' ? 'selected' : '' ?>>30</option>
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
        $closedBy = ($row['closed_by_username']) ? htmlspecialchars($row['closed_by_username']) : "—";
        ?>
          <tr id="row_<?= $report_id ?>" 
              data-image="<?= htmlspecialchars($row['image_upload']) ?>"
              data-closure-image="<?= htmlspecialchars($row['closure_image']) ?>"
              data-closure-notes="<?= htmlspecialchars($row['closure_notes']) ?>">
          <td>
            <button class="expand-btn" onclick="toggleDetails(<?= $report_id ?>)" id="btn_<?= $report_id ?>">+</button>
          </td>        
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
          <td colspan="15">
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
      echo "<tr><td colspan='15'>No reports found</td></tr>";
    }
    ?>
    </tbody>
  </table>
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
</script>
</body>
</html>