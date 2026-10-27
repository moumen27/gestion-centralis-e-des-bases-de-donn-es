<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagess/sealpic.png" type="image/png">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="utilisateur.css">
    <title>Bases de données</title>
</head>
<body>
    
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="acceuiluser.php" class="brand"><i class='bx bxs-data'></i>Utilisateur</a>
        <ul class="side-menu">
            <li><a href="acceuiluser.php" ><i class='bx bxs-home icon'></i> Acceuil</a></li>
            <li class="divider" data-text="main"></li>
            <li>
                <a href="displayalluser.php"class="active"><i class='bx bxs-data icon'></i> base de données <i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                <?php
                // Connect to MySQL
                // Connect to MySQL
                require_once 'config.php';
                $conn = new mysqli($servername, $username, $password);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Get list of databases
                $databases = array();
                $result = $conn->query("SHOW DATABASES");
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $databases[] = $row['Database'];
                    }
                }

                // Get database information
                $ignored_dbs = array('information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin');

                foreach ($databases as $db) {
                    if (in_array($db, $ignored_dbs)) {
                        continue;
                    }

                    // Get generic stats
                    $result = $conn->query("SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_pages_%';");
                    $innodb_buffer_pool_pages = 0;
                    while ($row = $result->fetch_assoc()) {
                        $innodb_buffer_pool_pages += $row['Value'];
                    }
                    $central_memory_usage = $innodb_buffer_pool_pages * 16 / 1024;

                    $result = $conn->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE TABLE_SCHEMA='$db'");
                    $size_val = 0;
                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $size_val = $row['size_mb'] ? (float)$row['size_mb'] : 0;
                    }

                    echo "<li><a href='displayinguser.php?dbname=" . urlencode($db) . "'>" . htmlspecialchars($db) . "</a></li>";

                    $dataPoints1[] = array("label" => $db, "y" => $size_val);
                    $dataPoints2[] = array("label" => $db, "y" => $central_memory_usage);
                }                 
                // Close MySQL connection
                $conn->close();
                ?>
                </ul>
    </section>

    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <form action="#">
                <div class="form-group">
                    <!--<input type="text" placeholder="Search...">
                    <i class='bx bx-search icon'></i>-->
                </div>
            </form>
            <a href="#" class="nav-link">
                <!--<i class='bx bxs-bell icon'></i>
                <span class="badge">5</span>-->
            </a>
            <a href="#" class="nav-link">
                <!--<i class='bx bxs-message-square-dots icon'></i>
                <span class="badge">8</span>-->
            </a>

			<div class="profile">
            <?php echo ' <img src="data:image/jpeg;base64,' . base64_encode($_SESSION["picture"]) . '"/>'?>
				<ul class="profile-link">
					<li><i class='bx bxs-user-circle icon' ></i> <?php echo $_SESSION['username']; ?></a></li>
					<li><a href="logout.php"><i class='bx bxs-log-out-circle' ></i> Logout</a></li>
				</ul>
			</div>
        </nav>
		<!-- MAIN -->
        <main> 
            <?php
            // Check if dbname parameter is set in the URL
            if (isset($_GET['dbname'])) {
                $dbname = $_GET['dbname'];

                // Get all backup files for this database
                $backupDir = 'backups/';
                $backupFiles = array();
                
                if (is_dir($backupDir)) {
                    $files = scandir($backupDir);
                    foreach ($files as $file) {
                        // Check if file starts with the database name and ends with .sql
                        if (strpos($file, $dbname . '_') === 0 && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                            $filePath = $backupDir . $file;
                            $backupFiles[] = array(
                                'name' => $file,
                                'path' => $filePath,
                                'date' => date("Y-m-d H:i:s", filemtime($filePath)),
                                'size' => filesize($filePath)
                            );
                        }
                    }
                }
                
                // Sort by date (newest first)
                usort($backupFiles, function($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });
            ?>
            <h1 class="title">Les derniers sauvegardes</h1>
            <ul class="breadcrumbs">
                <li><a href="acceuiluser.php">Acceuil</a></li>
                <li class="divider">/</li>
                <li><a href="#" class="active"> <?php echo htmlspecialchars($_GET['dbname']); ?></a></li>
            </ul>
            <div class="table-data">
                <div class="order">
                    <table>
                        <thead>
                            <tr>
                                <th>Fichier</th>
                                <th>Date</th>
                                <th>Taille</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Display the backup files
                            if (count($backupFiles) > 0) {
                                foreach ($backupFiles as $backup) {
                                    $sizeInMB = round($backup['size'] / 1024 / 1024, 2);
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($backup['name']) . "</td>";
                                    echo "<td>" . $backup['date'] . "</td>";
                                    echo "<td>" . $sizeInMB . " MB</td>";
                                    echo "<td>";
                                    echo "<a href='" . $backup['path'] . "' download class='btn-send' style='margin-right: 10px; text-decoration: none;'><i class='bx bxs-download'></i> Télécharger</a>";
                                    echo "<button class='btn-send delete-btn' data-file='" . htmlspecialchars($backup['name']) . "' style='background-color: #dc3545;'><i class='bx bxs-trash'></i> Supprimer</button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align: center;'>Aucune sauvegarde trouvée pour cette base de données.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
            } else {
                // If dbname parameter is not set, redirect to an appropriate page
                header("Location: acceuiluser.php");
                exit;
            }
            ?>
        </main>
    </section>
    <script src="index3.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.delete-btn').click(function() {
                var fileName = $(this).data('file');
                var dbname = '<?php echo $dbname; ?>';
                
                if (confirm('Êtes-vous sûr de vouloir supprimer cette sauvegarde ?')) {
                    $.ajax({
                        url: 'delete_backup.php',
                        type: 'POST',
                        dataType: 'json',
                        data: { file: fileName, dbname: dbname },
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                location.reload();
                            } else {
                                alert('Erreur: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Erreur de communication avec le serveur');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
