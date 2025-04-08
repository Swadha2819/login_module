<?php
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidEmailDomain($email) {
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, 'MX');
}

session_start();
require_once 'bd.php';
$error_message = "";
$success_message = "";

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate email format
    if (!isValidEmail($email)) {
        $error_message = "Invalid email format!";
    } 
    // Validate email domain
    elseif (!isValidEmailDomain($email)) {
        $error_message = "Invalid email domain!";
    } 
    // Validate password match
    elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } 
    // Proceed with registration if all validations pass
    else {
        $check_username = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $check_username);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error_message = "Username already exists!";
        } else {
            $check_email = "SELECT * FROM users WHERE email = ?";
            $stmt = mysqli_prepare($conn, $check_email);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $error_message = "Email already exists!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
               // Generate a unique verification token
$verification_token = bin2hex(random_bytes(32)); // 64 characters

// Insert user data with token and set is_verified to 0
$sql = "INSERT INTO users (username, email, password, verification_token, is_verified)
        VALUES (?, ?, ?, ?, 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $email, $hashed_password, $verification_token);

if ($stmt->execute()) {
    echo "Account created successfully! Please verify your email.";
} else {
    echo "Error: " . $stmt->error;
}

                }
            }
        }
    }

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container"> 
        <h2>Sign Up</h2>
        
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <form action="sin.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter Username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter Email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>

            <button type="submit">Sign Up</button>
        </form>

        <div class="links">
            <a href="lin.php">Already have an account? Login</a>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
