<?php
// update_authorization.php

// Check if required POST parameters are set 
if (isset($_POST['dbname']) && isset($_POST['current_autorisation'])) {
    // Get the parameters from POST request
    $dbname = $_POST['dbname'];
    $current_autorisation = $_POST['current_autorisation'];

    // Determine the new authorization value
    $new_autorisation = ($current_autorisation == 'complete') ? 'non complete' : 'complete';

    // Connect to MySQL
    include 'config.php';
    $conn->select_db($dbname);

    // Update the authorization value in the database
    $autorisation_table = 'disponibilite'; // Replace with your actual table name
    $autorisation_column = 'autorisation'; // Replace with your actual column name
    $sql = "UPDATE $autorisation_table SET $autorisation_column = '$new_autorisation' LIMIT 1";

    if ($conn->query($sql) === TRUE) {
        echo "Autorisation est bien modifiée";
    } else {
        echo "Error updating autorisation: " . $conn->error;
    }

    // Close MySQL connection
    $conn->close();

    // Redirect back to the display page
    header("Location: modifier.php?dbname=" . urlencode($dbname));
    exit;

} else {
    echo "Required parameters not set.";
}
?>
