<?php
session_start();
if (isset($_GET['token'])) {
    echo "Token received: " . htmlspecialchars($_GET['token']);
} else {
    echo "No token received.";
}

include 'bd.php';

// Check if the token is present in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Prepare and execute query to find the user with the token
    $sql = "SELECT * FROM users WHERE verification_token = ? AND is_verified = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Token is valid, update user status to verified
        $updateSql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("s", $token);
        $updateStmt->execute();

        echo "Email verified successfully! You can now log in.";
    } else {
        echo "Invalid or expired token. Please try again.";
    }
} else {
    echo "No verification token provided.";
}
?>

