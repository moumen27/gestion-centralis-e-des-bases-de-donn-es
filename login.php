<?php
session_start();

include 'config.php';

$is_invalid = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $entered_username = $mysqli->real_escape_string($_POST['username']);
    $entered_password = $_POST['password'];

    // Authenticate against Database for ALL users (including Admin)
    // This allows Admin password changes to take effect
    $sql = "SELECT * FROM users WHERE username='$entered_username'";
    $result = $mysqli->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Direct password comparison (as per existing logic)
        if ($entered_password === $user['password']) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['picture'] = $user['picture']; // Blob data
            
            if ($user['username'] === 'admin') {
                $_SESSION['user_id'] = 'admin';
                header("Location: acceuil.php");
            } else {
                $_SESSION['user_id'] = $user['id'];
                header("Location: acceuiluser.php");
            }
            exit;
        }
    } elseif ($entered_username === 'admin' && $entered_password === 'admin') {
        // Fallback: If Admin not in DB, create it and log in
        // This ensures the default credentials always work
        $default_picture = "";
        if (file_exists("imagess/sealpic.png")) {
            $default_picture = file_get_contents("imagess/sealpic.png");
        }
        
        // Insert Admin
        $stmt = $mysqli->prepare("INSERT INTO users (username, password, autorisation, picture) VALUES ('admin', 'admin', 'compléte', ?)");
        // Note: Using 's' for blob if small enough, ensuring compatibility
        $null = NULL;
        $stmt->bind_param("s", $default_picture);
        $stmt->execute();
        $stmt->close();
        
        // Set Session
        $_SESSION['user_id'] = 'admin';
        $_SESSION['username'] = 'admin';
        $_SESSION['picture'] = $default_picture;
        
        header("Location: acceuil.php");
        exit;
    }
    // Proceed with regular user authentication
    $sql = "SELECT * FROM users WHERE username='$entered_username'";
    $result = $mysqli->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($entered_password === $user['password']) { // Direct password comparison
            // Password is correct
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['picture'] = $user['picture']; // Assuming there's a profile_pic column
            header("Location: acceuiluser.php");
            exit;
        }
    }

    $is_invalid = true;
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="loginpage.css">
  <link rel="icon" href="imagess/sealpic.png" type="image/png">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
  <div class="wrapper">
    <form method="post">
      <h1>Login</h1>
      <?php if ($is_invalid): ?>
        <em>Invalid login</em>
      <?php endif; ?>
      <div class="input-box">
        <input type="text" placeholder="Nom d'utilisateur" name="username" id="username" required>
        <i class='bx bxs-user'></i>
      </div>
      <div class="input-box">
        <input type="password" placeholder="Mot de passe" name="password" id="password" required>
        <i class='bx bxs-lock-alt'></i>
      </div>
      <button type="submit" class="btn">Login</button>
    </form>
  </div>
</body>
</html>
