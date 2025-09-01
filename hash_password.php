<?php
require_once 'constants/dbconnect.php';

// Create a more secure version of this script with better output handling
header('Content-Type: text/plain');

try {
    echo "Password hashing script started.\n";
    
    // First, check if there are any unhashed passwords
    $checkSql = "SELECT COUNT(*) AS unhashed_count FROM users WHERE LENGTH(password) < 40";
    $checkResult = $conn->query($checkSql);
    $row = $checkResult->fetch_assoc();
    
    if ($row['unhashed_count'] == 0) {
        echo "All passwords appear to be already hashed. No action needed.\n";
        exit;
    }
    
    echo "Found {$row['unhashed_count']} unhashed passwords. Processing...\n";
    
    // Fetch all users with unhashed passwords
    $sql = "SELECT id, username, password FROM users WHERE LENGTH(password) < 40";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $updated = 0;
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $username = $row['username'];
            $plainPassword = $row['password'];
            
            // Hash the password using PHP's password_hash function (bcrypt by default)
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
            
            // Update the user with the hashed password
            $updateSql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("si", $hashedPassword, $id);
            
            if ($stmt->execute()) {
                echo "Password hashed successfully for user: $username (ID: $id)\n";
                $updated++;
            } else {
                echo "Failed to hash password for user: $username (ID: $id) - " . $conn->error . "\n";
            }
            $stmt->close();
        }
        
        echo "\nPassword hashing complete. $updated out of " . $result->num_rows . " passwords updated.\n";
    } else {
        echo "No unhashed passwords found in the users table.\n";
    }

    $conn->close();
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}
?>