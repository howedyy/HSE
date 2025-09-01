 <?php

$host = 'localhost';    
$user = 'root';         
$pass = '';             
$dbname = 'hse'; 
$store_url = "http://localhost//Edara-HSE111/";   

$conn = new mysqli($host, $user, $pass, $dbname);
mysqli_set_charset($conn, "utf8mb4");
// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
