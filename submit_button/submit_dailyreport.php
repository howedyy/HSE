<?php
// Check authentication first
require_once(__DIR__ . '/../constants/auth_check.php');

require_once(__DIR__ . '/../constants/dbconnect.php');
//$conn->query("SET time_zone = '+02:00'");
//date_default_timezone_set('Africa/Cairo');

// Telegram configuration - ensure proper formatting
$botToken = "7790658256:AAFeCjdZ_IllGGp1_5IE4A7P1eSoLCcWgHk";

// Define chat IDs for different regions
$westRegionChatId = -1002005572564; // H&S Edara West Region
$eastRegionChatId = -1002285365220; // H&S Edara East Region
//$testChatId = -1002792075991; // Hse-test

// Check if the server can connect to the internet
$internetTest = @fsockopen("api.telegram.org", 443); 
if(!$internetTest) {
    error_log("Cannot connect to Telegram API - network connection issue");
} else {
    fclose($internetTest);
    error_log("Successfully connected to api.telegram.org");
}

function sendTelegramMessage($message, $botToken, $chatId) {
    // Try a completely new implementation with direct posting and fewer options
    
    // Make sure the bot token and chat ID are in the correct format
    $botToken = trim($botToken);
    $chatId = trim($chatId);
    
    // First check if cURL is available
    if (!function_exists('curl_init')) {
        error_log("cURL is not enabled on this server. Falling back to file_get_contents");
        
        // Try using file_get_contents as fallback
        $url = "https://api.telegram.org/bot$botToken/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message
        ];
        
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 5
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            error_log("file_get_contents failed for Telegram API");
            return false;
        }
        
        return true;
    }
    
    // Use a simplified cURL approach with minimal options
    $ch = curl_init("https://api.telegram.org/bot$botToken/sendMessage");
    
    // Basic setup
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'chat_id' => $chatId,
        'text' => $message
    ]);
    
    // Attempt the request
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    error_log("Telegram API call - Status: $status, Error: $error, Response: $response");
    
    curl_close($ch);
    return $status >= 200 && $status < 300;
}

// 🔐 Verify session
$current_user_id = $_SESSION['user_id'] ?? null;
if (!$current_user_id) {
    die("User not logged in. Please login first.");
}

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

// 🔍 Collect form inputs safely
$date = (new DateTime('now', new DateTimeZone('Africa/Cairo')))->format('Y-m-d H:i:s');
$projectname             = $_POST['projectname'] ?? '';
$department              = $_POST['department'] ?? '';
$observation             = $_POST['observation'] ?? '';
$work_type               = $_POST['work_type'] ?? '';
$risk                    = $_POST['risk'] ?? '';
$observation_description = $_POST['observation_description'] ?? '';
$description             = $_POST['description'] ?? '';
$operation_corrective    = $_POST['operation_corrective'] ?? '';

$uploaded_images = [];

