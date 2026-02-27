<?php
require_once __DIR__ . '/../header.php';

// Logout - destroy session
session_unset();
session_destroy();

echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
?>
