<?php
if (isset($_GET['message']) && isset($_GET['file'])) {
    $message = $_GET['message'];
    $file = $_GET['file'];
} else {
    // Redirect to home if accessed directly
    header("Location: acceuil.php");
    exit;
}

session_start();
$retour_link = "acceuil.php";
if (isset($_SESSION['username']) && $_SESSION['username'] !== 'admin') {
    $retour_link = "acceuiluser.php";
}
?>

<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="imagess/sealpic.png" type="image/png">
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="index3.css">
	<title>Télecharger </title>
</head>
<body>
	<section id="sidebar">
		<a href="acceuil.php" class="brand"><i class='bx bxs-data'></i>Admin</a>
		<ul class="side-menu">
			<li><a href="acceuil.php" ><i class='bx bxs-home icon' ></i> Acceuil</a></li>
			<li class="divider" data-text="main"></li>
			<li>
				<a href="displayall.php"><i class='bx bxs-data icon' class="active"></i> base de données <i class='bx bx-chevron-right icon-right' ></i></a>
                <ul class="side-dropdown">
   
<?php
    // Connect to MySQL
    include 'config.php';

    // Get list of databases
    $databases = array();
    $result = $conn->query("SHOW DATABASES");
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $databases[] = $row['Database'];
        }
    }

    // Get database information for databases with 'disponibilite' table
    foreach ($databases as $db) {
        // Check if the 'disponibilite' table exists in the current database
        $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='disponibilite'");
        $row = $result->fetch_assoc();
        $table_exists = $row['count'] > 0;

        if ($table_exists) {
            // If the 'disponibilite' table exists, retrieve the last update time
            $result = $conn->query("SELECT TABLE_NAME, UPDATE_TIME FROM information_schema.tables WHERE TABLE_SCHEMA='$db' ORDER BY UPDATE_TIME DESC LIMIT 1");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();                
                echo "<li><a href='displaying.php?dbname=$db'>" . $db . "</a></li>";
            }
        }
    }                 
     // Close MySQL connection
    $conn->close();
    ?>
                </ul>
			</li>
			<li><a href="usersprofiles.php"><i class='bx bxs-group icon' ></i> Utilisateur</a></li>
		</ul>
		<div class="ads">
				<a href="logout.php" class="btn-upgrade"><i class='bx bxs-log-out-circle' ></i>Se déconnecter</a>
			</div>
		</div>
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

			<div class="profile">
				<img src="imagess/sealpic.png">
		</nav>
		<main>
        <div class="info-data">
        <div class="card">
					<div class="head">
                    <h1><?php echo htmlspecialchars($message); ?></h1>
    <p>Votre fichier de sauvegarde est prêt: <a href="<?php echo htmlspecialchars($file); ?>">Télecharger</a></p>
    <a href="<?php echo $retour_link; ?>">Retourner</a>


	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
	<script src="index3.js"></script>
</body>
</html>