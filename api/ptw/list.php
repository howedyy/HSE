<?php
require_once __DIR__ . '/../header.php';

$project = $_GET['project'] ?? '';
$department = $_GET['department'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = intval($_GET['limit'] ?? 20);
$offset = ($page - 1) * $limit;

$conditions = [];
if ($project !== '') $conditions[] = "p.project_name = '" . $conn->real_escape_string($project) . "'";
if ($department !== '') $conditions[] = "p.department = " . intval($department);
if ($status !== '') $conditions[] = "p.ptw_status = " . intval($status);

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count
$countRes = $conn->query("SELECT COUNT(*) as total FROM PTW p LEFT JOIN department d ON p.department = d.id $where");
$total = $countRes ? (int)$countRes->fetch_assoc()['total'] : 0;

// Fetch
$sql = "SELECT p.id, p.permit_number, p.permit_date, p.editor_name, p.job_title,
        p.project_name, d.department_name, p.operation_type, p.ptw_status,
        p.work_location, p.work_description
        FROM PTW p LEFT JOIN department d ON p.department = d.id
        $where ORDER BY p.id DESC LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
$permits = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int)$row['id'];
        $row['ptw_status'] = (int)$row['ptw_status'];
        $permits[] = $row;
    }
}

echo json_encode([
    'data' => $permits,
    'total' => $total,
    'page' => $page,
    'totalPages' => ceil($total / max($limit, 1)),
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>
