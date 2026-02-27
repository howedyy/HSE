<?php
require_once "constants/auth_check.php";
require_once "constants/dbconnect.php";

// Check permissions
if (!hasAccess('dailyreport_overview.php', 'delete_comment')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    $user_id = $_SESSION['user_id'];

    // Validate inputs
    if ($comment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
        exit();
    }

    // Check if user owns this comment and get image path
    $checkStmt = $conn->prepare("SELECT user_id, image_path FROM observation_comments WHERE id = ?");
    $checkStmt->bind_param("i", $comment_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Comment not found']);
        $checkStmt->close();
        exit();
    }
    
    $commentData = $checkResult->fetch_assoc();
    $checkStmt->close();
    
    // Only allow users to delete their own comments
    if ($commentData['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'You can only delete your own comments']);
        exit();
    }

    // Delete attached images from disk
    if (!empty($commentData['image_path'])) {
        $images = json_decode($commentData['image_path'], true);
        if (is_array($images)) {
            foreach ($images as $img) {
                $filePath = 'assests/uploads/comments/' . $img;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }

    // Delete comment
    $stmt = $conn->prepare("DELETE FROM observation_comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete comment: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