// 📁 Handle multiple images
if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $uploadFolder     = '../assests/uploads/';
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $maxFileSize      = 10 * 1024 * 1024; // Increased to 10MB to allow for compression
    $maxFiles         = 10;

    // Create upload folder if missing
    if (!is_dir($uploadFolder)) {
        mkdir($uploadFolder, 0777, true);
    }

    $validFiles = array_filter($_FILES['images']['name'], function($name) { return !empty($name); });
    if (count($validFiles) > $maxFiles) {
        die("Too many files. Max limit: $maxFiles.");
    }

    foreach ($_FILES['images']['name'] as $key => $fileName) {
        if (
            empty($fileName) ||
            $_FILES['images']['error'][$key] !== UPLOAD_ERR_OK
        ) {
            continue;
        }

        $tmpPath     = $_FILES['images']['tmp_name'][$key];
        $fileSize    = $_FILES['images']['size'][$key];
        $extension   = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // 🧼 Validate extension and size
        if (!in_array($extension, $allowedExtensions)) {
            die("Invalid file type for '$fileName'. Allowed: JPG, JPEG, PNG.");
        }

        if ($fileSize > $maxFileSize) {
            die("File too large: '$fileName'. Max size is 10MB.");
        }

        // 🎯 Unique name generation
        $baseName     = pathinfo($fileName, PATHINFO_FILENAME);
        $safeBase     = preg_replace("/[^A-Za-z0-9_\-]/", "_", $baseName);
        $uniqueName   = time() . '_' . uniqid() . '_' . $safeBase . '.' . $extension;
        $finalPath    = $uploadFolder . $uniqueName;
        $webPath      = '' . $uniqueName;

        // If GD library is available, try compression for large files
        if ($gd_available && $fileSize > 4 * 1024 * 1024) {
            $tempFinalPath = $uploadFolder . 'temp_' . $uniqueName;
            
            // First move the uploaded file to a temporary location
            if (move_uploaded_file($tmpPath, $tempFinalPath)) {
                if (compressImage($tempFinalPath, $finalPath)) {
                    unlink($tempFinalPath); // Delete the temporary file
                    $uploaded_images[] = $webPath;
                } else {
                    // If compression fails, just use the original file
                    rename($tempFinalPath, $finalPath);
                    $uploaded_images[] = $webPath;
                }
            } else {
                die("Failed to upload: '$fileName'.");
            }
        } else {
            // For smaller files or when GD is not available, just move the file
            if (move_uploaded_file($tmpPath, $finalPath)) {
                $uploaded_images[] = $webPath;
            } else {
                die("Failed to upload: '$fileName'.");
            }
        }
    }
}

$images_json = json_encode($uploaded_images);
$edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;

// If editing and no new images, preserve old images
if ($edit_id > 0 && empty($uploaded_images)) {
    $existingStmt = $conn->prepare("SELECT image_upload FROM daily_report WHERE id = ?");
    $existingStmt->bind_param("i", $edit_id);
    $existingStmt->execute();
    $existingResult = $existingStmt->get_result()->fetch_assoc();
    if ($existingResult) {
        $images_json = $existingResult['image_upload'];
    }
    $existingStmt->close();
}

