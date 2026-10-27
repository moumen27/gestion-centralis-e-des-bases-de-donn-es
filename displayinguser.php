<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="imagess/sealpic.png" type="image/png">
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="index3.css">
	<title>Base de données</title>
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
                <ul class="side-dropdown">>

   
<?php
    require_once 'config.php';
    // config.php connects to 'usersdb' by default. 
    // For listing databases, we can use the same connection if the user has permissions, 
    // OR create a new one. The sidebar usually just needs a connection.
    // Let's use the credentials from config to create a list connection if needed, 
    // OR just use $conn from config.php.
    // Given the previous pattern in other files, let's just use $conn from config.php if it works, 
    // but config.php selects a specific DB.
    // Let's create a specific connection for listing to be safe and consistent with other files.
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

    // Get database information for databases with 'disponibilite' table
    foreach ($databases as $db) {
        // Check if the 'disponibilite' table exists in the current database
        $result = $conn_list->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='disponibilite'");
        $row = $result->fetch_assoc();
        $table_exists = $row['count'] > 0;

        if ($table_exists) {
            // If the 'disponibilite' table exists, retrieve the last update time
            $result = $conn_list->query("SELECT TABLE_NAME, UPDATE_TIME FROM information_schema.tables WHERE TABLE_SCHEMA='$db' ORDER BY UPDATE_TIME DESC LIMIT 1");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();                
                echo "<li><a href='displayinguser.php?dbname=$db'>" . $db . "</a></li>";
            }
        }
    }                 
     // Close MySQL connection
    $conn_list->close();
    ?>
                </ul>
			</li>
	</section>
	<!-- SIDEBAR -->

	<!-- NAVBAR -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu toggle-sidebar' ></i>
			<form action="#">
				<div class="form-group"><!--
					<input type="text" placeholder="Search...">
					<i class='bx bx-search icon' ></i>-->
				</div>
			</form>
			<a href="#" class="nav-link"><!--
				<i class='bx bxs-bell icon' ></i>
				<span class="badge">5</span>-->
			</a>
			<a href="#" class="nav-link"><!--
				<i class='bx bxs-message-square-dots icon' ></i>
				<span class="badge">8</span>-->
			</a>


		</nav>
<?php
if (isset($_GET['dbname'])) {
    $dbname = $_GET['dbname'];

    // Connect to MySQL
    // config.php is already included above (via require_once), so $conn (to usersdb) and credentials are available.
    // We want to connect to $dbname.
    // We can just select the db on a new connection or the existing one.
    // Let's create a new connection to be safe as we are in the main content area and might want to avoid side effects.
    // actually, config.php creates $conn. 
    // Let's use a new connection for the specific DB to be clean.
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
    $size = $row['Size (MB)'];

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
    // Get authorization and backup details from the database
    $autorisation = 'Inconnu'; 
    $last_backup_time = 'Jamais';
    $last_backup_user = 'Inconnu';

    $check_table = $conn->query("SHOW TABLES LIKE 'disponibilite'");
    
    if ($check_table && $check_table->num_rows > 0) {
        $result = $conn->query("SELECT * FROM disponibilite LIMIT 1");
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $autorisation = isset($row['autorisation']) ? $row['autorisation'] : 'Inconnu';
            $last_backup_time = isset($row['last_backup_time']) ? $row['last_backup_time'] : 'Jamais';
            $last_backup_user = isset($row['last_backup_user']) ? $row['last_backup_user'] : 'Inconnu';
        }
    }

    // Verify User Permissions
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
         // Redirect or die if not logged in
         die("Accès refusé : Veuillez vous connecter.");
    }

    // Fetch User Authorization Level
    // We need to query the 'usersdb' (default connection from config.php) for the user's rights.
    // However, $conn currently points to $dbname because of $conn = new mysqli(..., $dbname);
    // We need a connection to usersdb or just use a new one.
    // Let's use a temporary connection to usersdb to check user rights.
    
    $conn_auth = new mysqli($servername, $username, $password, "usersdb"); // Assuming usersdb is the central DB
    if ($conn_auth->connect_error) {
        // Fallback or error
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

        // Enforce Logic
        // If User is 'non compléte', they can ONLY view DBs that are 'non compléte'
        if ($user_auth_level !== 'compléte') {
            // Note: $autorisation fetched above is the DB's authorization
            if ($autorisation !== 'non compléte') {
                 // Access Denied
                 $conn->close();
                 // header("Location: acceuiluser.php?error=AccessDenied"); // Optional redirect
                 die("<div style='color:red; text-align:center; margin-top:50px;'><h1>Accès Refusé</h1><p>Vous n'avez pas l'autorisation nécessaire pour accéder à cette base de données.</p><a href='acceuiluser.php'>Retour à l'accueil</a></div>");
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
	<main>
			<h1 class="title">Desription de la base de données </h1>
			<ul class="breadcrumbs">
				<li><a href="acceuil.php">Acceuil</a></li>
				<li class="divider">/</li>
				<li><a href="displayinguser.php" class="active"><?php echo  $dbname ; ?></a></li>
			</ul>
		<div class="info-data">
            <div class="card">
					<div class="head">
						<div>
							<h2>Espace mémoire occupé</h>
						</div>
                    </div>					
                    <?php echo "<p>: $size MB</p>";?>

				</div>
				<div class="card">
					<div class="head">
						<div>
							<h2>Utilisation de mémoire centrale</h>
						</div>
                    </div>					
                    <?php echo "<h4> $central_memory_usage MB<h4>";?>

				</div>
				<div class="card">
					<div class="head">
						<div>
							<h2>Temp CPU</h2>							
						</div>
					</div>
                    <?php echo "<p> " . ($cpu_time['ru_utime.tv_sec'] + $cpu_time['ru_stime.tv_sec']) . " seconds</p>";?>
				</div>

                <div class="card">
    <div class="head">
        <div>
            <h2><a href="restorelistuser.php?dbname=<?php echo urlencode($dbname); ?>">Le dernier sauvegarde</a></h2>
            <h4><?php echo htmlspecialchars($last_backup_user); ?></h4>
            <?php echo "<h4>$last_backup_time</h4>"; ?>
        </div>
    </div>
</div>

					<div class="card">
						<div class="head">
							<div>
								<h2>Adress IP</h2>							
							</div>
						</div>
						<h4>192.168.253.1</h4>
					</div>
					<div class="card">
						<div class="head">
							<div>
                    <h2><a href="sauvegardeuser.php?dbname=<?php echo urlencode($dbname); ?>"> sauvegarde</a></h2>
                        </div>
                    </div>
			
			</div>
			</div>
	</main>
	</section>
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
	<script src="index3.js"></script>
</body>
</html>