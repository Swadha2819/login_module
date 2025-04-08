<?php
ini_set('display_errors', 0); // Optional: hide errors on screen
error_reporting(E_ALL);

session_start();
require_once 'bd.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php?msg=unauthorized");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $new_role = mysqli_real_escape_string($conn, $_POST['role']);

    // Prevent admins from changing their own role
    if ($user_id == $_SESSION['user_id']) {
        header("Location: dashboard.php?msg=cannot_change_own_role");
        exit();
    }

    // Update the user's role in the database
    $sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $new_role, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: dashboard.php?msg=role_updated");
        exit();
    } else {
        header("Location: dashboard.php?msg=error");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
