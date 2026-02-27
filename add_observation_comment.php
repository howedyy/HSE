<?php
require_once "constants/auth_check.php";
require_once "constants/dbconnect.php";

// Check permissions
if (!hasAccess('dailyreport_overview.php', 'add_comment')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
    $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';
    $user_id = $_SESSION['user_id'];
    $uploaded_images = [];

    // Validate inputs
    if ($report_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid report ID']);
        exit();
    }

    if (empty($comment_text)) {
        echo json_encode(['success' => false, 'message' => 'Comment text cannot be empty']);
        exit();
    }

    // Handle Image Uploads
    if (isset($_FILES['comment_image']) && !empty($_FILES['comment_image']['name'][0])) {
        $uploadFolder = 'assests/uploads/comments/';
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        $maxFiles = 10;

        if (!is_dir($uploadFolder)) {
            if (!mkdir($uploadFolder, 0777, true)) {
                echo json_encode(['success' => false, 'message' => 'Failed to create comment upload directory.']);
                exit();
            }
        }

        $validFiles = array_filter($_FILES['comment_image']['name'], function($name) { return !empty($name); });
        if (count($validFiles) > $maxFiles) {
            echo json_encode(['success' => false, 'message' => 'Too many files. Max limit: ' . $maxFiles]);
            exit();
        }

        foreach ($_FILES['comment_image']['name'] as $key => $fileName) {
            if ($_FILES['comment_image']['error'][$key] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($_FILES['comment_image']['error'][$key] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => "Upload error for $fileName: " . $_FILES['comment_image']['error'][$key]]);
                exit();
            }

            $tmpPath = $_FILES['comment_image']['tmp_name'][$key];
            $fileSize = $_FILES['comment_image']['size'][$key];
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions)) {
                echo json_encode(['success' => false, 'message' => "Invalid file type: $fileName"]);
                exit();
            }

            if ($fileSize > $maxFileSize) {
                echo json_encode(['success' => false, 'message' => "File too large: $fileName"]);
                exit();
            }

            $uniqueName = time() . '_' . uniqid() . '.' . $extension;
            $finalPath = $uploadFolder . $uniqueName;

            if (move_uploaded_file($tmpPath, $finalPath)) {
                $uploaded_images[] = $uniqueName;
            } else {
                echo json_encode(['success' => false, 'message' => "Failed to move uploaded file: $fileName"]);
                exit();
            }
        }
    }

    $images_json = !empty($uploaded_images) ? json_encode($uploaded_images) : null;

    // Insert comment with image_path
    $stmt = $conn->prepare("INSERT INTO observation_comments (report_id, user_id, comment_text, image_path, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiss", $report_id, $user_id, $comment_text, $images_json);

    if ($stmt->execute()) {
        $comment_id = $stmt->insert_id;
        
        // Get the username for display
        $userStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $userStmt->bind_param("i", $user_id);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userData = $userResult->fetch_assoc();
        $userStmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment_id' => $comment_id,
            'username' => $userData['username'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add comment: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
