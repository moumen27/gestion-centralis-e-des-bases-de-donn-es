<?php
session_start();
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $error = isset($_GET['error']) ? $_GET['error'] : '';
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
    <title>Echec Sauvegarde</title>
</head>
<body>
    
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="acceuiluser.php" class="brand"><i class='bx bxs-data'></i>Utilisateur</a>
        <ul class="side-menu">
            <li><a href="acceuiluser.php" ><i class='bx bxs-home icon'></i> Acceuil</a></li>
            <li class="divider" data-text="main"></li>
            <li>
                <a href="displayalluser.php"><i class='bx bxs-data icon'></i> Base de données</a>
            </li>
        </ul>
    </section>

    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <div class="profile">
                <?php if(isset($_SESSION['username'])): ?>
                    <span style="margin-right: 10px;"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <?php endif; ?>
            </div>
        </nav>

        <!-- MAIN -->
        <main>
            <h1 class="title">Sauvegarde</h1>
            <ul class="breadcrumbs">
                <li><a href="acceuiluser.php">Acceuil</a></li>
                <li class="divider">/</li>
                <li><a href="#" class="active" style="color:red;">Echec</a></li>
            </ul>

            <div class="info-data">
                <div class="card">
                    <div class="head">
                        <div>
                            <h1 style="color: red;"><?php echo htmlspecialchars($message); ?></h1>
                            <br>
                            <?php if ($error): ?>
                            <div style="background-color: #ffe6e6; border: 1px solid red; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                                <h3>Détails de l'erreur :</h3>
                                <pre style="white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($error); ?></pre>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                    <br>
                    <a href="acceuiluser.php" style="display: inline-block; padding: 10px 20px; background: #3C91E6; color: white; text-decoration: none; border-radius: 5px;"><i class='bx bx-arrow-back'></i> Retourner à l'accueil</a>
                </div>
            </div>
        </main>
    </section>

    <script src="index3.js"></script>
</body>
</html>
