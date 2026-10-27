<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php';
$conn->select_db($dbname);

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = $_POST['username'];
    $newPassword = $_POST['password']; // In a real app complexity check
    // Note: The original code stored plain text passwords (based on signup.php logic step 683, $password stored directly).
    // I will stick to existing logic (plain text) unless I see hashing, but signup.php definitely bind_param("ssss") with raw password.
    // Ideally I should hash it but I must maintain compatibility.
    
    // Check photo
    $photoContent = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $photo = $_FILES['photo'];
        if ($photo['size'] < 2000000) {
            $ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
            if (in_array($ext, array('jpg', 'jpeg', 'png'))) {
                $photoContent = file_get_contents($photo['tmp_name']);
            } else {
                $message = "Invalid file type.";
            }
        } else {
            $message = "File too large.";
        }
    }

    if (empty($message)) {
        if ($photoContent !== null) {
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, picture = ? WHERE id = ?");
            $stmt->bind_param("sssi", $newUsername, $newPassword, $photoContent, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssi", $newUsername, $newPassword, $user_id);
        }

        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
            // Update session info
            $_SESSION['username'] = $newUsername;
            if ($photoContent !== null) {
                $_SESSION['picture'] = $photoContent; // Update session blob
            }
        } else {
            $message = "Error updating profile: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch current data
$stmt = $conn->prepare("SELECT username, password, picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagess/sealpic.png" type="image/png">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="index3.css">
    <title>Mon Profil</title>
    <style>
        .profile-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 20px auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn-save {
            background: var(--blue);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .current-pic {
            width: 100px; 
            height: 100px; 
            border-radius: 50%; 
            object-fit: cover; 
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    
   <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="acceuiluser.php" class="brand"><i class='bx bxs-data'></i>Utilisateur</a>
        <ul class="side-menu">
            <li><a href="acceuiluser.php" ><i class='bx bxs-home icon'></i> Acceuil</a></li>
            <li class="divider" data-text="main"></li>
            <li>
                <a href="displayalluser.php"><i class='bx bxs-data icon'></i> base de données <i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                <?php
                // Connect to MySQL
                // include 'config.php'; // Already included at top
                // But typically inside sidebar loops we might need logic.
                // We reuse logic from previous fixes.
                
                $databases = array();
                $result = $conn->query("SHOW DATABASES");
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $databases[] = $row['Database'];
                    }
                }

                foreach ($databases as $db) {
                    $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='disponibilite'");
                    $row = $result->fetch_assoc();
                    if ($row['count'] > 0) {
                        echo "<li><a href='displayinguser.php?dbname=$db'>" . $db . "</a></li>";
                    }
                }                 
                ?>
                </ul>
            </li>
        </ul>
        <div class="ads">
            <a href="logout.php" class="btn-upgrade"><i class='bx bxs-log-out-circle'></i>Se déconnecter</a>
        </div>
    </section>
    <!-- SIDEBAR -->

    <!-- NAVBAR -->
    <section id="content">
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <form action="#"><div class="form-group"></div></form>
            <div class="profile">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($_SESSION['picture']); ?>" alt="Profile Picture">
                <ul class="profile-link">
                    <li><i class='bx bxs-user-circle icon' ></i> <?php echo $_SESSION['username']; ?></a></li>
                    <li><a href="logout.php"><i class='bx bxs-log-out-circle' ></i> Logout</a></li>
                </ul>
            </div>
        </nav>

        <main>
            <h1 class="title">Mon Profil</h1>
            <div class="profile-container">
                <?php if ($message) echo "<p style='color:green; font-weight:bold;'>$message</p>"; ?>
                
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group" style="text-align:center;">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($user['picture']); ?>" class="current-pic">
                    </div>
                    
                    <div class="form-group">
                        <label>Photo de profil</label>
                        <input type="file" name="photo">
                    </div>

                    <div class="form-group">
                        <label>Nom d'utilisateur</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" required>
                    </div>

                    <button type="submit" class="btn-save">Mettre à jour</button>
                </form>
            </div>
        </main>
    </section>

    <script src="index3.js"></script>
</body>
</html>
