<?php
require_once "constants/dbconnect.php";

if (isset($_GET['permit_number'])) {
    $permit_number = $conn->real_escape_string($_GET['permit_number']);
    $sql = "SELECT action, action_by, action_date, notes FROM ptw_history WHERE permit_number = ? ORDER BY action_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $permit_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div>";
        while ($row = $result->fetch_assoc()) {
            echo "<div class='history-item'>";
            echo "Action: " . htmlspecialchars($row['action']) . "<br>";
            echo "By: " . htmlspecialchars($row['action_by']) . "<br>";
            echo "Date: " . htmlspecialchars($row['action_date']) . "<br>";
            echo "Notes: " . htmlspecialchars($row['notes']) . "<br>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p>No history available.</p>";
    }

    $stmt->close();
}

$conn->close();
?>