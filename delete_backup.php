<?php
session_start();

if (isset($_POST['file']) && isset($_POST['dbname'])) {
    $filename = $_POST['file'];
    $dbname = $_POST['dbname'];
    
    // Sécurité : vérifier que le fichier commence bien par le nom de la base
    if (strpos($filename, $dbname . '_') !== 0) {
        echo json_encode(['success' => false, 'message' => 'Nom de fichier invalide']);
        exit;
    }
    
    // Sécurité : vérifier que le fichier est bien un .sql
    if (pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
        echo json_encode(['success' => false, 'message' => 'Type de fichier invalide']);
        exit;
    }
    
    $filePath = 'backups/' . $filename;
    
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            echo json_encode(['success' => true, 'message' => 'Sauvegarde supprimée avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Fichier introuvable']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
}
?>
