<?php
/**
 * Script PHP pour corriger automatiquement la colonne 'picture' de la table 'users'
 * Exécutez ce fichier une seule fois pour appliquer la correction
 */

include 'config.php';

echo "<h1>Correction de la colonne 'picture'</h1>";

// Vérifier la connexion
if ($conn->connect_error) {
    die("<p style='color:red;'>Erreur de connexion: " . $conn->connect_error . "</p>");
}

// Sélectionner la base de données
$conn->select_db($dbname);

// Modifier la colonne 'picture' pour accepter des BLOB
$sql = "ALTER TABLE users MODIFY COLUMN picture MEDIUMBLOB";

echo "<p>Exécution de la requête SQL...</p>";
echo "<pre>$sql</pre>";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green;'><strong>✓ Succès!</strong> La colonne 'picture' a été modifiée en MEDIUMBLOB.</p>";
    echo "<p>Vous pouvez maintenant ajouter des utilisateurs avec des photos.</p>";
    
    // Afficher la structure de la table pour confirmation
    echo "<h2>Structure actuelle de la table 'users':</h2>";
    $result = $conn->query("DESCRIBE users");
    
    if ($result) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($row['Type']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
} else {
    echo "<p style='color:red;'><strong>✗ Erreur:</strong> " . $conn->error . "</p>";
    
    // Si l'erreur est que la colonne n'existe pas, afficher les colonnes existantes
    if (strpos($conn->error, "Unknown column") !== false) {
        echo "<h2>Colonnes existantes dans la table 'users':</h2>";
        $result = $conn->query("DESCRIBE users");
        
        if ($result) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['Field']) . " (" . htmlspecialchars($row['Type']) . ")</li>";
            }
            echo "</ul>";
        }
    }
}

$conn->close();

echo "<hr>";
echo "<p><a href='adduser.php'>← Retour à l'ajout d'utilisateur</a></p>";
echo "<p><a href='usersprofiles.php'>← Voir la liste des utilisateurs</a></p>";
?>
