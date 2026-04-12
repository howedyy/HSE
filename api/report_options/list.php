<?php
require_once __DIR__ . '/../header.php';

$observations = [];
$res = $conn->query("
    SELECT 
        c.id AS category_id,
        c.name AS category_name,
        c.status AS category_status,
        s.id AS sub_id,
        s.name AS sub_name,
        s.status AS sub_status
    FROM report_observation_types c
    LEFT JOIN report_work_types s ON s.observation_type_id = c.id
    ORDER BY c.id ASC, s.id ASC
");

$map = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $cid = $row['category_id'];
        if (!isset($map[$cid])) {
            $map[$cid] = [
                'id'     => (int)$cid,
                'name'   => $row['category_name'],
                'status' => (int)$row['category_status'],
                'work_types' => [],
            ];
        }
        if ($row['sub_id']) {
            $map[$cid]['work_types'][] = [
                'id'     => (int)$row['sub_id'],
                'name'   => $row['sub_name'],
                'status' => (int)$row['sub_status'],
            ];
        }
    }
}

echo json_encode(['data' => array_values($map)], JSON_UNESCAPED_UNICODE);
$conn->close();
?>
