<?php
session_start();
// Rediriger si non connecté
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['dbname']) || empty(trim($_GET['dbname']))) {
    header("Location: displayall.php");
    exit;
}

$dbname = trim($_GET['dbname']);

// Connect to MySQL
$servername = "localhost";
$username = "root";
$password = "";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
} catch (Throwable $e) {
    header("Location: displayall.php");
    exit;
}

if ($conn->connect_error) {
    header("Location: displayall.php");
    exit;
}

// Get database size
$result = $conn->query("SELECT table_schema AS 'Database', 
                              ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' 
                       FROM information_schema.tables 
                       WHERE table_schema = '$dbname'");
$row = $result ? $result->fetch_assoc() : null;
$size = ($row && isset($row['Size (MB)'])) ? $row['Size (MB)'] : 0;

// Get database status
$result = $conn->query("SHOW TABLE STATUS FROM `$dbname`");
$rows = $result ? $result->num_rows : 0;
if ($rows > 0) {
    $state = 'actif'; 
} else {
    $state = "non actif";
}

// Get central memory usage
$result = $conn->query("SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_pages_%';");
$innodb_buffer_pool_pages = 0;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $innodb_buffer_pool_pages += (float)$row['Value'];
    }
}
$central_memory_usage = round($innodb_buffer_pool_pages * 16 / 1024, 2); 

// Get latest save time
$result = $conn->query("SELECT UPDATE_TIME 
                        FROM information_schema.tables 
                        WHERE TABLE_SCHEMA = '$dbname' 
                        ORDER BY UPDATE_TIME DESC 
                        LIMIT 1");
$row = $result ? $result->fetch_assoc() : null;
$latest_save_time = ($row && !empty($row['UPDATE_TIME'])) ? $row['UPDATE_TIME'] : 'Jamais';

// Get CPU time
$cpu_time = function_exists('getrusage') ? getrusage() : array('ru_utime.tv_sec' => 0, 'ru_stime.tv_sec' => 0);
$cpu_seconds = (isset($cpu_time['ru_utime.tv_sec']) && isset($cpu_time['ru_stime.tv_sec'])) 
    ? ($cpu_time['ru_utime.tv_sec'] + $cpu_time['ru_stime.tv_sec']) 
    : 0;

// Obtenir ou initialiser automatiquement la table 'disponibilite'
$autorisation_table = 'disponibilite';
$autorisation_column = 'autorisation';
$autorisation = 'complète';

$chk_table = $conn->query("SHOW TABLES LIKE '$autorisation_table'");
if (!$chk_table || $chk_table->num_rows == 0) {
    // Création automatique de la table disponibilite
    $conn->query("CREATE TABLE IF NOT EXISTS `$autorisation_table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `nom` VARCHAR(255) DEFAULT 'SEAAL',
        `description` VARCHAR(255) DEFAULT 'Base de données',
        `autorisation` VARCHAR(255) DEFAULT 'complète',
        `last_backup_time` DATETIME DEFAULT NULL,
        `last_backup_user` VARCHAR(100) DEFAULT 'Inconnu',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("INSERT INTO `$autorisation_table` (`nom`, `description`, `autorisation`) 
                  VALUES ('SEAAL', 'Base de données', 'complète')");
} else {
    // Si la table existe déjà, récupérer l'autorisation
    $result_auth = $conn->query("SELECT $autorisation_column FROM `$autorisation_table` LIMIT 1");
    if ($result_auth && $result_auth->num_rows > 0) {
        $row_auth = $result_auth->fetch_assoc();
        $autorisation = $row_auth[$autorisation_column] ?? 'complète';
    }
}

// Get IP address
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Close MySQL connection for this specific db
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="imagess/sealpic.png" type="image/png">
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="index3.css">
	<title>Base de données - <?php echo htmlspecialchars($dbname); ?></title>
</head>
<body>
	
	<!-- SIDEBAR -->
	<?php include 'sidebar.php'; ?>
	<!-- SIDEBAR -->

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu toggle-sidebar' ></i>
			<form action="#">
				<div class="form-group">
				</div>
			</form>
			<a href="#" class="nav-link"></a>
			<a href="#" class="nav-link"></a>

			<div class="profile">
				<img src="<?php echo !empty($_SESSION['picture']) ? 'data:image/jpeg;base64,' . base64_encode($_SESSION['picture']) : 'imagess/sealpic.png'; ?>" alt="Profile">
				<ul class="profile-link">
					<li><a href="profile.php"><i class='bx bxs-user-circle icon'></i> Profil</a></li>
					<li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
				</ul>
			</div>
		</nav>
		<!-- NAVBAR -->

		<main>
			<h1 class="title">Description de la base de données</h1>
			<ul class="breadcrumbs">
				<li><a href="acceuil.php">Acceuil</a></li>
				<li class="divider">/</li>
				<li><a href="displaying.php?dbname=<?php echo urlencode($dbname); ?>" class="active"><?php echo htmlspecialchars($dbname); ?></a></li>
			</ul>
			<div class="info-data">
				<div class="card">
					<div class="head">
						<div>
							<h2>Espace mémoire occupé</h2>
						</div>
					</div>					
					<p>: <?php echo htmlspecialchars($size); ?> MB</p>
				</div>
				<div class="card">
					<div class="head">
						<div>
							<h2>Utilisation de mémoire centrale</h2>
						</div>
					</div>					
					<h4><?php echo htmlspecialchars($central_memory_usage); ?> MB</h4>
				</div>
				<div class="card">
					<div class="head">
						<div>
							<h2>Temps CPU</h2>							
						</div>
					</div>
					<p><?php echo $cpu_seconds; ?> seconds</p>
				</div>
				<div class="card">
					<div class="head">
						<div>
							<h2><a href="modifier.php?dbname=<?php echo urlencode($dbname); ?>">Autorisation</a></h2>
							<h4><?php echo htmlspecialchars($autorisation); ?></h4>					
						</div>
					</div>
				</div>
				<div class="card">
					<div class="head">
						<div>
							<h2><a href="restorelist.php?dbname=<?php echo urlencode($dbname); ?>">La dernière sauvegarde</a></h2>
							<h4><?php echo htmlspecialchars($latest_save_time); ?></h4>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="head">
						<div>
							<h2>Adresse IP</h2>							
						</div>
					</div>
					<h4><?php echo htmlspecialchars($ip_address); ?></h4>
				</div>
				<div class="card">
					<div class="head">
						<div>
							<h2><a href="sauvegarde.php?dbname=<?php echo urlencode($dbname); ?>">Sauvegarde</a></h2>
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