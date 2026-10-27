<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Updates</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Update Dates for Database: <?php echo htmlspecialchars($_GET['dbname']); ?></h2>

<table>
    <tr>
        <th>Table Name</th>
        <th>Last Update Time</th>
    </tr>
    <?php
    // Check if dbname parameter is set in the URL
    if (isset($_GET['dbname'])) {
        // Get the database name from the URL parameter
        $dbname = $_GET['dbname'];

        // Connect to MySQL
        include 'config.php';
        $conn->select_db($dbname);

        // Execute SQL query to get the update times of all tables in the database
        $sql = "SELECT TABLE_NAME, UPDATE_TIME 
                FROM information_schema.tables 
                WHERE TABLE_SCHEMA = '$dbname'
                ORDER BY UPDATE_TIME DESC";
        $result = $conn->query($sql);

        // Display the update times
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row['TABLE_NAME'] . "</td><td>" . $row['UPDATE_TIME'] . "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='2'>No updates found.</td></tr>";
        }

        // Close MySQL connection
        $conn->close();
    } else {
        // If dbname parameter is not set, redirect to an appropriate page
        header("Location: index.php");
        exit;
    }
    ?>
</table>

</body>
</html>
