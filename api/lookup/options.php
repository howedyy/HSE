<?php
require_once __DIR__ . '/../header.php';

// Returns projects and departments for form dropdowns
$projects = [];
$departments = [];

$res = $conn->query("SELECT id, project_name FROM project WHERE project_status = 1 ORDER BY project_name");
if ($res) { while ($r = $res->fetch_assoc()) $projects[] = $r; }

$res = $conn->query("SELECT id, department_name FROM department WHERE department_status = 1 ORDER BY department_name");
if ($res) { while ($r = $res->fetch_assoc()) $departments[] = $r; }

$users = [];
$res = $conn->query("SELECT id, username FROM users WHERE user_type IN (1, 2) ORDER BY username");
if ($res) { while ($r = $res->fetch_assoc()) $users[] = $r; }

echo json_encode([
    'projects' => $projects,
    'departments' => $departments,
    'users' => $users,
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>
