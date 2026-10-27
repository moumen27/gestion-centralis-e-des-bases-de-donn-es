<?php
if (isset($_POST['filename'])) {
    $filename = $_POST['filename'];

    // Security check: validate filename
    // Start with strict validation: only allow alphanumeric, dot, underscore, dash
    // And ensure no parent directory reference
    if (preg_match('/^[a-zA-Z0-9_.-]+$/', $filename) && strpos($filename, '..') === false) {
        
        $backupDir = __DIR__ . '/backups/';
        $filePath = $backupDir . $filename;

        // Check if file exists and is really a file
        if (file_exists($filePath) && is_file($filePath)) {
            if (unlink($filePath)) {
                echo "Fichier '$filename' supprimé avec succès.";
            } else {
                echo "Erreur lors de la suppression du fichier.";
            }
        } else {
            echo "Fichier introuvable.";
        }
    } else {
        echo "Nom de fichier invalide.";
    }

} else {
    echo "Nom de fichier manquant.";
}
?>
