<?php
require_once __DIR__ . '/../header.php';

if (!hasAccess('dailyreport_overview.php', 'delete_comment')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || (isset($_GET['method']) && $_GET['method'] === 'DELETE')) {
    $comment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $user_id = $_SESSION['user_id'];

    if ($comment_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
        exit();
    }

    // Check ownership unless admin (role 1)
    $stmt = $conn->prepare("SELECT user_id, image_path FROM observation_comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();

    if (!$comment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Comment not found']);
        exit();
    }

    if ($comment['user_id'] != $user_id && $_SESSION['user_type'] != 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You can only delete your own comments']);
        exit();
    }

    // Delete images if they exist
    if (!empty($comment['image_path'])) {
        $images = json_decode($comment['image_path'], true);
        if (is_array($images)) {
            foreach ($images as $img) {
                $filePath = '../../assests/uploads/comments/' . $img;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }

    $delStmt = $conn->prepare("DELETE FROM observation_comments WHERE id = ?");
    $delStmt->bind_param("i", $comment_id);

    if ($delStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete comment']);
    }

    $stmt->close();
    $delStmt->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
