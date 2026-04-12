<?php
require_once __DIR__ . '/../header.php';

$operation_types = [];
$res1 = $conn->query("SELECT * FROM ptw_operation_types ORDER BY operation_name ASC");
if ($res1) {
    while ($row = $res1->fetch_assoc()) {
        $row['id'] = (int)$row['id'];
        $row['is_active'] = (int)$row['is_active'];
        $operation_types[] = $row;
    }
}

$safety_measures = [];
$res2 = $conn->query("SELECT * FROM ptw_safety_measures ORDER BY measure_name ASC");
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        $row['id'] = (int)$row['id'];
        $row['is_active'] = (int)$row['is_active'];
        $safety_measures[] = $row;
    }
}

echo json_encode([
    'operation_types' => $operation_types,
    'safety_measures' => $safety_measures
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>
