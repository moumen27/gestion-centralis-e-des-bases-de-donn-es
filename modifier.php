<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['dbname']) || empty(trim($_GET['dbname']))) {
    header("Location: displayall.php");
    exit;
}

include 'config.php';
$dbname = trim($_GET['dbname']);

try {
    $conn->select_db($dbname);
} catch (Throwable $e) {
    header("Location: displayall.php");
    exit;
}

// Get authorization safely and auto-create table if missing
$autorisation_table = 'disponibilite'; 
$autorisation_column = 'autorisation'; 
$autorisation = 'complète';

$chk_table = $conn->query("SHOW TABLES LIKE '$autorisation_table'");
if (!$chk_table || $chk_table->num_rows == 0) {
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
    $result = $conn->query("SELECT $autorisation_column FROM `$autorisation_table` LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $autorisation = $row[$autorisation_column] ?? 'complète';
    }
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
	<title>Modifier l'autorisation - <?php echo htmlspecialchars($dbname); ?></title>
</head>
<body>
	<!-- SIDEBAR -->
	<?php include 'sidebar.php'; ?>
	<!-- SIDEBAR -->

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

		<!-- MAIN -->
		<main>
			<h1 class="title">Modifier l'autorisation</h1>
			<ul class="breadcrumbs">
				<li><a href="acceuil.php">Acceuil</a></li>
				<li class="divider">/</li>
				<li><a href="displaying.php?dbname=<?php echo urlencode($dbname); ?>"><?php echo htmlspecialchars($dbname); ?></a></li>
				<li class="divider">/</li>
				<li><a href="#" class="active">Autorisation</a></li>
			</ul>
			<div class="info-data">
				<div class="card">
					<div class="head">
						<div>
							<h2>Autorisation actuelle</h2>		
							<h4><?php echo htmlspecialchars($autorisation); ?></h4>
							<form action="update_autorisation.php?dbname=<?php echo urlencode($dbname); ?>" method="post" style="margin-top: 15px;">
								<input type="hidden" name="dbname" value="<?php echo htmlspecialchars($dbname); ?>">
								<input type="hidden" name="current_autorisation" value="<?php echo htmlspecialchars($autorisation); ?>">
								<button type="submit" class="btn-send">Modifier</button>
								<a href="displaying.php?dbname=<?php echo urlencode($dbname); ?>" class="btn-send" style="text-decoration:none; margin-left:10px; background-color: #f0f0f0; color: #333;">Retour</a>
							</form>						
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