<?php
// Connect to MySQL
include 'config.php';

// Get list of databases with 'disponibilite' table
$databases = array();
$result = $conn->query("SHOW DATABASES");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $databases[] = $row['Database'];
    }
}

// Get database information for databases with 'disponibilite' table
foreach ($databases as $db) {
    // Check if the 'disponibilite' table exists in the current database
    $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='disponibilite'");
    $row = $result->fetch_assoc();
    $table_exists = $row['count'] > 0;

    if ($table_exists) {
        // If the 'disponibilite' table exists, retrieve the last update time
        $result = $conn->query("SELECT TABLE_NAME, UPDATE_TIME FROM information_schema.tables WHERE TABLE_SCHEMA='$db' ORDER BY UPDATE_TIME DESC LIMIT 1");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Generate link to the backup page with database name as parameter
            echo "<tr><td><a href='backup_page.php?database=" . urlencode($db) . "'>Le dernier sauvgarde</a></td><td>" . $row['UPDATE_TIME'] . "</td></tr>";
        }
    }
}

// Close MySQL connection
$conn->close();
?>
