<?php
include 'config.php';
$conn->select_db($dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id > 0) {
    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT username, picture, autorisation FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
} else {
    $user = null;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagess/sealpic.png" type="image/png">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="utilisateur.css">
    <title>Détails d'utilisateur</title>
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
            <form method="" action="#">
                <div class="form-group"></div>
            </form>
            <a href="#" class="nav-link"></a>
            <a href="#" class="nav-link"></a>
            <div class="profile">
                <img src="imagess/sealpic.png">
            </div>
        </nav>

        <!-- MAIN -->
        <main>
            <h1 class="title">Détails de l'utilisateur</h1>
            <ul class="breadcrumbs">
                <li><a href="acceuil.php">Acceuil</a></li>
                <li class="divider">/</li>
                <li><a href="usersprofiles.php" class="active">Utilisateur</a></li>
            </ul>
            <div class="table-data">
                <div class="order">
                <form method="post" action="userinf_action.php?id=<?php echo $user_id; ?>" enctype="multipart/form-data">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom d'utilisateur</th>
                                <th>Autorisation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                <?php if ($user): ?>                    
                    <td>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($user['picture']); ?>" alt="User Picture" style="width:100px; height:100px; object-fit:cover; border-radius:50%; display:block; margin-bottom:10px;"/>
                        <input type="file" name="photo" class="form-control" style="margin-bottom:10px;">
                        <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                    </td>
                    <td>                                    
                        <select class="status completed" name="autorisation">
                            <option <?php if ($user['autorisation'] == 'non compléte') echo 'selected'; ?>>non compléte</option>
                            <option <?php if ($user['autorisation'] == 'compléte') echo 'selected'; ?>>compléte</option>
                        </select>
                        <p></p>
                    </td>
                            </tr>
                        <?php endif; ?>
                </tbody>
                </table>
                <div class="text-right mt-3">
                    <button type="submit" class="btn-send" name="sauvgarder"><i class='bx bx-save'>Sauvegarder</i></button>&nbsp;
                    <button type="submit" class="btn-send" name="supprimer"><i class='bx bx-trash-alt'>Supprimer</i></button>&nbsp;
                </div>
                </form>
                </div>
            </div>
        </main>
    </section>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="index3.js"></script>
</body>
</html>
