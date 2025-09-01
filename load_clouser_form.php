<?php
$report_id = $_GET['report_id'] ?? null;

if ($report_id):
?>
<form action="close_observation.php" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="report_id" value="<?= htmlspecialchars($report_id) ?>">
  
  <label for="closure_notes">Closure Notes:</label><br>
  <textarea name="closure_notes" required rows="4" cols="40"></textarea><br>

  <label for="closure_image">Upload Images:</label><br>
  <input type="file" name="closure_image[]" accept="image/*" multiple required><br>
  <small>You can select multiple images at once (max 10 files, 10MB each)</small><br>

  <input type="submit" value="Submit Closure">
</form>
<?php endif; ?>