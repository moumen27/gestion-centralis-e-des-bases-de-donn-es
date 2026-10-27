<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 'admin') {
    // Basic check, adjust if admin ID is different or stored differently
    // In login.php (step 609), admin sesssion: user_id='admin', username='admin'.
    if ($_SESSION['username'] !== 'admin') {
        header("Location: login.php");
        exit;
    }
}

// Admin handling is slightly different because admin might not be in 'users' table or is hardcoded?
// In login.php: "if ($entered_username === 'admin' ...) { $_SESSION['user_id'] = 'admin'; ... }"
// So Admin is NOT in the database?
// If Admin is not in DB, we cannot "Update" their profile in DB.
// Let's check login.php again (Step 609).
// Lines 13-20: Hardcoded Admin check.
// So Admin profile is STATIC in code.
// Exception: "picture" is set to 'sealpic.png'.
// If user wants to "manage profile" for admin, we have two choices:
// 1. Convert Admin to a DB user.
// 2. Create a "settings" table or file for admin.
// 3. Or just say "Admin profile is managed via config" (but user asked for it).
// Given "ajouter la gestion de profil pour l'admin", I should probably migrate Admin to DB or create a specific record.

// Check if 'admin' exists in users table.
include 'config.php';
$conn->select_db($dbname);

$stmt = $conn->prepare("SELECT id FROM users WHERE username = 'admin'");
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    // Admin user not in DB. Create it?
    // If I create it, I need to update login.php to check DB for admin too OR prefer DB admin.
    // However, login.php has hardcoded "if === 'admin'".
    // For now, I will create a simple Profile page for Admin that might just allow changing the Password?
    // But if password is hardcoded in login.php, changing it here won't work unless I change login.php.
    
    // DECISION: To properly support Admin Profile, I should insert the admin into the DB and update login.php to use DB for admin (or at least check DB first).
    // This is safer and cleaner.
    
    // Let's insert Admin if missing.
    // Default pwd 'admin' (from login.php).
    // Picture 'sealpic.png' (we need to read file content).
    $default_blob = "";
    if (file_exists("imagess/sealpic.png")) {
        $default_blob = file_get_contents("imagess/sealpic.png");
    }
    
    $stmtIns = $conn->prepare("INSERT INTO users (username, password, autorisation, picture) VALUES ('admin', 'admin', 'compléte', ?)");
    // This "admin" string pwd matches the hardcoded one.
    $stmtIns->bind_param("s", $default_blob);
    $stmtIns->execute();
    $stmtIns->close();
}
$stmt->close();

// Now Admin is in DB (id might be auto-inc).
// BUT login.php (Step 609) checks hardcoded values FIRST.
// "if ($entered_username === 'admin' && $entered_password === 'admin')"
// This prevents using the DB password if changed.
// I MUST update login.php to remove hardcoded admin check or prioritize DB.
// I will update login.php to REMOVE the hardcoded block and treat Admin as a normal user (with specific role/session flags if needed, but currently just 'admin' username).
// Actually, acceuiluser.php checks "if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === 'admin')".
// If I make admin a normal user, their ID will be an integer (e.g. 1).
// `acceuiluser.php` line 4: `if (... || $_SESSION['user_id'] === 'admin')` -> Redirects to Login.
// This means Admin CANNOT access `acceuiluser.php` (User Home). That's correct.
// But Admin Home (`acceuil.php`)?
// `acceuil.php` Step 672: `session_start();` ... No access control check at top!
// It just assumes valid session.

// Plan:
// 1. `profile.php` will work with DB admin user.
// 2. I will update `login.php` to use DB for EVERYONE.
// 3. I will need to verify `acceuil.php` access control.

