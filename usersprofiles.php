<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';
$conn->select_db($dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Execute SQL query and store in array to avoid conflict with sidebar loop
$sql = "SELECT id, username, picture, autorisation FROM users";
$result = $conn->query($sql);
$users = array();
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
// We do NOT close $conn here because sidebar needs it.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagess/sealpic.png" type="image/png">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="utilisateur.css">
    <title>Liste d'utilisateurs</title>
</head>
<body>
    <!-- SIDEBAR -->
    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>
    <!-- SIDEBAR -->

    <!-- NAVBAR -->
    <section id="content">
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <form action="#">
                <div class="form-group"></div>
            </form>
            <a href="#" class="nav-link"></a>
            <a href="#" class="nav-link"></a>
            <div class="profile">
                <?php
                $navPic = (isset($_SESSION['picture']) && !empty($_SESSION['picture'])) ? "data:image/jpeg;base64," . base64_encode($_SESSION['picture']) : "imagess/sealpic.png";
                ?>
                <img src="<?php echo $navPic; ?>" alt="Profile">
                <ul class="profile-link">
                    <li><a href="profile.php"><i class='bx bxs-user-circle icon'></i> Profil</a></li>
                    <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
                </ul>
            </div>
        </nav>

        <!-- MAIN -->
        <main>
            <h1 class="title">Profile des Utilisateurs</h1>
            <ul class="breadcrumbs">
                <li><a href="index3.html">Acceuil</a></li>
                <li class="divider">/</li>
                <li><a href="index4.html" class="active">Utilisateur</a></li>
            </ul>
            <div class="table-data">
                <div class="order">
                    <table>
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Autorisation</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
if (!empty($users)) {
    // Output data of each row
    foreach ($users as $row) {
        if ($row["username"] !== "admin") {
        $userImg = (!empty($row["picture"])) ? "data:image/jpeg;base64," . base64_encode($row["picture"]) : "imagess/sealpic.png";
        echo '<tr>
        <td>
            <img src="' . $userImg . '" alt="' . htmlspecialchars($row["username"]) . '"/>
            <p><a href="userinf.php?id=' . $row["id"] . '">' . htmlspecialchars($row["username"]) . '</a></p>
        </td>
        <td><span class="status completed">' . htmlspecialchars($row["autorisation"]) . '</span></td>
        </tr>';
        }
    }
}
// Close connection (sidebar logic already ran, so now safe to close if not closed there)
// Sidebar block doesn't explicitly close $conn at end of its block, it loop over $databases.
// But wait, the Sidebar block (lines 34-61) uses $result variable name!
// My previous fix REUSED $result variable in sidebar.
// This overwrote the $result object.
// But now I fetch into $users array at top, so overwriting $result is fine.
$conn->close();
?>
                        </tbody>
                    </table>
                    <button class="btn-send"><i class='bx bxs-user-plus'><a href="adduser.php">Ajouter</a></i></button>
                </div>
            </div>
        </main>
    </section>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="index3.js"></script>
</body>
</html>
