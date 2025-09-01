<?php 
session_start();
require_once 'constants/dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: index.php'); 
    exit(); 
}

// Optional: Check if session is still valid (e.g., not expired)
// You can add session timeout logic here if needed
?>
