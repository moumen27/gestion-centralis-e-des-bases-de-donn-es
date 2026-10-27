<?php
include 'config.php';

echo "<h1>Debug Info</h1>";

// 1. Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "<p>Connected successfully to MySQL.</p>";

// 2. Show Databases
$result = $conn->query("SHOW DATABASES");
if (!$result) {
    die("SHOW DATABASES failed: " . $conn->error);
}

$databases = [];
if ($result->num_rows > 0) {
    echo "<h2>Found Databases:</h2><ul>";
    while($row = $result->fetch_assoc()) {
        $db = $row['Database'];
        echo "<li>" . $db . "</li>";
        $databases[] = $db;
    }
    echo "</ul>";
} else {
    echo "<p>No databases found.</p>";
}

// 3. Check for 'disponibilite' table in each db
echo "<h2>Checking for 'disponibilite' table:</h2>";
foreach ($databases as $db) {
    echo "<p>Checking <strong>$db</strong>... ";
    
    // Explicitly select DB first (optional but safer)
    $conn->select_db($db);
    
    // Check using information_schema
    $query = "SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='disponibilite'";
    $res = $conn->query($query);
    if ($res) {
        $row = $res->fetch_assoc();
        $count = $row['count'];
        echo "Count: $count - ";
        if ($count > 0) {
             echo "<span style='color:green'>FOUND</span>";
        } else {
             echo "<span style='color:red'>NOT FOUND</span>";
        }
    } else {
        echo "Query failed: " . $conn->error;
    }
    echo "</p>";
}
?>
