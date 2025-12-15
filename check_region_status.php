<!DOCTYPE html>
<html>
<head>
    <title>Region Setup Status</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Region Setup Status Check</h1>
    
    <?php
    require_once('dbconnect.php');
    
    try {
        echo "<h2>Database Connection</h2>";
        echo "<p class='success'>✅ Connected to database successfully</p>";
        
        // Check if region column exists
        echo "<h2>Region Column Status</h2>";
        $result = $conn->query("SHOW COLUMNS FROM project LIKE 'region'");
        if ($result->num_rows > 0) {
            echo "<p class='success'>✅ Region column exists in project table</p>";
            
            // Apply region updates if needed
            echo "<h2>Applying Region Updates</h2>";
            
            // Update West Region projects
            $westProjects = ['Wes_town', 'Polygon', 'Allegria', 'Allegria_res', 'Polygon_x', 'Forty_west', '6west Res', 'HUB', 'Pavlion', '6west Comm', 'caesar', 'june', 'ogami'];
            $westCount = 0;
            foreach ($westProjects as $project) {
                $stmt = $conn->prepare("UPDATE project SET region = 1 WHERE project_name = ? AND (region IS NULL OR region != 1)");
                $stmt->bind_param("s", $project);
                $stmt->execute();
                $westCount += $stmt->affected_rows;
                $stmt->close();
            }
            echo "<p class='info'>📝 Updated $westCount west region projects</p>";
            
            // Update East Region projects
            $eastProjects = ['Portal', 'SMD', 'EDNC', 'east town ETR', 'SODIC east', 'villet'];
            $eastCount = 0;
            foreach ($eastProjects as $project) {
                $stmt = $conn->prepare("UPDATE project SET region = 2 WHERE project_name = ? AND (region IS NULL OR region != 2)");
                $stmt->bind_param("s", $project);
                $stmt->execute();
                $eastCount += $stmt->affected_rows;
                $stmt->close();
            }
            echo "<p class='info'>📝 Updated $eastCount east region projects</p>";
            
        } else {
            echo "<p class='error'>❌ Region column does not exist</p>";
            echo "<p class='info'>🔧 Adding region column...</p>";
            
            if ($conn->query("ALTER TABLE project ADD COLUMN region INT DEFAULT 1")) {
                echo "<p class='success'>✅ Region column added successfully</p>";
            } else {
                echo "<p class='error'>❌ Error adding region column: " . $conn->error . "</p>";
            }
        }
        
        // Display current project regions
        echo "<h2>Current Project Regions</h2>";
        $result = $conn->query("SELECT id, project_name, region FROM project ORDER BY region, project_name");
        
        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Project Name</th><th>Region</th><th>Telegram Group</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                $regionName = $row['region'] == 1 ? 'West Region' : ($row['region'] == 2 ? 'East Region' : 'Unknown');
                $telegramGroup = $row['region'] == 1 ? 'H&S Edara West Region' : ($row['region'] == 2 ? 'H&S Edara East Region' : 'Default');
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['project_name']) . "</td>";
                echo "<td>" . $row['region'] . " ($regionName)</td>";
                echo "<td>$telegramGroup</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
        
        // Summary
        echo "<h2>Summary</h2>";
        $westTotal = $conn->query("SELECT COUNT(*) as count FROM project WHERE region = 1")->fetch_assoc()['count'];
        $eastTotal = $conn->query("SELECT COUNT(*) as count FROM project WHERE region = 2")->fetch_assoc()['count'];
        
        echo "<p><strong>West Region Projects:</strong> $westTotal</p>";
        echo "<p><strong>East Region Projects:</strong> $eastTotal</p>";
        echo "<p class='success'><strong>✅ Region-based Telegram messaging is now configured!</strong></p>";
        
        echo "<h2>How It Works</h2>";
        echo "<ul>";
        echo "<li>When a PTW is submitted, the system queries the project table to get the region</li>";
        echo "<li>If region = 1 (West), message is sent to: H&S Edara West Region (-1002005572564)</li>";
        echo "<li>If region = 2 (East), message is sent to: H&S Edara East Region (-1002285365220)</li>";
        echo "<li>If region is unknown, defaults to West Region</li>";
        echo "</ul>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    } finally {
        $conn->close();
    }
    ?>
    
</body>
</html>