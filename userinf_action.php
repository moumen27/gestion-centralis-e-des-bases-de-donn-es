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
    // Handle sauvgarde action
    if (isset($_POST['sauvgarder'])) {
        $newUsername = $_POST['username'];
        $newAutorisation = $_POST['autorisation'];
        
        // Check if a new photo was uploaded
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $photo = $_FILES['photo'];
            if ($photo['size'] < 2000000) { // 2MB limit
                $allowed = array('jpg', 'jpeg', 'png');
                $ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $photoContent = file_get_contents($photo['tmp_name']);
                    
                    // Update with picture
                    $stmt = $conn->prepare("UPDATE users SET username = ?, autorisation = ?, picture = ? WHERE id = ?");
                    // Assuming authorization field exists and is string.
                    // Note: In typical mysqli, send_long_data might be needed for large blobs, but for <2MB string binding often works.
                    // If errors occur, we might need to switch to full blob handling, but adhering to existing pattern.
                    $stmt->bind_param("sssi", $newUsername, $newAutorisation, $photoContent, $user_id);
                } else {
                    echo "Invalid file type. Only JPG, PNG allowed.";
                    exit;
                }
            } else {
                echo "File too large. Max 2MB.";
                exit;
            }
        } else {
            // Update without picture
            $stmt = $conn->prepare("UPDATE users SET username = ?, autorisation = ? WHERE id = ?");
            $stmt->bind_param("ssi", $newUsername, $newAutorisation, $user_id);
        }
        
        if ($stmt->execute()) {
             // Redirect back to userinf to show changes (User friendly)
             header("Location: userinf.php?id=" . $user_id);
             exit;
        } else {
            echo "Error updating record: " . $stmt->error;
        }
        
        $stmt->close();
    }

    // Handle supprime action
    if (isset($_POST['supprimer'])) {
        // Prepare the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
