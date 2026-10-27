<?php
include 'config.php';

echo "<h1>Installing 'disponibilite' table...</h1>";

// 1. Get Databases
$databases = array();
$result = $conn->query("SHOW DATABASES");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $databases[] = $row['Database'];
    }
}

$ignored_dbs = array('information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin');

foreach ($databases as $db) {
    if (in_array($db, $ignored_dbs)) continue;

    echo "<p>Checking <strong>$db</strong>... ";
    
    // Select DB
    $conn->select_db($db);
    
    // Create Table
    $sql = "CREATE TABLE IF NOT EXISTS disponibilite (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        autorisation VARCHAR(50) DEFAULT 'non complete',
        last_backup_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_backup_user VARCHAR(50) DEFAULT 'System'
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'disponibilite' checked/created. ";
        
        // Insert Default Row if Empty
        $check = $conn->query("SELECT count(*) as count FROM disponibilite");
        $row = $check->fetch_assoc();
        if ($row['count'] == 0) {
             $init = "INSERT INTO disponibilite (autorisation, last_backup_user) VALUES ('non complete', 'Sahbi Nadjem')";
             if ($conn->query($init) === TRUE) {
                 echo "Inserted default row.";
             } else {
                 echo "Error inserting default: " . $conn->error;
             }
        }
        echo "<span style='color:green'>OK</span>";
    } else {
        echo "Error creating table: " . $conn->error;
    }
    echo "</p>";
}

$conn->close();
echo "<h2>Installation Complete.</h2>";
?>
