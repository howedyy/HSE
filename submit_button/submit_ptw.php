<?php
// Check authentication first
require_once(__DIR__ . '/../constants/auth_check.php');

header('Content-Type: application/json'); // Return JSON

include_once(__DIR__ . '/../constants/dbconnect.php');

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

function sendTelegramMessageMarkdown($message, $botToken, $chatId) {
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
            'text' => $message,
            'parse_mode' => 'Markdown'
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
        'text' => $message,
        'parse_mode' => 'Markdown'
    ]);
    
    // Attempt the request
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    error_log("Telegram API call (Markdown) - Status: $status, Error: $error, Response: $response");
    
    curl_close($ch);
    return $status >= 200 && $status < 300;
}

function sendTelegramMessageMarkdownV2($message, $botToken, $chatId) {
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
            'text' => $message,
            'parse_mode' => 'MarkdownV2'
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
        'text' => $message,
        'parse_mode' => 'MarkdownV2'
    ]);
    
    // Attempt the request
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    error_log("Telegram API call (MarkdownV2) - Status: $status, Error: $error, Response: $response");
    
    curl_close($ch);
    return $status >= 200 && $status < 300;
}

// Function to generate next permit number atomically
function generateUniquePermitNumber($conn) {
    // Start transaction to ensure atomicity
    $conn->autocommit(false);
    
    try {
        // Lock the PTW table for writing to prevent concurrent access
        $lock_result = $conn->query("LOCK TABLES PTW WRITE");
        if (!$lock_result) {
            throw new Exception("Failed to lock table: " . $conn->error);
        }
        
        // Get the last permit number within the locked transaction
        $query = "SELECT permit_number FROM PTW ORDER BY id DESC LIMIT 1";
        $result = $conn->query($query);
        
        if ($result === false) {
            throw new Exception("Failed to query permit number: " . $conn->error);
        }
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (isset($row['permit_number']) && preg_match('/^PTW(\d{3})$/', $row['permit_number'], $matches)) {
                $last_number = intval($matches[1]);
                $next_number = $last_number + 1;
                error_log("Last permit: " . $row['permit_number'] . ", Next number: $next_number");
            } else {
                error_log("Invalid permit_number format: " . json_encode($row));
                $next_number = 1; // Fallback if format is invalid
            }
        } else {
            $next_number = 1;
            error_log("No previous permits found, starting with $next_number");
        }
        
        $next_permit = "PTW" . str_pad($next_number, 3, "0", STR_PAD_LEFT);
        
        // Commit the transaction and unlock tables
        $conn->commit();
        $conn->query("UNLOCK TABLES");
        $conn->autocommit(true);
        
        return $next_permit;
        
    } catch (Exception $e) {
        // Rollback on error and unlock tables
        $conn->rollback();
        $conn->query("UNLOCK TABLES");
        $conn->autocommit(true);
        error_log("Error generating permit number: " . $e->getMessage());
        throw $e;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate unique permit number atomically
    try {
        $permit_number = generateUniquePermitNumber($conn);
        error_log("Generated permit number: $permit_number at " . date('Y-m-d H:i:s'));
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "خطأ في توليد رقم التصريح: " . $e->getMessage()]);
        $conn->close();
        exit;
    }
    
    // Collect data from form
    $editor_name = $conn->real_escape_string($_POST['editor_name'] ?? NULL);
    $job_title = $conn->real_escape_string($_POST['job_title'] ?? NULL);
    $department = $conn->real_escape_string($_POST['department'] ?? NULL);
    $project_name = $conn->real_escape_string($_POST['project_name'] ?? NULL);
    $work_location = $conn->real_escape_string($_POST['work_location'] ?? NULL);
    // $permit_number is already generated above
    $permit_date = $conn->real_escape_string($_POST['permit_date'] ?? NULL);
    $start_time = $conn->real_escape_string($_POST['start_time'] ?? NULL);
    $end_time = $conn->real_escape_string($_POST['end_time'] ?? NULL);
    $work_description = $conn->real_escape_string($_POST['work_description'] ?? NULL);
    $tools_equipment = $conn->real_escape_string($_POST['tools_equipment'] ?? NULL);
    $company_name = $conn->real_escape_string($_POST['company_name'] ?? NULL);
    $execution_manager_signature = $conn->real_escape_string($_POST['execution_manager_signature'] ?? NULL);
    $admin_signature = $conn->real_escape_string($_POST['admin_signature'] ?? NULL);
    $operation_type = $conn->real_escape_string($_POST['operation'] ?? NULL);
    $risk_assessment = $conn->real_escape_string($_POST['risk'] ?? NULL);
    $safety_measures = isset($_POST['safety']) && is_array($_POST['safety']) ? $conn->real_escape_string(implode(',', $_POST['safety'])) : NULL;

    // SQL query to insert all form data into PTW table
    $sql = "INSERT INTO PTW (editor_name, job_title, department, project_name, work_location, permit_number, permit_date, start_time, end_time, work_description, tools_equipment, company_name, execution_manager_signature, admin_signature, operation_type, risk_assessment, safety_measures) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "خطأ في إعداد الاستعلام: " . $conn->error]);
        $conn->close();
        exit;
    }

    $stmt->bind_param("sssssssssssssssss", $editor_name, $job_title, $department, $project_name, $work_location, $permit_number, $permit_date, $start_time, $end_time, $work_description, $tools_equipment, $company_name, $execution_manager_signature, $admin_signature, $operation_type, $risk_assessment, $safety_measures);

    if ($stmt->execute()) {
        // Handle optional image upload
        if (isset($_FILES['attachment_image']) && $_FILES['attachment_image']['error'] == 0) {
            $uploadedFile = $_FILES['attachment_image'];
            $uploadDir = __DIR__ . '/../assests/uploads/ptw_closure/';
            
            // Ensure directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
            $newFilename = time() . '_' . uniqid() . '_attachment.' . $extension;
            $targetFile = $uploadDir . $newFilename;
            
            if (move_uploaded_file($uploadedFile['tmp_name'], $targetFile)) {
                // Insert into ptw_images
                // We use 'attachment' as the type for initial attachments
                $imgSql = "INSERT INTO ptw_images (permit_number, image_path, image_type) VALUES (?, ?, 'attachment')";
                $imgStmt = $conn->prepare($imgSql);
                if ($imgStmt) {
                    $imgStmt->bind_param("ss", $permit_number, $newFilename);
                    $imgStmt->execute();
                    $imgStmt->close();
                } else {
                    error_log("Failed to prepare ptw_images insert: " . $conn->error);
                }
            } else {
                error_log("Failed to move uploaded file to $targetFile");
            }
        }

        // Query the project region to determine which chat ID to use
        $regionQuery = "SELECT region FROM project WHERE project_name = ?";
        $regionStmt = $conn->prepare($regionQuery);
        
        if (!$regionStmt) {
            error_log("Failed to prepare region query: " . $conn->error);
            // Default to west region if query fails
            $chatId = $westRegionChatId;
        } else {
            $regionStmt->bind_param("s", $project_name);
            $regionStmt->execute();
            $regionResult = $regionStmt->get_result();
            
            if ($regionResult->num_rows > 0) {
                $regionRow = $regionResult->fetch_assoc();
                $region = $regionRow['region'];
                
                // Determine chat ID based on region
                if ($region == 1) {
                    $chatId = $westRegionChatId;
                    error_log("Sending to West Region chat for project: $project_name (region: $region)");
                } elseif ($region == 2) {
                    $chatId = $eastRegionChatId;
                    error_log("Sending to East Region chat for project: $project_name (region: $region)");
                } else {
                    // Default to west region for unknown regions
                    $chatId = $westRegionChatId;
                    error_log("Unknown region $region for project: $project_name, defaulting to West Region");
                }
            } else {
                // Default to west region if project not found
                $chatId = $westRegionChatId;
                error_log("Project $project_name not found in database, defaulting to West Region");
            }
            
            $regionStmt->close();
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
        $link_url = $base_url . "/ptw_overview.php?highlight=" . urlencode($permit_number);
        
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
        
        // Create completely plain text message with URL
        $telegramMsg = "📢 New PTW Submission\n\n"
                     . "🏢 Department: $department_name\n"
                     . "🏗️ Project Name: $project_name\n"
                     . "📍 Work Location: $work_location\n"
                     . "🔢 Permit Number: $permit_number\n"
                     . "⏰ Work Description: $work_description\n"
                     . "⏰ Tools and Equipment: $tools_equipment\n"
                     . "⏰ Submitted: " . date('Y-m-d H:i:s') . "\n\n"
                     . "Link: $link_url";

        // Log the message for debugging
        error_log("Sending Telegram message to chat ID $chatId: " . $telegramMsg);
        
        // Send Telegram message as plain text
        try {
            $telegramSuccess = sendTelegramMessage($telegramMsg, $botToken, $chatId);
            
            if ($telegramSuccess) {
                echo json_encode(["status" => "success", "message" => "Form submitted and notification sent!"]);
            } else {
                error_log("Telegram notification failed at " . date('Y-m-d H:i:s'));
                echo json_encode(["status" => "success", "message" => "Form submitted, but Telegram notification failed."]);
            }
        } catch (Exception $e) {
            error_log("Exception when sending Telegram message: " . $e->getMessage());
            echo json_encode(["status" => "success", "message" => "Form submitted, but Telegram notification error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "خطأ: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>