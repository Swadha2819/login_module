<?php
require_once 'session_handler.php';
secureSession();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'bd.php';

$error_message = "";
$success_message = "";

// Get user ID from URL
$user_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : $_SESSION['user_id'];

if ($_SESSION['role']!== 'admin' && $user_id != $_SESSION['user_id']) {
    header("Location: dashboard.php?msg=unauthorized");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = $_POST['new_password'];
    
    $check_username = "SELECT * FROM users WHERE username = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $check_username);
    mysqli_stmt_bind_param($stmt, "si", $username, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $error_message = "Username already exists!";
    } else {

        $check_email = "SELECT * FROM users WHERE email = ? AND id != ?";
        $stmt = mysqli_prepare($conn, $check_email);
        mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error_message = "Email already exists!";
        } else {
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $hashed_password, $user_id);
            } else {
                // Update without changing password
                $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $user_id);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                // Update session variables if user is editing their own profile
                if ($user_id == $_SESSION['user_id']) {
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                }
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Error updating profile: " . mysqli_error($conn);
            }
        }
    }
}

$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location:dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>Edit Profile<?php echo ($user_id != $_SESSION['user_id']) ? ' - ' . htmlspecialchars($user['username']) : ''; ?></h2>
        
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <form action="edit_profile.php?id=<?php echo $user_id; ?>" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="new_password">New Password: (Leave blank to keep current password)</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
        </form>

        <div class="links">
            <a href="dashboard.php">Back to Dashboard</a>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html> 