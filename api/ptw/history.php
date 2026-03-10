<?php
require_once __DIR__ . '/../header.php';

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(["message" => "Not authenticated."]); exit(); }

$permit_number = trim($_GET['permit_number'] ?? '');
if (!$permit_number) { echo json_encode([]); exit(); }

$stmt = $conn->prepare("SELECT action, action_by, action_date, notes FROM ptw_history WHERE permit_number=? ORDER BY action_date ASC");
$stmt->bind_param("s", $permit_number);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) $history[] = $row;
$stmt->close(); $conn->close();

echo json_encode($history, JSON_UNESCAPED_UNICODE);
?>
