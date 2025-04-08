<?php

$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "user_database";  

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the token is provided via GET request
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists in the database and is not expired
    $sql = "SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token is valid, process the password reset
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $newPassword = $_POST['password'];
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // Hash the new password

            // Update the password in the database and clear the reset token
            $updateSql = "UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("ss", $hashedPassword, $token);
            $stmt->execute();

            echo "Your password has been successfully reset!";
        }
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}

$conn->close();
?>
