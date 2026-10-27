<?php
session_start();
if (isset($_GET['message']) && isset($_GET['file'])) {
    $message = $_GET['message'];
    $file = $_GET['file'];
} else {
    // Redirect to home if accessed directly
    header("Location: acceuiluser.php");
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
    <title>Sauvegarde Réussie</title>
</head>
<body>
    
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="acceuiluser.php" class="brand"><i class='bx bxs-data'></i>Utilisateur</a>
        <ul class="side-menu">
            <li><a href="acceuiluser.php" ><i class='bx bxs-home icon'></i> Acceuil</a></li>
            <li class="divider" data-text="main"></li>
            <li>
                <a href="displayalluser.php"><i class='bx bxs-data icon'></i> Base de données <i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                <?php
                require_once 'config.php';
                $conn_list = new mysqli($servername, $username, $password);
                if (!$conn_list->connect_error) {
                    $databases = array();
                    $result = $conn_list->query("SHOW DATABASES");
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $databases[] = $row['Database'];
                        }
                    }
                    $ignored_dbs = array('information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin');
                    foreach ($databases as $db) {
                        if (in_array($db, $ignored_dbs)) continue;
                        echo "<li><a href='displayinguser.php?dbname=" . urlencode($db) . "'>" . htmlspecialchars($db) . "</a></li>";
                    }
                    $conn_list->close();
                }
                ?>
                </ul>
            </li>
        </ul>
    </section>

    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <form action="#">
                <div class="form-group"></div>
            </form>
            <div class="profile">
                <?php if(isset($_SESSION['username']) && isset($_SESSION['picture'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($_SESSION['picture']); ?>" alt="Profile Picture">
                    <ul class="profile-link">
                        <li><a href="profileuser.php"><i class='bx bxs-user-circle icon' ></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                        <li><a href="logout.php"><i class='bx bxs-log-out-circle' ></i> Logout</a></li>
                    </ul>
                <?php endif; ?>
            </div>
        </nav>

        <!-- MAIN -->
        <main>
            <h1 class="title">Sauvegarde</h1>
            <ul class="breadcrumbs">
                <li><a href="acceuiluser.php">Acceuil</a></li>
                <li class="divider">/</li>
                <li><a href="#" class="active">Succès</a></li>
            </ul>

            <div class="info-data">
                <div class="card">
                    <div class="head">
                        <div>
                            <h1><?php echo htmlspecialchars($message); ?></h1>
                            <br>
                            <p style="font-size: 1.1em;">Votre fichier de sauvegarde est prêt : <br><br>
                            <a href="<?php echo htmlspecialchars($file); ?>" style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;"><i class='bx bxs-download'></i> Télécharger la sauvegarde</a>
                            </p>
                        </div>
                    </div>
                    <br><br>
                    <a href="acceuiluser.php" style="display: inline-block; padding: 10px 20px; background: #3C91E6; color: white; text-decoration: none; border-radius: 5px;"><i class='bx bx-arrow-back'></i> Retourner à l'accueil</a>
                </div>
            </div>
        </main>
    </section>

    <script src="index3.js"></script>
</body>
</html>