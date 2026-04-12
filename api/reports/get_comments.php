<?php
require_once __DIR__ . '/../header.php';

if (!isset($_GET['report_id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Report ID is required."]);
    exit();
}

$report_id = intval($_GET['report_id']);
$current_user_id = $_SESSION['user_id'] ?? 0;

// Check if user has permission to view (default to true if they can see reports)
if (!hasAccess('dailyreport_overview.php', 'view')) {
    http_response_code(403);
    echo json_encode(["message" => "Unauthorized access"]);
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
    ORDER BY oc.created_at ASC
");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($comment = $result->fetch_assoc()) {
    $comment['id'] = (int)$comment['id'];
    $comment['user_id'] = (int)$comment['user_id'];
    $comment['is_owner'] = ($comment['user_id'] == $current_user_id);
    
    // Parse images
    if (!empty($comment['image_path'])) {
        $comment['images'] = json_decode($comment['image_path'], true);
    } else {
        $comment['images'] = [];
    }
    unset($comment['image_path']);
    
    $comments[] = $comment;
}

echo json_encode($comments, JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();
?>
