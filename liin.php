<?php
//require_once __DIR__ . '/google-api-php-client/vendor/autoload.php';
require_once 'session_handler.php';
require_once 'vendor/google-api-php-client--PHP8.0/vendor/autoload.php';
secureSession();

require_once 'bd.php';

$error_message = "";

// Check if timeout message exists
if (isset($_GET['msg']) && $_GET['msg'] == 'timeout') {
    $error_message = "Your session has expired. Please login again.";
}

if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize input to prevent SQL Injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

   
    $query = "SELECT * FROM users WHERE (username = ? OR email = ?) LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    
    if (mysqli_num_rows($result) > 0) {
        
        $user = mysqli_fetch_assoc($result);

    
        if (password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

           
            $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "i", $user['id']);
            mysqli_stmt_execute($stmt);

    
            header("Location: dashboard.php");
            exit();
        } else {
            
            $error_message = "Invalid username/email or password!";
        }
    } else {
        
        $error_message = "Invalid username/email or password!";
    }
}
$client = new Google_Client();
$client->setClientId('785784893264-73oingfdastd4dgfivqr9aqd0509je7h.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost:8080/google_callback.php');
$client->addScope('email');
$client->addScope('profile');

$authUrl = $client->createAuthUrl();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Theme Selector Box -->
    <div class="theme-selector-box">
        <h3>Choose Theme</h3>
        <select id="theme">
            <option value="light">Light</option>
            <option value="dark">Dark</option>
            <option value="custom">Custom</option>
        </select>
    <!-- Login Box -->
    <div class="form-container">
        <h2>Login</h2>
        <form action="liin.php" method="POST">
            <label for="username">Username/Email:</label>
            <input type="text" id="username" name="username" placeholder="Enter Username or Email" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required><br>

            <button type="submit">Login</button>
            <br>
<a href="google-login.php">
    <button type="button" style="background-color: #4285F4; color: white; border: none; padding: 10px 20px; cursor: pointer;">
        Login with Google
    </button>
</a>
        </form>

        <?php if (!empty($error_message)) { ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php } ?>
        <div class="links">
            <a href="sin.php">Sign Up</a>
            <a href="forgot-pass.php">Forgot Password?</a>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>