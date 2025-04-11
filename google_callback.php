<?php
require_once 'vendor/google-api-php-client--PHP8.0/vendor/autoload.php';
require_once 'bd.php'; // Ensure database connection is included

$client = new Google_Client();
$client->setClientId('785784893264-jak123jkqf2r0pk6cvqtqoirjhu0n0jb.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost:8080/login_module/google_callback.php');
$client->addScope('email');
$client->addScope('profile');

if (!isset($_GET['code'])) {
    die('Error: Authorization code not received.');
}

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    die('Error fetching access token: ' . $token['error']);
}

$client->setAccessToken($token);

$oauth2 = new Google_Service_Oauth2($client);
$userInfo = $oauth2->userinfo->get();

// Get user data from Google
$email = $userInfo->email;
$name = $userInfo->name;

// Check if user exists in the database
$check_sql = "SELECT id, role FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user) {
    // User exists
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
} else {
    // Insert new Google user
    $insert_sql = "INSERT INTO users (username, email, is_verified, created_at, role) VALUES (?, ?, 1, NOW(), 'user')";
    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, "ss", $name, $email);
    mysqli_stmt_execute($stmt);
    $_SESSION['user_id'] = mysqli_insert_id($conn);
    $_SESSION['role'] = 'user';
}

// Set other session values
$_SESSION['name'] = $name;
$_SESSION['email'] = $email;
$_SESSION['picture'] = $userInfo->picture;

// Redirect to dashboard
header("Location: dashboard.php");
exit();
?>
