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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="imagess/sealpic.png" type="image/png">
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="index3.css">
	<title>Sauvegarder - <?php echo htmlspecialchars($dbname); ?></title>
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
			<h1 class="title">Sauvegarde de la base de données</h1>
			<ul class="breadcrumbs">
				<li><a href="acceuil.php">Acceuil</a></li>
				<li class="divider">/</li>
				<li><a href="displaying.php?dbname=<?php echo urlencode($dbname); ?>"><?php echo htmlspecialchars($dbname); ?></a></li>
				<li class="divider">/</li>
				<li><a href="#" class="active">Sauvegarde</a></li>
			</ul>
			<div class="info-data">
				<div class="card">
					<div class="head">
						<div>
							<h2>Lancer une sauvegarde</h2>
							<p style="margin: 10px 0;">Base de données : <strong><?php echo htmlspecialchars($dbname); ?></strong></p>
							<form method="get" action="export.php" onsubmit="showLoading()" style="margin-top: 15px;">
								<input type="hidden" name="dbname" value="<?php echo htmlspecialchars($dbname); ?>">
								<button type="submit" class="btn-send"><i class='bx bxs-save'></i> Sauvegarder</button>
								<a href="restorelist.php?dbname=<?php echo urlencode($dbname); ?>" class="btn-send" style="text-decoration:none; margin-left:10px; background-color: #17a2b8; color: white;"><i class='bx bx-list-ul'></i> Voir les sauvegardes</a>
								<a href="displaying.php?dbname=<?php echo urlencode($dbname); ?>" class="btn-send" style="text-decoration:none; margin-left:10px; background-color: #f0f0f0; color: #333;"><i class='bx bx-arrow-back'></i> Retour</a>
							</form>
							<div id="loading" style="display:none; margin-top: 15px; font-weight: bold; color: #007bff;">Sauvegarde en cours, veuillez patienter...</div>
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