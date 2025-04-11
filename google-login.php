<?php
require_once __DIR__ . '/vendor/autoload.php'; 
 // Include Composer autoload

session_start();

$client = new Google_Client();
$client->setClientId('785784893264-jak123jkqf2r0pk6cvqtqoirjhu0n0jb.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost:8080/login_module/google_callback.php');
$client->setAccessType('offline');
$client->setPrompt('consent');
$client->addScope("email");
$client->addScope("profile");

// Get the OAuth URL
$auth_url = $client->createAuthUrl();

// Redirect the user to Google login
header('Location:'.filter_var($auth_url, FILTER_SANITIZE_URL));
exit;
