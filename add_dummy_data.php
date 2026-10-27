<?php
include 'config.php';

$dbname = 'usersdb'; // Target database
$conn->select_db($dbname);

// Create a dummy table if not exists
$sql = "CREATE TABLE IF NOT EXISTS dummy_data (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content LONGTEXT
)";
$conn->query($sql);

echo "<h1>Adding Data to '$dbname'...</h1>";

// Insert ~5MB of data
$dummy_string = str_repeat("0123456789", 100000); // 1MB string
for ($i = 0; $i < 5; $i++) {
    $sql = "INSERT INTO dummy_data (content) VALUES ('$dummy_string')";
    if ($conn->query($sql) === TRUE) {
        echo "<p>Inserted 1MB chunk.</p>";
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}

echo "<h2>Done! Added ~5MB.</h2>";
echo "<p><a href='acceuil.php'>Go back to Dashboard to check the graph</a></p>";
?>
