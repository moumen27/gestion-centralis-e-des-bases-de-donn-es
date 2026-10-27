<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagess/sealpic.png" type="image/png">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="utilisateur.css">
    <title>Ajouter un utilisateur</title>
</head>
<body>
    
    <!-- SIDEBAR -->
    <section id="sidebar">
    <a href="acceuil.php" class="brand"><i class='bx bxs-data'></i>Admin</a>
        <ul class="side-menu">
            <li><a href="acceuil.php" ><i class='bx bxs-home icon'></i> Acceuil</a></li>
            <li class="divider" data-text="main">Main</li>
            <li>
                <a href="displayall.php"><i class='bx bxs-data icon'></i> base de données <i class='bx bx-chevron-right icon-right'></i></a>
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
    $conn->close();
    ?>
                </ul>
            </li>
            <li><a href="usersprofiles.php"class="active"><i class='bx bxs-group icon'></i> Utilisateur</a></li>
        </ul>
        <div class="ads">
            <a href="logout.php" class="btn-upgrade"><i class='bx bxs-log-out-circle'></i>Se déconnecter</a>
        </div>
    </section>

    <!-- NAVBAR -->
    <section id="content">
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <form action="#">
                <div class="form-group">
                    <!--
                    <input type="text" placeholder="Search...">
                    <i class='bx bx-search icon'></i>-->
                </div>
            </form>
            <a href="#" class="nav-link">
                <!--
                <i class='bx bxs-bell icon'></i>
                <span class="badge">5</span>-->
            </a>
            <a href="#" class="nav-link">
                <!--
                <i class='bx bxs-message-square-dots icon'></i>
                <span class="badge">8</span>-->
            </a>
            <div class="profile">
                <img src="imagess/sealpic.png">
            </div>
        </nav>

        <!-- MAIN -->
        <main>
            <h1 class="title">Ajouter un utilisateur</h1>
            <ul class="breadcrumbs">
                <li><a href="acceuil.php">Acceuil</a></li>
                <li class="divider">/</li>
                <li><a href="adduser.php" class="active">Ajouter un utilisateur</a></li>
            </ul>
            <form action="signup.php" method="post" id="signup" enctype="multipart/form-data">

                <div class="table-data">
                    <div class="order">
                        <table>
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Nom d'utilisateur</th>
                                    <th>Password</th>
                                    <th>Autorisation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr> 
                                    <td>
                                        <label>
                                            <input type="file" name="photo" required>
                                        </label> &nbsp;  
                                    </td>
                                    <td>
                                        <input type="text" id="username" name="username" required>
                                    </td>
                                    <td>
                                        <input type="password" id="password" name="password" required>
                                    </td>
                                    <td>
                                        <select name="autorisation" required>
                                            <option value="non compléte">non compléte</option>
                                            <option value="compléte" selected>compléte</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>              
                        </table>                   
                        <button type="submit" class="btn-send"><i class='bx bx-save'></i> Sauvegarder</button>                
                    </div>
                </div>
            </form>
        </main>
    </section>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="index3.js"></script>
</body>
</html>
