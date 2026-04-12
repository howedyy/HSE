<?php
require_once __DIR__ . '/../header.php';

if (!hasAccess('dailyreport_overview.php', 'add_comment')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's multipart/form-data (for images) or JSON
    $report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
    $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';
    
    // If not in POST, check JSON input
    if ($report_id === 0 && empty($comment_text)) {
        $json = json_decode(file_get_contents('php://input'), true);
        if ($json) {
            $report_id = isset($json['report_id']) ? intval($json['report_id']) : 0;
            $comment_text = isset($json['comment_text']) ? trim($json['comment_text']) : '';
        }
    }

    $user_id = $_SESSION['user_id'];
    $uploaded_images = [];

    // Validate inputs
    if ($report_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid report ID']);
        exit();
    }

    if (empty($comment_text)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Comment text cannot be empty']);
        exit();
    }

    // Handle Image Uploads
    if (isset($_FILES['comment_image'])) {
        $uploadFolder = '../../assests/uploads/comments/';
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        $maxFiles = 10;

        if (!is_dir($uploadFolder)) {
            mkdir($uploadFolder, 0777, true);
        }

        // Handle single or multiple files
        $files = $_FILES['comment_image'];
        if (!is_array($files['name'])) {
            // Convert to array format if it's a single file
            $files = [
                'name' => [$files['name']],
                'type' => [$files['type']],
                'tmp_name' => [$files['tmp_name']],
                'error' => [$files['error']],
                'size' => [$files['size']]
            ];
        }

        $validFiles = array_filter($files['name'], function($name) { return !empty($name); });
        if (count($validFiles) > $maxFiles) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Too many files. Max limit: ' . $maxFiles]);
            exit();
        }

        foreach ($files['name'] as $key => $fileName) {
            if ($files['error'][$key] === UPLOAD_ERR_NO_FILE) continue;
            if ($files['error'][$key] !== UPLOAD_ERR_OK) continue;

            $tmpPath = $files['tmp_name'][$key];
            $fileSize = $files['size'][$key];
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions)) continue;
            if ($fileSize > $maxFileSize) continue;

            $uniqueName = time() . '_' . uniqid() . '.' . $extension;
            $finalPath = $uploadFolder . $uniqueName;

            if (move_uploaded_file($tmpPath, $finalPath)) {
                $uploaded_images[] = $uniqueName;
            }
        }
    }

    $images_json = !empty($uploaded_images) ? json_encode($uploaded_images) : null;

    $stmt = $conn->prepare("INSERT INTO observation_comments (report_id, user_id, comment_text, image_path, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiss", $report_id, $user_id, $comment_text, $images_json);

    if ($stmt->execute()) {
        $comment_id = $stmt->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment_id' => $comment_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add comment: ' . $conn->error]);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