// 🧪 Field validation
if (
    $date &&
    $projectname &&
    $department &&
    $observation &&
    $risk &&
    $observation_description &&
    $description &&
    $images_json &&
    $operation_corrective
) {
    if ($edit_id > 0) {
        // UPDATE existing report
        $sql = "UPDATE daily_report SET 
                    project = ?, department = ?, observation = ?,
                    work_type = ?, risk = ?, observation_description = ?,
                    operation_corrective = ?, description = ?, image_upload = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issssssssi",
            $projectname,
            $department,
            $observation,
            $work_type,
            $risk,
            $observation_description,
            $operation_corrective,
            $description,
            $images_json,
            $edit_id
        );
    } else {
        // INSERT new report
        $sql = "INSERT INTO daily_report (
                    date, project, department, observation,
                    work_type, risk, observation_description,
                    operation_corrective, description, image_upload, user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sissssssssi",
            $date,
            $projectname,
            $department,
            $observation,
            $work_type,
            $risk,
            $observation_description,
            $operation_corrective,
            $description,
            $images_json,
            $current_user_id
        );
    }

    if ($stmt->execute()) {
        echo "success";
        
        // Only send Telegram notification for NEW high risk reports
        if ($edit_id == 0 && $risk === "عالية") {
            // Get the last inserted ID
            $report_id = $conn->insert_id;
            
            // Get department name from department table
            $department_name = $department; // Default to department ID if query fails
            $deptQuery = "SELECT department_name FROM department WHERE id = ?";
            $deptStmt = $conn->prepare($deptQuery);
            
            if ($deptStmt) {
                $deptStmt->bind_param("s", $department);
                $deptStmt->execute();
                $deptResult = $deptStmt->get_result();
                
                if ($deptResult->num_rows > 0) {
                    $deptRow = $deptResult->fetch_assoc();
                    $department_name = $deptRow['department_name'];
                }
                
                $deptStmt->close();
            }
            
            // Query the project region to determine which chat ID to use
            $regionQuery = "SELECT region FROM project WHERE id = ?";
            $regionStmt = $conn->prepare($regionQuery);
            
            if (!$regionStmt) {
                error_log("Failed to prepare region query: " . $conn->error);
                // Default to west region if query fails
                $chatId = $westRegionChatId;
            } else {
                $regionStmt->bind_param("s", $projectname);
                $regionStmt->execute();
                $regionResult = $regionStmt->get_result();
                
                if ($regionResult->num_rows > 0) {
                    $regionRow = $regionResult->fetch_assoc();
                    $region = $regionRow['region'];
                    
                    // Determine chat ID based on region
                    if ($region == 1) {
                        $chatId = $westRegionChatId;
                        error_log("Sending to West Region chat for project: $projectname (region: $region)");
                    } elseif ($region == 2) {
                        $chatId = $eastRegionChatId;
                        error_log("Sending to East Region chat for project: $projectname (region: $region)");
                    } else {
                        // Default to west region for unknown regions
                        $chatId = $westRegionChatId;
                        error_log("Unknown region $region for project: $projectname, defaulting to West Region");
                    }
                } else {
                    // Default to west region if project not found
                    $chatId = $westRegionChatId;
                    error_log("Project $projectname not found in database, defaulting to West Region");
                }
                
                $regionStmt->close();
            }
            
            // Get project name from project table
            $project_name = "Project #$projectname"; // Default if query fails
            $projQuery = "SELECT project_name FROM project WHERE id = ?";
            $projStmt = $conn->prepare($projQuery);
            
            if ($projStmt) {
                $projStmt->bind_param("s", $projectname);
                $projStmt->execute();
                $projResult = $projStmt->get_result();
                
                if ($projResult->num_rows > 0) {
                    $projRow = $projResult->fetch_assoc();
                    $project_name = $projRow['project_name'];
                }
                
                $projStmt->close();
            }
            
            // Get the server hostname and protocol for link generation
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            
            // Try to get the actual IP address instead of localhost
            if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
                // Try to get the local network IP
                $local_ip = '';
                if (function_exists('gethostbyname')) {
                    $local_ip = gethostbyname(gethostname());
                }
                // If we can't get IP, use a default that might work better
                $host = $local_ip ?: '192.168.1.100'; // Replace with your actual local IP
            } else {
                $host = $_SERVER['HTTP_HOST'];
            }
            
            $base_url = $protocol . $host;
            
            // Create a properly encoded URL
            $link_url = $base_url . "/Edara-HSE111/dailyreport_overview.php?highlight=" . urlencode($report_id);
            
            // Create completely plain text message with URL
            $telegramMsg = "⚠️ HIGH RISK Daily Report Submitted ⚠️\n\n"
                          . "🏢 Department: $department_name\n"
                          . "🏗️ Project: $project_name\n"
                          . "📝 Observation: $observation_description\n"
                          . "⚠️ Risk Level: $risk\n"
                          . "⏰ Submitted: " . date('Y-m-d H:i:s') . "\n\n"
                          . "Link: $link_url";

            // Log the message for debugging
            error_log("Sending Telegram message to chat ID $chatId: " . $telegramMsg);
            
            // Send Telegram message as plain text
            try {
                $telegramSuccess = sendTelegramMessage($telegramMsg, $botToken, $chatId);
                
                if (!$telegramSuccess) {
                    error_log("Telegram notification failed at " . date('Y-m-d H:i:s'));
                }
            } catch (Exception $e) {
                error_log("Exception when sending Telegram message: " . $e->getMessage());
            }
        }
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    echo "All fields are required.";
}

$conn->close();
?>
