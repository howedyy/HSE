<?php
// Apply region column changes to project table
require_once('dbconnect.php');

echo "<h2>Applying Region Column Changes</h2>\n";

try {
    // Add region column to project table
    $sql1 = "ALTER TABLE project ADD COLUMN region INT DEFAULT 1";
    if ($conn->query($sql1) === TRUE) {
        echo "<p>✅ Region column added successfully</p>\n";
    } else {
        if (strpos($conn->error, "Duplicate column name") !== false) {
            echo "<p>⚠️ Region column already exists</p>\n";
        } else {
            throw new Exception("Error adding region column: " . $conn->error);
        }
    }

    // Update West Region projects
    $westProjects = ['Wes_town', 'Polygon', 'Allegria', 'Allegria_res', 'Polygon_x', 'Forty_west', '6west Res', 'HUB', 'Pavlion', '6west Comm'];
    $westProjectsStr = "'" . implode("','", $westProjects) . "'";
    
    $sql2 = "UPDATE project SET region = 1 WHERE project_name IN ($westProjectsStr)";
    if ($conn->query($sql2) === TRUE) {
        echo "<p>✅ West Region projects updated (affected rows: " . $conn->affected_rows . ")</p>\n";
    } else {
        throw new Exception("Error updating west region projects: " . $conn->error);
    }

    // Update East Region projects
    $eastProjects = ['Portal', 'SMD', 'EDNC', 'east town ETR', 'SODIC east', 'villet', 'caesar', 'june', 'ogami'];
    $eastProjectsStr = "'" . implode("','", $eastProjects) . "'";
    
    $sql3 = "UPDATE project SET region = 2 WHERE project_name IN ($eastProjectsStr)";
    if ($conn->query($sql3) === TRUE) {
        echo "<p>✅ East Region projects updated (affected rows: " . $conn->affected_rows . ")</p>\n";
    } else {
        throw new Exception("Error updating east region projects: " . $conn->error);
    }

    // Display results
    echo "<h3>Current Project Regions:</h3>\n";
    $result = $conn->query("SELECT id, project_name, region FROM project ORDER BY region, project_name");
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
        echo "<tr><th>ID</th><th>Project Name</th><th>Region</th><th>Region Name</th></tr>\n";
        
        while ($row = $result->fetch_assoc()) {
            $regionName = $row['region'] == 1 ? 'West Region' : ($row['region'] == 2 ? 'East Region' : 'Unknown');
            echo "<tr><td>" . $row['id'] . "</td><td>" . htmlspecialchars($row['project_name']) . "</td><td>" . $row['region'] . "</td><td>" . $regionName . "</td></tr>\n";
        }
        
        echo "</table>\n";
    }

    echo "<h3>Summary:</h3>\n";
    $westCount = $conn->query("SELECT COUNT(*) as count FROM project WHERE region = 1")->fetch_assoc()['count'];
    $eastCount = $conn->query("SELECT COUNT(*) as count FROM project WHERE region = 2")->fetch_assoc()['count'];
    
    echo "<p>West Region Projects: $westCount</p>\n";
    echo "<p>East Region Projects: $eastCount</p>\n";
    echo "<p><strong>✅ Database changes applied successfully!</strong></p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>\n";
} finally {
    $conn->close();
}
?>