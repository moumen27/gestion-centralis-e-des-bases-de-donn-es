<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if (isset($_GET['dbname'])) {
    $dbname = $_GET['dbname'];

    // Chemin pour stocker le fichier de sauvegarde
    $backupFile = "backups/{$dbname}_" . date("Y-m-d_H-i-s") . ".sql";

    // Créer le répertoire backups s'il n'existe pas
    if (!file_exists('backups')) {
        mkdir('backups', 0777, true);
    }

    try {
        // Connexion à la base de données
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Ouvrir le fichier pour écriture
        $handle = fopen($backupFile, 'w+');
        if (!$handle) {
            throw new Exception("Cannot create backup file");
        }

        // En-tête du fichier SQL
        $return = "-- MySQL Dump\n";
        $return .= "-- Database: {$dbname}\n";
        $return .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
        $return .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $return .= "SET time_zone = \"+00:00\";\n\n";
        fwrite($handle, $return);

        // Récupérer toutes les tables
        $tables = array();
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }

        // Pour chaque table
        foreach ($tables as $table) {
            // Structure de la table
            $return = "\n\n-- --------------------------------------------------------\n\n";
            $return .= "-- Structure de la table `{$table}`\n\n";
            $return .= "DROP TABLE IF EXISTS `{$table}`;\n";
            fwrite($handle, $return);

            $result2 = $conn->query("SHOW CREATE TABLE `{$table}`");
            $row2 = $result2->fetch_row();
            fwrite($handle, $row2[1] . ";\n\n");

            // Données de la table
            $result = $conn->query("SELECT * FROM `{$table}`");
            $num_rows = $result->num_rows;

            if ($num_rows > 0) {
                $return = "-- Contenu de la table `{$table}`\n\n";
                fwrite($handle, $return);

                while ($row = $result->fetch_assoc()) {
                    $return = "INSERT INTO `{$table}` VALUES(";
                    $values = array();
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . $conn->real_escape_string($value) . "'";
                        }
                    }
                    $return .= implode(',', $values) . ");\n";
                    fwrite($handle, $return);
                }
            }
        }

        fclose($handle);
        // Update last_backup info in 'disponibilite' table
        // First check if table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'disponibilite'");
        if ($checkTable && $checkTable->num_rows > 0) {
            // Check if columns exist
            $checkCols = $conn->query("SHOW COLUMNS FROM disponibilite LIKE 'last_backup_time'");
            if ($checkCols && $checkCols->num_rows == 0) {
                $conn->query("ALTER TABLE disponibilite ADD COLUMN last_backup_time DATETIME DEFAULT NULL");
                $conn->query("ALTER TABLE disponibilite ADD COLUMN last_backup_user VARCHAR(100) DEFAULT 'Inconnu'");
            }
            
            $currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Admin');
            $currentTime = date('Y-m-d H:i:s');
            
            // Assuming there is only one row or we stick it in the first one if multiple (usually config table has 1 row)
            // Or we insert if empty?
            $checkRow = $conn->query("SELECT COUNT(*) as c FROM disponibilite");
            $rowC = $checkRow->fetch_assoc();
            if ($rowC['c'] > 0) {
                 $stmt = $conn->prepare("UPDATE disponibilite SET last_backup_time = ?, last_backup_user = ?");
                 $stmt->bind_param("ss", $currentTime, $currentUser);
                 $stmt->execute();
                 $stmt->close();
            } else {
                 // Table empty? Should probably insert. But usually it has 'autorisation'.
                 $stmt = $conn->prepare("INSERT INTO disponibilite (last_backup_time, last_backup_user) VALUES (?, ?)");
                 $stmt->bind_param("ss", $currentTime, $currentUser);
                 $stmt->execute();
                 $stmt->close();
            }
        }

        $conn->close();

        // Vérifier que le fichier a été créé et n'est pas vide
        if (file_exists($backupFile) && filesize($backupFile) > 0) {
            header("Location: success.php?message=Sauvegarde%20effectuée&file=" . urlencode($backupFile));
            exit;
        } else {
            throw new Exception("Le fichier de sauvegarde est vide");
        }

    } catch (Exception $e) {
        error_log("Erreur de sauvegarde: " . $e->getMessage());
        header("Location: failure.php?message=Sauvegarde%20échouée&error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: acceuil.php");
    exit;
}
?>
