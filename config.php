<?php
$servername = "localhost";
$username = "root";
$password = ""; // Mot de passe vide pour XAMPP par défaut
// Conserver $dbname si déjà défini par la page appelante
if (!isset($dbname) || empty($dbname)) {
    $dbname = "usersdb";
}

$conn = new mysqli($servername, $username, $password, $dbname);
$mysqli = $conn; // Alias for compatibility with code using $mysqli

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
