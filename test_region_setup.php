<?php
// Test database connection and apply region changes
$host = 'localhost';    
$user = 'root';         
$pass = '';             
$dbname = 'hse'; 

$conn = new mysqli($host, $user, $pass, $dbname);
mysqli_set_charset($conn, "utf8mb4");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

echo "Database connected successfully\n";

// Check if region column exists
$result = $conn->query("SHOW COLUMNS FROM project LIKE 'region'");
if ($result->num_rows == 0) {
    // Add region column
    if ($conn->query("ALTER TABLE project ADD COLUMN region INT DEFAULT 1")) {
        echo "Region column added successfully\n";
    } else {
        echo "Error adding region column: " . $conn->error . "\n";
    }
} else {
    echo "Region column already exists\n";
}

// Update West Region projects (region = 1)
$westProjects = ['Wes_town', 'Polygon', 'Allegria', 'Allegria_res', 'Polygon_x', 'Forty_west', '6west Res', 'HUB', 'Pavlion', '6west Comm'];
foreach ($westProjects as $project) {
    $stmt = $conn->prepare("UPDATE project SET region = 1 WHERE project_name = ?");
    $stmt->bind_param("s", $project);
    $stmt->execute();
    $stmt->close();
}
echo "West region projects updated\n";

// Update East Region projects (region = 2)
$eastProjects = ['Portal', 'SMD', 'EDNC', 'east town ETR', 'SODIC east', 'villet', 'caesar', 'june', 'ogami'];
foreach ($eastProjects as $project) {
    $stmt = $conn->prepare("UPDATE project SET region = 2 WHERE project_name = ?");
    $stmt->bind_param("s", $project);
    $stmt->execute();
    $stmt->close();
}
echo "East region projects updated\n";

// Display results
$result = $conn->query("SELECT project_name, region FROM project ORDER BY region, project_name");
echo "\nCurrent project regions:\n";
echo "West Region (1):\n";
echo "East Region (2):\n";

while ($row = $result->fetch_assoc()) {
    $regionName = $row['region'] == 1 ? 'West' : 'East';
    echo "- " . $row['project_name'] . " (Region: " . $row['region'] . " - $regionName)\n";
}

$conn->close();
echo "\nSetup completed successfully!\n";
?>