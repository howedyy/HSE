<?php
// Check authentication first
require_once 'constants/auth_check.php';
require_once 'constants/dbconnect.php';

// Set timezone
$conn->query("SET time_zone = '+02:00'");
date_default_timezone_set('Africa/Cairo');

// Check if GD library is available
$gd_available = function_exists('imagecreatefromjpeg') && function_exists('imagecreatefrompng');

function compressImage(string $sourcePath, string $destinationPath, int $quality = 75): bool {
    global $gd_available;
    
    // If GD library is not available, use copy as fallback
    if (!$gd_available) {
        return copy($sourcePath, $destinationPath);
    }
    
    $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
    
    // Get image dimensions
    list($width, $height) = getimagesize($sourcePath);
    
    // Calculate compression quality based on file size
    $fileSize = filesize($sourcePath);
    if ($fileSize > 8 * 1024 * 1024) { // > 8MB
        $quality = 60;
    } else if ($fileSize > 4 * 1024 * 1024) { // > 4MB
        $quality = 65;
    }
    
    // For very large images, resize them
    $maxDimension = 2000; // Maximum width or height
    $newWidth = $width;
    $newHeight = $height;
    
    if ($width > $maxDimension || $height > $maxDimension) {
        if ($width > $height) {
            $newWidth = $maxDimension;
            $newHeight = intval($height * ($maxDimension / $width));
        } else {
            $newHeight = $maxDimension;
            $newWidth = intval($width * ($maxDimension / $height));
        }
    }

    if ($extension === 'jpg' || $extension === 'jpeg') {
        $image = imagecreatefromjpeg($sourcePath);
        
        if ($width !== $newWidth || $height !== $newHeight) {
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $result = imagejpeg($resized, $destinationPath, $quality);
            imagedestroy($resized);
        } else {
            $result = imagejpeg($image, $destinationPath, $quality);
        }
        
        imagedestroy($image);
        return $result;
    }

    if ($extension === 'png') {
        $image = imagecreatefrompng($sourcePath);
        
        // Preserve transparency
        imagesavealpha($image, true);
        
        if ($width !== $newWidth || $height !== $newHeight) {
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $pngQuality = min(9, max(0, intval((100 - $quality) / 11))); // Convert quality to PNG scale (0-9)
            $result = imagepng($resized, $destinationPath, $pngQuality);
            imagedestroy($resized);
        } else {
            $pngQuality = min(9, max(0, intval((100 - $quality) / 11))); // Convert quality to PNG scale (0-9)
            $result = imagepng($image, $destinationPath, $pngQuality);
        }
        
        imagedestroy($image);
        return $result;
    }

    return false;
}

// Get current user ID
$current_user_id = $_SESSION['user_id'] ?? null;
if (!$current_user_id) {
    die("User not logged in. Please login first.");
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Get form data
$report_id = $_POST['report_id'] ?? '';
$closure_notes = $_POST['closure_notes'] ?? '';
$report_status = $_POST['report_status'] ?? 1;

// Validate required fields
if (empty($report_id) || empty($closure_notes)) {
    die("Report ID and closure notes are required.");
}

// Validate that the report exists and is not already closed
$checkQuery = "SELECT id, report_status FROM daily_report WHERE id = ?";
$checkStmt = $conn->prepare($checkQuery);
if (!$checkStmt) {
    die("Error preparing check query: " . $conn->error);
}

$checkStmt->bind_param("i", $report_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows == 0) {
    die("Report not found.");
}

$reportData = $checkResult->fetch_assoc();
if ($reportData['report_status'] == 1) {
    die("Report is already closed.");
}

$checkStmt->close();

// Handle multiple closure images upload
$uploaded_closure_images = [];

if (isset($_FILES['closure_image']) && is_array($_FILES['closure_image']['name'])) {
    $uploadFolder = 'assests/uploads/closures/';
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $maxFileSize = 10 * 1024 * 1024; // 10MB max file size
    $maxFiles = 10; // Maximum number of files
    
    // Create upload folder if it doesn't exist
    if (!is_dir($uploadFolder)) {
        mkdir($uploadFolder, 0777, true);
    }
    
    // Count valid files
    $validFiles = array_filter($_FILES['closure_image']['name'], function($name) { return !empty($name); });
    if (count($validFiles) > $maxFiles) {
        die("Too many files. Max limit: $maxFiles.");
    }
    
    foreach ($_FILES['closure_image']['name'] as $key => $fileName) {
        if (
            empty($fileName) ||
            $_FILES['closure_image']['error'][$key] !== UPLOAD_ERR_OK
        ) {
            continue;
        }
        
        $tmpPath = $_FILES['closure_image']['tmp_name'][$key];
        $fileSize = $_FILES['closure_image']['size'][$key];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validate file
        if (!in_array($extension, $allowedExtensions)) {
            die("Invalid file type for '$fileName'. Allowed: JPG, JPEG, PNG.");
        }
        
        if ($fileSize > $maxFileSize) {
            die("File too large: '$fileName'. Max size is 10MB.");
        }
        
        // Generate unique filename
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $safeBase = preg_replace("/[^A-Za-z0-9_\-]/", "_", $baseName);
        $uniqueName = time() . '_' . uniqid() . '_closure_' . $safeBase . '.' . $extension;
        $finalPath = $uploadFolder . $uniqueName;
        $webPath = $uniqueName; // Store just the filename, not the full path
        
        // If GD library is available, try compression for large files
        if ($gd_available && $fileSize > 4 * 1024 * 1024) {
            $tempFinalPath = $uploadFolder . 'temp_' . $uniqueName;
            
            // First move the uploaded file to a temporary location
            if (move_uploaded_file($tmpPath, $tempFinalPath)) {
                if (compressImage($tempFinalPath, $finalPath)) {
                    unlink($tempFinalPath); // Delete the temporary file
                    $uploaded_closure_images[] = $webPath;
                } else {
                    // If compression fails, just use the original file
                    rename($tempFinalPath, $finalPath);
                    $uploaded_closure_images[] = $webPath;
                }
            } else {
                die("Failed to upload: '$fileName'.");
            }
        } else {
            // For smaller files or when GD is not available, just move the file
            if (move_uploaded_file($tmpPath, $finalPath)) {
                $uploaded_closure_images[] = $webPath;
            } else {
                die("Failed to upload: '$fileName'.");
            }
        }
    }
}

$closure_images_json = json_encode($uploaded_closure_images);

// Update the daily report
$closed_at = date('Y-m-d H:i:s');

$updateQuery = "UPDATE daily_report SET 
                report_status = ?, 
                closure_notes = ?, 
                closure_image = ?, 
                closed_at = ?, 
                closed_by = ? 
                WHERE id = ?";

$updateStmt = $conn->prepare($updateQuery);
if (!$updateStmt) {
    die("Error preparing update query: " . $conn->error);
}

$updateStmt->bind_param("isssii", 
    $report_status, 
    $closure_notes, 
    $closure_images_json, 
    $closed_at, 
    $current_user_id, 
    $report_id
);

if ($updateStmt->execute()) {
    echo "success";
} else {
    die("Error updating report: " . $updateStmt->error);
}

$updateStmt->close();
$conn->close();
?>