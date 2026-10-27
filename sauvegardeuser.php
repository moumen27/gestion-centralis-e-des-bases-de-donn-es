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
    <link rel="stylesheet" href="index3.css">
    <title>Sauvegarder</title>
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
                require_once 'config.php';
                // Use credentials from config.php but create new connection for listing databases
                $conn_list = new mysqli($servername, $username, $password);

                // Check connection
                if ($conn_list->connect_error) {
                    die("Connection failed: " . $conn_list->connect_error);
                }

                // Get list of databases
                $databases = array();
                $result = $conn_list->query("SHOW DATABASES");
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

                    // Get number of tables
                    $tables_result = $conn_list->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA='$db'");
                    $table_count = 0;
                    if ($tables_result && $tables_result->num_rows > 0) {
                        $t_row = $tables_result->fetch_assoc();
                        $table_count = $t_row['count'];
                    }

                    // Get size of 'disponibilite' table (Admin style)
                    $result = $conn_list->query("SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='disponibilite'");
                    $size_val = 0;
                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $size_val = $row['size_mb'] ? (float)$row['size_mb'] : 0;
                    }

                    echo "<li><a href='displayinguser.php?dbname=" . urlencode($db) . "'>" . htmlspecialchars($db) . "</a></li>";

                    $dataPoints1[] = array("label" => $db, "y" => $size_val);
                    $dataPoints2[] = array("label" => $db, "y" => (int)$table_count);
                }                 
                // Close MySQL connection
                $conn_list->close();
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
		<?php
if (isset($_GET['dbname'])) {
    // Get the database name from the URL parameter
    $dbname = $_GET['dbname'];

    // Connect to MySQL
    // Connect to MySQL using config.php credentials
    require_once 'config.php';
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get database size
    $result = $conn->query("SELECT table_schema AS 'Database', 
                                  ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' 
                           FROM information_schema.tables 
                           WHERE table_schema = '$dbname'");
    $row = $result->fetch_assoc();
    $size = ($row && isset($row['Size (MB)'])) ? $row['Size (MB)'] : 0;

    // Get database status
    $result = $conn->query("SHOW TABLE STATUS FROM $dbname");
    $rows = $result->num_rows;
    if($row>1){
        $state='actif'; 
    }else{
        $state="non actif";
    }

    // Get central memory usage
    $result = $conn->query("SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_pages_%';");
    $innodb_buffer_pool_pages = 0;
    while ($row = $result->fetch_assoc()) {
        $innodb_buffer_pool_pages += $row['Value'];
    }
    $central_memory_usage = $innodb_buffer_pool_pages * 16 / 1024; 

    // Get latest save time
    $result = $conn->query("SELECT UPDATE_TIME 
                            FROM information_schema.tables 
                            WHERE TABLE_SCHEMA = '$dbname' 
                            ORDER BY UPDATE_TIME DESC 
                            LIMIT 1");
    $row = $result->fetch_assoc();
    $latest_save_time = ($row && isset($row['UPDATE_TIME'])) ? $row['UPDATE_TIME'] : 'Jamais';
    // Get CPU time
    $cpu_time = getrusage();
    // Get authorization 
        $autorisation_table = 'disponibilite'; 
        $autorisation_column = 'autorisation'; 
        $autorisation = 'non compléte'; // Default if table missing
        
        $check_table_query = "SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA='" . $conn->real_escape_string($dbname) . "' AND TABLE_NAME='$autorisation_table'";
        $check_result = $conn->query($check_table_query);
        $table_exists = false;
        if ($check_result && $row_c = $check_result->fetch_assoc()) {
             $table_exists = ($row_c['count'] > 0);
        }

        if ($table_exists) {
            $result = $conn->query("SELECT $autorisation_column FROM $autorisation_table LIMIT 1");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $autorisation = $row[$autorisation_column];
            }
        }

    // Verify User Permissions
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['username'])) {
         die("Accès refusé : Veuillez vous connecter.");
    }
    
    $conn_auth = new mysqli($servername, $username, $password, "usersdb"); // Using connection parameters from config.php
    if ($conn_auth->connect_error) {
       // Handle error
    } else {
        $stmt_u = $conn_auth->prepare("SELECT autorisation FROM users WHERE username = ?");
        $stmt_u->bind_param("s", $_SESSION['username']);
        $stmt_u->execute();
        $res_u = $stmt_u->get_result();
        $user_auth_level = 'non compléte';
        if ($row_u = $res_u->fetch_assoc()) {
            $user_auth_level = $row_u['autorisation'];
        }
        $stmt_u->close();
        $conn_auth->close();

        if ($user_auth_level !== 'compléte') {
            if ($autorisation !== 'non compléte') {
                 $conn->close();
                 die("<div style='color:red; text-align:center; margin-top:50px;'><h1>Accès Refusé</h1><p>Vous n'avez pas l'autorisation nécessaire pour accéder à cette page.</p><a href='acceuiluser.php'>Retour à l'accueil</a></div>");
            }
        }
    }

    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Close MySQL connection
    $conn->close();

  
} else {
    // If dbname parameter is not set, redirect to displayalluser.php
    header("Location: displayalluser.php");
    exit;
}
?>

		<!-- MAIN -->
		<main>
        <div class="info-data">
        <div class="card">
					<div class="head">
						<div>
                            <form method="get" action="exportuser.php" onsubmit="showLoading()">
    <input type="hidden" name="dbname" value="<?php echo htmlspecialchars($dbname); ?>">
    <button type="submit" class="btn-send"><i class='bx bxs-save'>Sauvegarder</i></button>
    <a href="restorelistuser.php?dbname=<?php echo htmlspecialchars($dbname); ?>" class="btn-send" style="text-decoration:none; margin-left:10px; background-color: #17a2b8; color: white;"><i class='bx bx-list-ul'></i> Voir les sauvegardes</a>
    <a href="acceuiluser.php" class="btn-send" style="text-decoration:none; margin-left:10px; background-color: #f0f0f0; color: #333;"><i class='bx bx-arrow-back'></i> Retourner</a>
</form>
<div id="loading" style="display:none;">Loading...</div>




			</div>
			
                            </div>
					</div>
				</div>

		</main>
	</section>
    <script>
function showLoading() {
    document.getElementById('loading').style.display = 'block';
}
</script>

	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
	<script src="index3.js"></script>
</body>
</html>