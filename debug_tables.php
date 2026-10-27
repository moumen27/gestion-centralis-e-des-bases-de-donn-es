<?php
include 'config.php';

echo "<h1>Debug Info - Tables in usersdb</h1>";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check tables in usersdb
$db = 'usersdb';
$conn->select_db($db);
$result = $conn->query("SHOW TABLES");

if ($result && $result->num_rows > 0) {
    echo "<h2>Tables in '$db':</h2><ul>";
    while($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No tables found in '$db' or database does not exist.</p>";
}

// Check tables in 'fichier' (just in case)
$db = 'fichier';
$conn->select_db($db);
$result = $conn->query("SHOW TABLES");

if ($result && $result->num_rows > 0) {
    echo "<h2>Tables in '$db':</h2><ul>";
    while($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No tables found in '$db' or database does not exist.</p>";
}
?>
