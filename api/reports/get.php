<?php
require_once __DIR__ . '/../header.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Report ID is required."]);
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT dr.*, pr.project_name, dp.department_name, u.username as created_by 
                        FROM daily_report dr 
                        LEFT JOIN project pr ON dr.project = pr.id 
                        LEFT JOIN department dp ON dr.department = dp.id 
                        LEFT JOIN users u ON dr.user_id = u.id 
                        WHERE dr.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($report = $result->fetch_assoc()) {
    $report['id'] = (int)$report['id'];
    $report['report_status'] = (int)$report['report_status'];
    $report['user_id'] = (int)$report['user_id'];
    echo json_encode($report, JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Report not found."]);
}

$stmt->close();
$conn->close();
?>
