<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagess/sealpic.png" type="image/png">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="index3.css">
    <title>Acceuil</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow-y: auto;
            font-family: Arial, sans-serif;
        }


        main {
            flex: 1;
            margin: 0;
            padding: 0;
        }

        .main-content {
            position: relative;
            width: 100%;
            height: 100vh;
            margin: 0;
            padding: 0;
        }

        .main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            margin: 0;
            padding: 0;
        }

        .main-text {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3em;
            font-weight: bold;
            color: var(--blue);
        }
    </style>
</head>
<body>
    
<?php
include 'config.php';

// Get the user's authorization status
$username = $_SESSION['username'];
$user_authorization = "";
$auth_result = $conn->query("SELECT autorisation FROM usersdb.users WHERE username='$username'");
if ($auth_result->num_rows > 0) {
    $row = $auth_result->fetch_assoc();
    $user_authorization = $row['autorisation'];
}

// Get list of databases
$databases = array();
$result = $conn->query("SHOW DATABASES");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $databases[] = $row['Database'];
    }
}

$valid_databases = array();
$dataPoints1 = array();
$dataPoints2 = array();

// Filter databases based on authorization (Simplified for now as 'disponibilite' table is missing)
$ignored_dbs = array('information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin');

foreach ($databases as $db) {
    // Skip system databases
    if (in_array($db, $ignored_dbs)) {
        continue;
    }

    // Since 'disponibilite' table is missing, we cannot check fine-grained authorization
    // For now, we will show all non-system databases to the user
    // Ideally, this should be replaced with a check against a central permissions table
    $should_show = true;

    if ($should_show) {
        $valid_databases[] = $db;

        // Get number of tables (Dynamic per DB)
        $tables_result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA='$db'");
        $table_count = 0;
        if ($tables_result && $tables_result->num_rows > 0) {
            $t_row = $tables_result->fetch_assoc();
            $table_count = $t_row['count'];
        }

        // Get the size of the 'disponibilite' table to match Admin graph logic
        // Use availability table size instead of total size
        $size_result = $conn->query("SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='disponibilite'");
        $size_val = 0;
        if ($size_result && $size_result->num_rows > 0) {
            $size_row = $size_result->fetch_assoc();
            $size_val = $size_row['size_mb'] ? (float)$size_row['size_mb'] : 0;
        }
            
        $dataPoints1[] = array("label" => $db, "y" => $size_val);
        $dataPoints2[] = array("label" => $db, "y" => (int)$table_count);
    }
}
// Close MySQL connection moved to end or auto-closed
// $conn->close();
?>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="acceuiluser.php" class="brand"><i class='bx bxs-data'></i>Utilisateur</a>
        <ul class="side-menu">
            <li><a href="acceuiluser.php" class="active"><i class='bx bxs-home icon'></i> Acceuil</a></li>
            <li class="divider" data-text="main"></li>
            <li>
                <a href="displayalluser.php"><i class='bx bxs-data icon'></i> base de données <i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                <?php foreach ($valid_databases as $db): ?>
                    <li><a href='displayinguser.php?dbname=<?php echo $db; ?>'><?php echo $db; ?></a></li>
                <?php endforeach; ?>
                </ul>
            </li>
        </ul>
        <div class="ads">
            <a href="logout.php" class="btn-upgrade"><i class='bx bxs-log-out-circle'></i>Se déconnecter</a>
        </div>
    </section>
    <!-- SIDEBAR -->

    <!-- NAVBAR -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <form action="#">
                <div class="form-group">
                </div>
            </form>
            <a href="#" class="nav-link">
            </a>
            <a href="#" class="nav-link">
            </a>

            	<div class="profile">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($_SESSION['picture']); ?>" alt="Profile Picture">
				<ul class="profile-link">
					<li><a href="profileuser.php"><i class='bx bxs-user-circle icon' ></i> <?php echo $_SESSION['username']; ?></a></li>
					<li><a href="logout.php"><i class='bx bxs-log-out-circle' ></i> Logout</a></li>
				</ul>
			</div>
        </nav>

        <!-- MAIN -->
        <main>
            <div class="main-content">
                <img src="imagess/homepic.jpg" alt="Welcome Image" class="main-image">
                <div class="main-text">Bienvenue <?php echo $_SESSION['username']; ?></div>
            </div>

            <div class="data">
                <div class="content-data">
                    <div class="head">
                        <h3>Bases de données disponibles</h3>
                    </div>
                     <div class="info-data">
                        <?php foreach ($valid_databases as $db): ?>
                            <!-- Get size for this specific db from dataPoints1 -->
                            <?php 
                                $current_size = 0;
                                foreach ($dataPoints1 as $point) {
                                    if ($point['label'] == $db) {
                                        $current_size = $point['y'];
                                        break;
                                    }
                                }
                            ?>
                            <a href="displayinguser.php?dbname=<?php echo urlencode($db); ?>" style="text-decoration: none; color: inherit;">
                                <div class="card">
                                    <div class="head">
                                        <div>
                                            <h2><?php echo $db; ?></h2>
                                            <p><?php echo $current_size; ?> MB</p>
                                        </div>
                                        <i class='bx bx-trending-up icon'></i>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="data">
                <div class="content-data">
                    <div class="head">
                        <h3>Statistiques des Bases de Données</h3>
                    </div>
                    <div id="chart"></div>
                </div>
            </div>
        </main>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="index3.js"></script>
    <script>
        window.serverData = {
            series1: <?php echo json_encode($dataPoints1, JSON_NUMERIC_CHECK); ?>,
            series2: <?php echo json_encode($dataPoints2, JSON_NUMERIC_CHECK); ?>
        };
    </script>
</body>
</html>
