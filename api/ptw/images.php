<?php
require_once __DIR__ . '/../header.php';

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(["message" => "Not authenticated."]); exit(); }

$permit_number = trim($_GET['permit_number'] ?? '');
if (!$permit_number) { echo json_encode([]); exit(); }

$stmt = $conn->prepare("SELECT image_path, image_type FROM ptw_images WHERE permit_number=? ORDER BY id ASC");
$stmt->bind_param("s", $permit_number);
$stmt->execute();
$result = $stmt->get_result();

$images = [];
while ($row = $result->fetch_assoc()) $images[] = $row;
$stmt->close(); $conn->close();

echo json_encode($images, JSON_UNESCAPED_UNICODE);
?>
