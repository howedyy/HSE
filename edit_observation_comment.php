<?php
require_once "constants/auth_check.php";
require_once "constants/dbconnect.php";

// Check permissions
if (!hasAccess('dailyreport_overview.php', 'edit_comment')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    $comment_text = isset($_POST['comment_text']) ? trim($_POST['comment_text']) : '';
    $user_id = $_SESSION['user_id'];

    // Validate inputs
    if ($comment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
        exit();
    }

    if (empty($comment_text)) {
        echo json_encode(['success' => false, 'message' => 'Comment text cannot be empty']);
        exit();
    }

    // Check if user owns this comment
    $checkStmt = $conn->prepare("SELECT user_id FROM observation_comments WHERE id = ?");
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
    
    // Only allow users to edit their own comments
    if ($commentData['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'You can only edit your own comments']);
        exit();
    }

    // Update comment
    $stmt = $conn->prepare("UPDATE observation_comments SET comment_text = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $comment_text, $comment_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Comment updated successfully',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update comment: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
