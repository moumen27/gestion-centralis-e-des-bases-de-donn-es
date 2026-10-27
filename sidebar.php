<?php
// Sauvegarder les variables de la page parente pour éviter tout écrasement accidentel
$__saved_dbname = isset($dbname) ? $dbname : null;

// Ensure config is included
include_once 'config.php';

// Restaurer $dbname si la page parente l'avait défini
if ($__saved_dbname !== null) {
    $dbname = $__saved_dbname;
}

// Ensure a valid connection exists
if (!isset($conn) || !($conn instanceof mysqli) || !@$conn->ping()) {
    $conn = new mysqli($servername, $username, $password);
}

// Check if we need to fetch databases
if (!isset($header_databases) || empty($header_databases)) {
    $header_databases = array();
    $result = $conn->query("SHOW DATABASES");
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $header_databases[] = $row['Database'];
        }
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
$is_db_active = in_array($current_page, array('displayall.php', 'displaying.php', 'restorelist.php', 'modifier.php', 'sauvegarde.php'));
?>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="acceuil.php" class="brand"><i class='bx bxs-data'></i>Admin</a>
        <ul class="side-menu">
            <li><a href="acceuil.php" class="<?php echo ($current_page == 'acceuil.php') ? 'active' : ''; ?>"><i class='bx bxs-home icon'></i> Acceuil</a></li>
            <li class="divider" data-text="main"></li>
            <li>
                <a href="displayall.php" class="<?php echo $is_db_active ? 'active' : ''; ?>"><i class='bx bxs-data icon'></i> base de données <i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                    <?php
                    // Ignorer uniquement les bases internes de MySQL
                    $ignored_dbs_sidebar = array('information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin');

                    foreach ($header_databases as $db) {
                         if (in_array(strtolower($db), $ignored_dbs_sidebar)) {
                             continue;
                         }
                         echo "<li><a href='displaying.php?dbname=" . urlencode($db) . "'>" . htmlspecialchars($db) . "</a></li>";
                    }                 
                    ?>
                </ul>
            </li>
            <li><a href="usersprofiles.php" class="<?php echo ($current_page == 'usersprofiles.php') ? 'active' : ''; ?>"><i class='bx bxs-group icon'></i> Utilisateur</a></li>
        </ul>
        <div class="ads">
            <a href="logout.php" class="btn-upgrade"><i class='bx bxs-log-out-circle'></i>Se déconnecter</a>
        </div>
    </section>
    <!-- SIDEBAR -->
<?php
if ($__saved_dbname !== null) {
    $dbname = $__saved_dbname;
}
unset($__saved_dbname);
?>
