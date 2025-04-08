<?php
require_once 'vendor/google-api-php-client--PHP8.0/vendor/autoload.php';

// Ensure you installed Google API Client via Composer
session_start();

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

$_SESSION['user_id'] = $userInfo->id;
$_SESSION['name'] = $userInfo->name;
$_SESSION['email'] = $userInfo->email;
$_SESSION['picture'] = $userInfo->picture;

// Redirect to dashboard or any authenticated page
header("Location: dashboard.php");
exit();
?>

