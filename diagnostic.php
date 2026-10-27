<?php
/**
 * Script de diagnostic pour vérifier la configuration de l'application SEAAL
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic SEAAL</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; border-left: 4px solid #2196F3; padding-left: 10px; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .warning { color: #ff9800; font-weight: bold; }
        .info { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: bold; }
        .status-ok { color: #4CAF50; }
        .status-error { color: #f44336; }
        .status-warning { color: #ff9800; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic de l'Application SEAAL</h1>
        
        <?php
        $errors = [];
        $warnings = [];
        $success = [];
        
        // 1. Vérification de la connexion à la base de données
        echo "<h2>1. Connexion à la Base de Données</h2>";
include 'config.php';

// Since config.php dies on error, we are connected if we reach here
echo "<p class='success'>✅ Connexion MySQL réussie</p>";
$success[] = "Connexion MySQL";
                
                // Vérifier si la base usersdb existe
                $result = $conn->query("SHOW DATABASES LIKE 'usersdb'");
                if ($result->num_rows > 0) {
                    echo "<p class='success'>✅ Base de données 'usersdb' existe</p>";
                    $success[] = "Base usersdb existe";
                    
                    // Se connecter à usersdb
                    $conn->select_db($dbname);
                    
                    // Vérifier la table users
                    $result = $conn->query("SHOW TABLES LIKE 'users'");
                    if ($result->num_rows > 0) {
                        echo "<p class='success'>✅ Table 'users' existe</p>";
                        $success[] = "Table users existe";
                        
                        // Vérifier les colonnes de la table users
                        echo "<h3>Structure de la table 'users':</h3>";
                        echo "<table>";
                        echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Défaut</th></tr>";
                        
                        $columns = $conn->query("DESCRIBE users");
                        $has_autorisation = false;
                        while ($col = $columns->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
                            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
                            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
                            echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
                            echo "</tr>";
                            
                            if ($col['Field'] === 'autorisation') {
                                $has_autorisation = true;
                            }
                        }
                        echo "</table>";
                        
                        if ($has_autorisation) {
                            echo "<p class='success'>✅ Colonne 'autorisation' présente</p>";
                            $success[] = "Colonne autorisation existe";
                        } else {
                            echo "<p class='error'>❌ Colonne 'autorisation' manquante</p>";
                            echo "<div class='info'>⚠️ Vous devez ajouter la colonne 'autorisation' à la table users. Consultez le guide fix_autorisation_column.md</div>";
                            $errors[] = "Colonne autorisation manquante";
                        }
                        
                        // Compter les utilisateurs
                        $result = $conn->query("SELECT COUNT(*) as count FROM users");
                        $row = $result->fetch_assoc();
                        echo "<p class='info'>📊 Nombre d'utilisateurs: <strong>" . $row['count'] . "</strong></p>";
                        
                        // Afficher les utilisateurs
                        if ($row['count'] > 0) {
                            echo "<h3>Utilisateurs dans la base:</h3>";
                            echo "<table>";
                            echo "<tr><th>ID</th><th>Username</th><th>Autorisation</th></tr>";
                            
                            $users_query = $has_autorisation ? 
                                "SELECT id, username, autorisation FROM users" : 
                                "SELECT id, username FROM users";
                            
                            $users = $conn->query($users_query);
                            while ($user = $users->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['autorisation'] ?? 'N/A') . "</td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        }
                        
                    } else {
                        echo "<p class='error'>❌ Table 'users' n'existe pas</p>";
                        echo "<div class='info'>⚠️ Vous devez importer le fichier usersdb_complet.sql via phpMyAdmin</div>";
                        $errors[] = "Table users manquante";
                    }
                    
                } else {
                    echo "<p class='error'>❌ Base de données 'usersdb' n'existe pas</p>";
                    echo "<div class='info'>⚠️ Vous devez créer la base et importer usersdb_complet.sql via phpMyAdmin</div>";
                    $errors[] = "Base usersdb manquante";
                }

            

        
        // 2. Vérification des fichiers critiques
        echo "<h2>2. Fichiers de l'Application</h2>";
        $critical_files = [
            'login.php' => 'Page de connexion',
            'acceuil.php' => 'Accueil admin',
            'acceuiluser.php' => 'Accueil utilisateur',
            'usersprofiles.php' => 'Profils utilisateurs',
            'displayall.php' => 'Liste des bases (admin)',
            'displayalluser.php' => 'Liste des bases (user)',
            'basestyle.css' => 'Feuille de style',
            'index3.js' => 'Script JavaScript'
        ];
        
        echo "<table>";
        echo "<tr><th>Fichier</th><th>Description</th><th>Status</th></tr>";
        foreach ($critical_files as $file => $desc) {
            $exists = file_exists($file);
            $status = $exists ? "<span class='status-ok'>✅ Existe</span>" : "<span class='status-error'>❌ Manquant</span>";
            echo "<tr><td>$file</td><td>$desc</td><td>$status</td></tr>";
            
            if (!$exists) {
                $errors[] = "Fichier manquant: $file";
            }
        }
        echo "</table>";
        
        // 3. Vérification des redirections problématiques
        echo "<h2>3. Redirections Problématiques</h2>";
        $problematic_redirects = [
            'acceuiluser.php' => 'a.php',
            'displaying.php' => 'a.php',
            'displayinguser.php' => 'a.php',
            'modifier.php' => 'a.php',
            'sauvegarde.php' => 'a.php',
            'sauvegardeuser.php' => 'a.php'
        ];
        
        $has_redirect_issues = false;
        echo "<table>";
        echo "<tr><th>Fichier Source</th><th>Redirige vers</th><th>Problème</th></tr>";
        foreach ($problematic_redirects as $source => $target) {
            if (file_exists($source)) {
                $target_exists = file_exists($target);
                if (!$target_exists) {
                    echo "<tr>";
                    echo "<td>$source</td>";
                    echo "<td>$target</td>";
                    echo "<td class='status-error'>❌ Fichier cible inexistant</td>";
                    echo "</tr>";
                    $warnings[] = "Redirection cassée: $source -> $target";
                    $has_redirect_issues = true;
                }
            }
        }
        echo "</table>";
        
        if (!$has_redirect_issues) {
            echo "<p class='info'>ℹ️ Note: Certains fichiers redirigent vers 'a.php' qui n'existe pas. Cela peut causer des erreurs.</p>";
        }
        
        // 4. Configuration PHP
        echo "<h2>4. Configuration PHP</h2>";
        echo "<table>";
        echo "<tr><th>Paramètre</th><th>Valeur</th></tr>";
        echo "<tr><td>Version PHP</td><td>" . phpversion() . "</td></tr>";
        echo "<tr><td>Extension MySQLi</td><td>" . (extension_loaded('mysqli') ? '<span class="status-ok">✅ Activée</span>' : '<span class="status-error">❌ Désactivée</span>') . "</td></tr>";
        echo "<tr><td>Extension GD (images)</td><td>" . (extension_loaded('gd') ? '<span class="status-ok">✅ Activée</span>' : '<span class="status-warning">⚠️ Désactivée</span>') . "</td></tr>";
        echo "</table>";
        
        // Résumé final
        echo "<h2>📋 Résumé</h2>";
        echo "<table>";
        echo "<tr><th>Catégorie</th><th>Nombre</th></tr>";
        echo "<tr><td>✅ Succès</td><td class='status-ok'>" . count($success) . "</td></tr>";
        echo "<tr><td>⚠️ Avertissements</td><td class='status-warning'>" . count($warnings) . "</td></tr>";
        echo "<tr><td>❌ Erreurs</td><td class='status-error'>" . count($errors) . "</td></tr>";
        echo "</table>";
        
        if (count($errors) === 0 && count($warnings) === 0) {
            echo "<div style='background: #4CAF50; color: white; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
            echo "<h3 style='margin: 0; color: white;'>🎉 Tout fonctionne correctement !</h3>";
            echo "<p style='margin: 10px 0 0 0;'>Vous pouvez maintenant utiliser l'application.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #ff9800; color: white; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
            echo "<h3 style='margin: 0; color: white;'>⚠️ Des problèmes ont été détectés</h3>";
            echo "<p style='margin: 10px 0 0 0;'>Veuillez corriger les erreurs ci-dessus avant d'utiliser l'application.</p>";
            echo "</div>";
        }
        
        if (isset($conn)) {
            $conn->close();
        }
        ?>
        
        <div style="margin-top: 30px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
            <p><strong>💡 Actions recommandées:</strong></p>
            <ol>
                <li>Si la colonne 'autorisation' manque, consultez <code>fix_autorisation_column.md</code></li>
                <li>Vérifiez que XAMPP est démarré (Apache + MySQL)</li>
                <li>Testez la connexion sur <a href="login.php">login.php</a></li>
            </ol>
        </div>
    </div>
</body>
</html>
