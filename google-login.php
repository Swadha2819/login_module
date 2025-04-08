<?php
require_once 'vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php'; 
 // Include Composer autoload

session_start();

$client = new Google_Client();
$client->setClientId('785784893264-g21f57bq2n99fsjp4naqqo1id8g2pge6.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost:8080/login_module/google_callback.php'); // e.g., http://localhost/google-login.php
$client->addScope("email");
$client->addScope("profile");

// Get the OAuth URL
$auth_url = $client->createAuthUrl();

// Redirect the user to Google login
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit;