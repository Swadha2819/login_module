<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'session_handler.php';
secureSession();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: lin.php");
    exit();
}

require_once 'bd.php';

if (isset($_POST['delete_user'])) {
    $user_id_to_delete = mysqli_real_escape_string($conn, $_POST['user_id']);
    
    // Prevent users from deleting their own account
    if ($user_id_to_delete == $_SESSION['user_id']) {
        header("Location:dashboard.php?msg=cant_delete_self");
        exit();
    }
    
    // Check if user exists and is verified
$get_user = "SELECT * FROM users WHERE id = ? AND is_verified = 1";
$stmt = mysqli_prepare($conn, $get_user);
mysqli_stmt_bind_param($stmt, "i", $user_id_to_delete);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($user_result);

if (!$user_data) {
    echo "User not found or not verified.";
    exit();
}

    if ($user_data) {
        mysqli_begin_transaction($conn);
        
        try {
            $archive_user = "INSERT INTO deleted_users (original_id, username, email, password, created_at, last_login, deleted_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $archive_user);
            mysqli_stmt_bind_param($stmt, "isssssi", 
                $user_data['id'],
                $user_data['username'],
                $user_data['email'],
                $user_data['password'],
                $user_data['created_at'],
                $user_data['last_login'],
                $_SESSION['user_id']
            );
            mysqli_stmt_execute($stmt);
            
            $delete_user = "DELETE FROM users WHERE id = ?";
            $stmt = mysqli_prepare($conn, $delete_user);
            mysqli_stmt_bind_param($stmt, "i", $user_id_to_delete);
            mysqli_stmt_execute($stmt);
            
            // Commit transaction
            mysqli_commit($conn);
            header("Location: dashboard.php?msg=deleted");
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            header("Location: dashboard.php?msg=error");
            exit();
        }
    } else {
        header("Location: dashboard.php?msg=not_found");
        exit();
    }
}

// Fetch current user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, created_at, last_login FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$current_user = mysqli_fetch_assoc($result);

$all_users_sql = "SELECT id, username, email, created_at, last_login, role FROM users ORDER BY created_at DESC";
$all_users_result = mysqli_query($conn, $all_users_sql);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
.dashboard-container {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: calc(100% - 40px); /* Subtracts 20px from each side */
    max-width: none; /* Removes the maximum width constraint */
    margin: 20px; /* Adds equal margin on all sides */
}

        .user-info {
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .user-info p {
            margin: 10px 0;
            font-size: 16px;
        }

        .user-info label {
            font-weight: bold;
            width: 120px;
            display: inline-block;
        }

        .button-group {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .edit-btn {
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .edit-btn:hover {
            background-color: #0056b3;
        }

        .welcome-header {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .session-info {
            font-size: 14px;
            color: #666;
            text-align: center;
            margin-top: 20px;
        }

        .all-users-section {
            margin-top: 40px;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .users-table th,
        .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .users-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .users-table tr:hover {
            background-color: #f5f5f5;
        }

        .current-user-row {
            background-color: #e8f4fe;
        }

        .section-title {
            color: #333;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .user-table th, .user-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .user-table th {
            background-color: #f5f5f5;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .edit-btn, .delete-btn {
            padding: 5px 10px;
            color: #ffffff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .edit-btn {
            background-color: #4CAF50;
            color: #ffffff;
        }
        .delete-btn {
            background-color: #f44336;
            color: #ffffff;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
        }
        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        .role-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.9em;
            font-weight: bold;
            text-transform: capitalize;
        }
        .role-badge.admin {
            background-color: #ffd700;
            color: #000;
        }
        .role-badge.user {
            background-color: #87ceeb;
            color: #000;
        }
        .unauthorized-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ff4444;
            color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1000;
            text-align: center;
        }
        .unauthorized-message button {
            margin-top: 10px;
            padding: 5px 15px;
            background-color: white;
            color: #ff4444;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<body>
    <div class="dashboard-container">
    <body>
    <!-- Display success/error messages -->
    <?php if (isset($_GET['msg'])): ?>
        <?php
        $message = '';
        $message_class = '';
        switch ($_GET['msg']) {
            case 'role_updated':
                $message = 'User role updated successfully!';
                $message_class = 'success-message';
                break;
            case 'error':
                $message = 'An error occurred while updating the user role.';
                $message_class = 'error-message';
                break;
            case 'unauthorized':
                $message = 'You are not authorized to perform this action.';
                $message_class = 'error-message';
                break;
            case 'cannot_change_own_role':
                $message = 'You cannot change your own role.';
                $message_class = 'error-message';
                break;
        }
        ?>
        <?php if ($message): ?>
            <p class="<?php echo $message_class; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    <?php endif; ?>

</body>
<body data-theme="light">
</body>
        <h1 class="welcome-header">Welcome, <?php echo htmlspecialchars($current_user['username']); ?>!</h1>
        
        <div class="user-info">
            <h2>Your Profile Information</h2>
            <p><label>Username:</label> <?php echo htmlspecialchars($current_user['username']); ?></p>
            <p><label>Email:</label> <?php echo htmlspecialchars($current_user['email']); ?></p>
            <p><label>Role:</label> <span class="role-badge <?php echo $_SESSION['role']; ?>"><?php echo ucfirst($_SESSION['role']); ?></span></p>
            <p><label>Member Since:</label> <?php echo date('F j, Y', strtotime($current_user['created_at'])); ?></p>
            <p><label>Last Login:</label> <?php echo $current_user['last_login'] ? date('F j, Y g:i A', strtotime($current_user['last_login'])) : 'First Login'; ?></p>
        </div>

        <div class="button-group">
            <a href="edit_profile.php" class="edit-btn">Edit Profile</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="all-users-section">
            <h2 class="section-title">All Registered Users</h2>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Member Since</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($user = mysqli_fetch_assoc($all_users_result)): ?>
    <tr class="<?php echo ($user['id'] == $user_id) ? 'current-user-row' : ''; ?>">
        <td><?php echo htmlspecialchars($user['username']); ?></td>
        <td><?php echo htmlspecialchars($user['email']); ?></td>
        <td>
            <?php if ($_SESSION['role'] === 'admin'): ?> <!-- Only show for admin -->
                <form action="updat.php" method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <select name="role">
                        <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                    <button type="submit">Update Role</button>
                </form>
            <?php else: ?>
                <?php echo ucfirst($user['role']); ?> <!-- Display role for non-admin users -->
            <?php endif; ?>
        </td>
        <td><?php echo date('F j, Y', strtotime($user['created_at'])); ?></td>
        <td><?php echo $user['last_login'] ? date('F j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?></td>
        <td class="action-buttons">
            <?php if ($_SESSION['role'] === 'admin' || $user['id'] == $_SESSION['user_id']): ?>
                <a href="edit_profile.php?id=<?php echo $user['id']; ?>" class="edit-btn">Edit</a>
                <?php if ($_SESSION['role'] === 'admin' && $user['id'] != $_SESSION['user_id']): ?>
                    <button class="delete-btn" onclick="showDeleteConfirmation(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">Delete</button>

                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
<?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <p class="session-info">Session will timeout after 5 minutes of inactivity</p>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete the profile of <span id="deleteUsername"></span>?</p>
            <div class="modal-buttons">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <input type="hidden" name="delete_user" value="1">
                    <button type="button" onclick="hideDeleteConfirmation()" style="background-color: #gray;">Cancel</button>
                    <button type="submit" style="background-color: #f44336;">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Load saved theme from localStorage
    document.addEventListener('DOMContentLoaded', function () {
        const body = document.body;
        const savedTheme = localStorage.getItem('theme') || 'light';
        body.setAttribute('data-theme', savedTheme);
    });

    function showDeleteConfirmation(userId, username) {
        document.getElementById('deleteModal').style.display = 'block';
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteUsername').textContent = username;
    }

    function hideDeleteConfirmation() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == document.getElementById('deleteModal')) {
            hideDeleteConfirmation();
        }
    }

    // Check for unauthorized message
    window.onload = function() {
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
        showUnauthorizedMessage();
        <?php endif; ?>
    }

    function showUnauthorizedMessage() {
        const message = document.createElement('div');
        message.className = 'unauthorized-message';
        message.innerHTML = `
            <h3>Unauthorized Access</h3>
            <p>You are not authorized to edit other users' profiles.</p>
            <button onclick="this.parentElement.remove()">OK</button>
        `;
        document.body.appendChild(message);
    }
</script>
    <?php if (isset($_GET['msg'])): ?>
        <?php 
        $message = '';
        $message_class = '';
        switch ($_GET['msg']) {
            case 'deleted':
                $message = 'User profile deleted successfully!';
                $message_class = 'success-message';
                break;
            case 'cant_delete_self':
                $message = 'You cannot delete your own account!';
                $message_class = 'error-message';
                break;
            case 'error':
                $message = 'An error occurred while deleting the user.';
                $message_class = 'error-message';
                break;
            case 'not_found':
                $message = 'User not found.';
                $message_class = 'error-message';
                break;
            case 'unauthorized':
                $message = 'You are not authorized to perform this action.';
                $message_class = 'error-message';
                break;
        }
        ?>
        <?php if ($message): ?>
            <p class="<?php echo $message_class; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    <?php endif; ?>
    <script src="script.js"></script>
</body>
</html>
