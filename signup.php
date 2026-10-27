<?php
include 'config.php';
$conn->select_db($dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $autorisation = $_POST['autorisation'];
    
    // Handle file upload
    $photo = $_FILES['photo'];
    $photoName = $photo['name'];
    $photoTmpName = $photo['tmp_name'];
    $photoSize = $photo['size'];
    $photoError = $photo['error'];
    $photoType = $photo['type'];

    // Validate and move uploaded file
    $photoExt = strtolower(pathinfo($photoName, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png');

    if (in_array($photoExt, $allowed)) {
        if ($photoError === 0) {
            if ($photoSize < 2000000) { // Limit file size to 2MB for safe BLOB storage
                
                // Read the file content
                $photoContent = file_get_contents($photoTmpName);

                // Prepare the SQL statement
                // Note: We use null for placeholder? No, standard prepared statement.
                $stmt = $conn->prepare("INSERT INTO users (username, password, autorisation, picture) VALUES (?, ?, ?, ?)");
                
                // Bind params. 'b' is for Blob, but 's' usually works for medium blobs. 
                // However, correct way for BLOB is sending packets. 
                // For simplicity in this env, we try binding as string first (standard php-mysqli practice for small images).
                $null = NULL;
                $stmt->bind_param("ssss", $username, $password, $autorisation, $photoContent);
                // Note: If image is large, bind_param might fail or need send_long_data. 
                // But let's assume standard small profile pics.

                if ($stmt->execute()) {
                    header("Location: usersprofiles.php"); // Redirect to profile list instead of signup-success
                } else {
                    echo "Error: " . $stmt->error;
                }

                $stmt->close();
            } else {
                echo "Your file is too big! Max 2MB.";
            }
        } else {
            echo "There was an error uploading your file!";
        }
    } else {
        echo "You cannot upload files of this type!";
    }
}

$conn->close();
?>
