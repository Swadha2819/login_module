<?php
define('GOOGLE_CLIENT_ID', '785784893264-g21f57bq2n99fsjp4naqqo1id8g2pge6.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost:8080/login_module/google-callback.php');

$google_oauth_scope = ['email', 'profile'];

$db_host = "localhost";     
$db_user = "root";         
$db_pass = "";            
$db_name = "login_system";

if (!session_id()) {
    session_start();
}
//require_once 'google-api-php-client/vendor/autoload.php';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>