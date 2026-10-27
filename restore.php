<?php
if (isset($_POST['filename']) && isset($_POST['dbname'])) {
    $filename = $_POST['filename'];
    $dbname = $_POST['dbname'];

    // Security check: validate filename and dbname
    if (preg_match('/^[a-zA-Z0-9_.-]+$/', $filename) && strpos($filename, '..') === false &&
        preg_match('/^[a-zA-Z0-9_]+$/', $dbname)) { // dbname usually strictly alphanumeric + underscore
        
        $backupDir = __DIR__ . '/backups/';
        $filePath = $backupDir . $filename;

        if (file_exists($filePath)) {
            include 'config.php';
            
            // MySQL Bin path (from export.php)
            $mysqlBinDir = "C:\\xampp\\mysql\\bin";
            $mysqlPath = "$mysqlBinDir\\mysql.exe";

            if (!file_exists($mysqlPath)) {
                echo "Erreur: Exécutable mysql introuvable.";
                exit;
            }

            // Command: mysql -u user -p pass dbname < file.sql
            // Note: Password usage on command line can be insecure but it's consistent with existing export.php logic.
            // Using < for redirection in shell.
            $command = "cd /d \"$mysqlBinDir\" && .\\mysql --host=$servername --user=$username --password=$password $dbname < \"$filePath\"";
            
            exec($command . ' 2>&1', $output, $result);

            if ($result === 0) {
                echo "Restauration effectuée avec succès.";
                 // Update disponibilite table if exists
                $conn->select_db($dbname);
                 // We don't necessarily update "last_backup_time" on restore, but maybe "last_restore"?
                 // For now, let's just confirm success.
            } else {
                echo "Erreur lors de la restauration: " . implode("\n", $output);
            }
            $conn->close();

        } else {
            echo "Fichier de sauvegarde introuvable.";
        }
    } else {
        echo "Paramètres invalides.";
    }
} else {
    echo "Paramètres manquants.";
}
?>
