<?php
// Check authentication first
require_once "../constants/auth_check.php";

require_once "../constants/dbconnect.php";

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

if (!isset($_POST['permit_number'])) {
    header("Location: ../ptw_overview.php");
    exit();
}

$permit_number = $_POST['permit_number'];
$noncompliance_reason = $_POST['noncompliance_reason'];
$noncompliance_date = date('Y-m-d'); // Set to current date
$noncompliance_time = date('H:i:s'); // Set to current time
$safety_officer = $_POST['safety_officer'];
$corrective_actions = $_POST['corrective_actions'];

// Update the PTW status to 4 (Non Compliance)
$update_sql = "UPDATE ptw SET ptw_status = 4 WHERE permit_number = ?";

$stmt = $conn->prepare($update_sql);
if ($stmt === false) {
    die("Error in SQL prepare statement: " . $conn->error);
}
$stmt->bind_param("s", $permit_number);

$success = $stmt->execute();
$stmt->close();

// Try to update additional non-compliance details after status has been updated
try {
    $details_sql = "UPDATE ptw SET 
                   noncompliance_reason = ?,
                   noncompliance_date = ?,
                   noncompliance_time = ?,
                   noncompliance_officer = ?,
                   corrective_actions = ?
                   WHERE permit_number = ?";
                   
    $details_stmt = $conn->prepare($details_sql);
    if ($details_stmt) {
        $details_stmt->bind_param("ssssss", $noncompliance_reason, $noncompliance_date, $noncompliance_time, $safety_officer, $corrective_actions, $permit_number);
        $details_stmt->execute();
        $details_stmt->close();
    }
} catch (Exception $e) {
    // Silently ignore if columns don't exist yet
}

// Handle image uploads
$uploadedImages = [];
if (!empty($_FILES['noncompliance_images']['name'][0])) {
    $upload_dir = "../assests/uploads/ptw_closure/";
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $total_files = count($_FILES['noncompliance_images']['name']);
    
    for ($i = 0; $i < $total_files; $i++) {
        $file_name = $_FILES['noncompliance_images']['name'][$i];
        $file_tmp = $_FILES['noncompliance_images']['tmp_name'][$i];
        $file_size = $_FILES['noncompliance_images']['size'][$i];
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file
        if (!in_array($extension, $allowedExtensions)) {
            continue; // Skip invalid file types
        }
        
        if ($file_size > $maxFileSize) {
            continue; // Skip files that are too large
        }
        
        // Generate unique filename
        $baseName = pathinfo($file_name, PATHINFO_FILENAME);
        $safeBase = preg_replace("/[^A-Za-z0-9_\-]/", "_", $baseName);
        $unique_name = time() . '_noncompliance_' . $safeBase . '.' . $extension;
        $target_file = $upload_dir . $unique_name;
        $web_path = $unique_name;  // Store only filename, not full path
        
        // Use compression for large files
        if ($gd_available && $file_size > 4 * 1024 * 1024) {
            $temp_file = $upload_dir . 'temp_' . $unique_name;
            
            // First move the uploaded file to a temporary location
            if (move_uploaded_file($file_tmp, $temp_file)) {
                if (compressImage($temp_file, $target_file)) {
                    unlink($temp_file); // Delete the temporary file
                    $uploadedImages[] = $web_path;
                } else {
                    // If compression fails, just use the original file
                    rename($temp_file, $target_file);
                    $uploadedImages[] = $web_path;
                }
            }
        } else {
            // For smaller files or when GD is not available, just move the file
            if (move_uploaded_file($file_tmp, $target_file)) {
                $uploadedImages[] = $web_path;
            }
        }
        
        // Insert image record into database with web path
        if (in_array($web_path, $uploadedImages)) {
            $img_sql = "INSERT INTO ptw_images (permit_number, image_path, image_type) VALUES (?, ?, 'noncompliance')";
            $img_stmt = $conn->prepare($img_sql);
            $img_stmt->bind_param("ss", $permit_number, $web_path);
            $img_stmt->execute();
        }
    }
}

// Log the non-compliance action
$log_sql = "INSERT INTO ptw_history (permit_number, action, action_by, action_date, notes) 
            VALUES (?, 'Marked as Non Compliance', ?, NOW(), ?)";
$log_stmt = $conn->prepare($log_sql);
$username = $_SESSION['username'] ?? 'Unknown';
$log_notes = "Non-compliance reason: $noncompliance_reason";
$log_stmt->bind_param("sss", $permit_number, $username, $log_notes);
$log_stmt->execute();

if ($success) {
    $_SESSION['message'] = "PTW marked as Non Compliance successfully.";
} else {
    $_SESSION['message'] = "Error marking PTW as Non Compliance: " . $conn->error;
}

header("Location: ../ptw_overview.php");
exit(); 