<?php
require_once "constants/auth_check.php";
require_once "constants/dbconnect.php";

if (isset($_GET['report_id'])) {
    $report_id = intval($_GET['report_id']);
    $current_user_id = $_SESSION['user_id'];
    
    // Check if user has permission to view comments
    if (!hasAccess('dailyreport_overview.php', 'view')) {
        echo '<div class="no-comments">Unauthorized access</div>';
        exit();
    }
    
    // Get comments for this report
    $stmt = $conn->prepare("
        SELECT 
            oc.id,
            oc.comment_text,
            oc.image_path,
            oc.created_at,
            oc.updated_at,
            oc.user_id,
            u.username
        FROM observation_comments oc
        LEFT JOIN users u ON oc.user_id = u.id
        WHERE oc.report_id = ?
        ORDER BY oc.created_at DESC
    ");
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($comment = $result->fetch_assoc()) {
            $isOwner = ($comment['user_id'] == $current_user_id);
            $updatedText = $comment['updated_at'] ? ' (edited)' : '';
            $timestamp = $comment['updated_at'] ? $comment['updated_at'] : $comment['created_at'];
            
            echo '<div class="comment-item" data-comment-id="' . $comment['id'] . '">';
            echo '<div class="comment-header">';
            echo '<span class="comment-author">👤 ' . htmlspecialchars($comment['username']) . '</span>';
            echo '<span class="comment-time">🕒 ' . date('Y-m-d H:i', strtotime($timestamp)) . $updatedText . '</span>';
            echo '</div>';
            echo '<div class="comment-text" id="comment-text-' . $comment['id'] . '">' . nl2br(htmlspecialchars($comment['comment_text'])) . '</div>';
            
            // Display Attached Images
            if (!empty($comment['image_path'])) {
                $images = json_decode($comment['image_path'], true);
                if (is_array($images)) {
                    echo '<div class="comment-images">';
                    foreach ($images as $img) {
                        echo '<a href="assests/uploads/comments/' . htmlspecialchars($img) . '" target="_blank">';
                        echo '<img src="assests/uploads/comments/' . htmlspecialchars($img) . '" alt="Comment Attachment" class="comment-img">';
                        echo '</a>';
                    }
                    echo '</div>';
                }
            }
            

            echo '</div>';
        }
    } else {
        echo '<div class="no-comments">No comments yet. Be the first to comment!</div>';
    }
    
    $stmt->close();
} else {
    echo '<div class="no-comments">Invalid request</div>';
}

$conn->close();
?>
