<?php
require_once __DIR__ . '/../header.php';

// Filters
$project = $_GET['project'] ?? '';
$department = $_GET['department'] ?? '';
$risk = $_GET['risk'] ?? '';
$status = $_GET['status'] ?? '';
$createdBy = $_GET['created_by'] ?? '';
$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';

$page = max(1, intval($_GET['page'] ?? 1));
$limit = intval($_GET['limit'] ?? 20);
$offset = ($page - 1) * $limit;

$conditions = [];
$params = [];
$types = '';

if ($project !== '') { $conditions[] = "dr.project = ?"; $params[] = intval($project); $types .= 'i'; }
if ($department !== '') { $conditions[] = "dr.department = ?"; $params[] = intval($department); $types .= 'i'; }
if ($risk !== '') { $conditions[] = "dr.risk = ?"; $params[] = $risk; $types .= 's'; }
if ($status !== '') { $conditions[] = "dr.report_status = ?"; $params[] = intval($status); $types .= 'i'; }
if ($createdBy !== '') { $conditions[] = "dr.user_id = ?"; $params[] = intval($createdBy); $types .= 'i'; }
if ($startDate !== '') { $conditions[] = "dr.date >= ?"; $params[] = $startDate . ' 00:00:00'; $types .= 's'; }
if ($endDate !== '') { $conditions[] = "dr.date <= ?"; $params[] = $endDate . ' 23:59:59'; $types .= 's'; }

$where = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count
$countSql = "SELECT COUNT(*) as total FROM daily_report dr $where";
$stmt = $conn->prepare($countSql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Fetch
$sql = "SELECT dr.id, dr.date, pr.project_name, dp.department_name, dr.work_type, dr.risk,
        dr.observation_description, dr.description, dr.observation, dr.operation_corrective,
        dr.report_status, dr.closed_at, dr.image_upload, dr.closure_notes, dr.closure_image,
        dr.email_sent, dr.user_id,
        u.username as created_by, u2.username as closed_by_username,
        (SELECT COUNT(*) FROM observation_comments WHERE report_id = dr.id) as comments_count
        FROM daily_report dr
        LEFT JOIN project pr ON dr.project = pr.id
        LEFT JOIN department dp ON dr.department = dp.id
        LEFT JOIN users u ON dr.user_id = u.id
        LEFT JOIN users u2 ON dr.closed_by = u2.id
        $where ORDER BY dr.date DESC LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $row['id'] = (int)$row['id'];
    $row['report_status'] = (int)$row['report_status'];
    $reports[] = $row;
}
$stmt->close();

echo json_encode([
    'data' => $reports,
    'total' => (int)$total,
    'page' => $page,
    'totalPages' => ceil($total / $limit),
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>
