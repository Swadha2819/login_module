<?php
require_once 'bd.php';

$message = '';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $sql = "SELECT id, username FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $user['id'], $token, $expires);
        
        if (mysqli_stmt_execute($stmt)) {
            // Send email with reset link
            $reset_link = "http://localhost:8080/login_module/reset-password.php?token=" . $token;
            $to = $email;
            $subject = "Password Reset Request";
            $message_body = "Hello " . $user['username'] . ",\n\n";
            $message_body .= "You have requested to reset your password. Click the link below to reset it:\n\n";
            $message_body .= $reset_link . "\n\n";
            $message_body .= "This link will expire in 1 hour.\n\n";
            $message_body .= "If you didn't request this, please ignore this email.\n";
            $headers = "From: noreply@yourwebsite.com";
            
            if (mail($to, $subject, $message_body, $headers)) {
                $message = "Password reset instructions have been sent to your email.";
            } else {
                $message = "Error sending email. Please try again later.";
            }
        } else {
            $message = "Error processing request. Please try again.";
        }
    } else {
        // For security, show the same message even if email doesn't exist
        $message = "If the email exists in our system, you will receive reset instructions.";
    }
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .info-message {
            background-color: #e3f2fd;
            color: #0d47a1;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            text-align: center;
        }
        .back-btn {
            display: inline-block;
            margin-top: 10px;
            color: #4CAF50;
            text-decoration: none;
        }
        .back-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Forgot Password</h2>
        
        <?php if (!empty($message)): ?>
            <p class="info-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="forgot-pass.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            
            <button type="submit">Reset Password</button>
        </form>

        <div class="links">
            <a href="lin.php" class="back-btn">Back to Login</a>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>