// Let's write `profile.php` assuming DB access.
// We need to fetch the admin's ID.
$stmt = $conn->prepare("SELECT id, username, password, picture FROM users WHERE username = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Robustness: If Admin is not found in DB (e.g. wiped), force logout.
// The login.php script will auto-create the Admin user upon next login.
if (!$user) {
    header("Location: logout.php");
    exit;
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = isset($_POST['password']) ? $_POST['password'] : $user['password'];
    
    $photoContent = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        if ($_FILES['photo']['size'] < 2000000) {
            $photoContent = file_get_contents($_FILES['photo']['tmp_name']);
        } else {
            $message = "Fichier trop volumineux (max 2 Mo).";
        }
    }

    if (empty($message)) {
        if ($photoContent) {
            $stmt = $conn->prepare("UPDATE users SET password = ?, picture = ? WHERE id = ?");
            $stmt->bind_param("ssi", $newPassword, $photoContent, $user['id']);
        } else {
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $newPassword, $user['id']);
        }
        $stmt->execute();
        $stmt->close();
        
        $message = "Profil mis à jour avec succès.";
        if ($photoContent) {
            $_SESSION['picture'] = $photoContent;
            $user['picture'] = $photoContent;
        }
        $user['password'] = $newPassword;
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
    <title>Profil Admin</title>
    <style>
        .profile-container { background: white; padding: 20px; border-radius: 8px; max-width: 500px; margin: 20px auto; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-control { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-save { background: var(--blue); color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .current-pic { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; display: block; margin: 0 auto 20px; }
    </style>
</head>
<body>
    <section id="sidebar">
        <a href="acceuil.php" class="brand"><i class='bx bxs-data'></i>Admin</a>
        <ul class="side-menu">
            <li><a href="acceuil.php"><i class='bx bxs-home icon'></i> Acceuil</a></li>
            <li class="divider" data-text="main"></li>
            <li>
                <a href="displayall.php"><i class='bx bxs-data icon'></i> base de données <i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                    <?php
                    $databases = array();
                    $result = $conn->query("SHOW DATABASES");
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) { $databases[] = $row['Database']; }
                    }
                    foreach ($databases as $db) {
                        $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='disponibilite'");
                        $row = $result->fetch_assoc();
                        if ($row['count'] > 0) {
                             $result = $conn->query("SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA='$db' ORDER BY UPDATE_TIME DESC LIMIT 1");
                             if ($result->num_rows > 0) echo "<li><a href='displaying.php?dbname=$db'>" . $db . "</a></li>";
                        }
                    }                 
                    ?>
                </ul>
            </li>
            <li><a href="usersprofiles.php"><i class='bx bxs-group icon'></i> Utilisateur</a></li>
        </ul>
        <div class="ads">
            <a href="logout.php" class="btn-upgrade"><i class='bx bxs-log-out-circle'></i>Se déconnecter</a>
        </div>
    </section>

    <section id="content">
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <form action="#"><div class="form-group"></div></form>
            <div class="profile">
                <!-- Use dynamic blob or fallback -->
                 <?php 
                 $imgData = !empty($user['picture']) ? base64_encode($user['picture']) : '';
                 if (empty($imgData) && isset($_SESSION['picture']) && !empty($_SESSION['picture'])) {
                     $imgData = base64_encode($_SESSION['picture']);
                 }
                 $avatarSrc = !empty($imgData) ? "data:image/jpeg;base64," . $imgData : "imagess/sealpic.png";
                 ?>
                <img src="<?php echo $avatarSrc; ?>" alt="Admin">
                <ul class="profile-link">
                    <li><a href="profile.php"><i class='bx bxs-user-circle icon'></i> Profil</a></li>
                    <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Logout</a></li>
                </ul>
            </div>
        </nav>
        <main>
            <h1 class="title">Profil Administrateur</h1>
            <div class="profile-container">
                <?php if ($message) echo "<p style='color:green'>$message</p>"; ?>
                <form method="post" enctype="multipart/form-data">
                    <img src="<?php echo $avatarSrc; ?>" class="current-pic">
                    
                    <div class="form-group">
                        <label>Photo de profil</label>
                        <input type="file" name="photo" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Nom d'utilisateur</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" class="form-control" disabled title="Admin username cannot be changed">
                    </div>
                    
                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn-save">Mettre à jour</button>
                </form>
            </div>
        </main>
    </section>
    <script src="index3.js"></script>
</body>
</html>
