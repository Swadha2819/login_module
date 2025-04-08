<?php
function secureSession() {
    $secure = true; // for HTTPS only
    $httponly = true; // prevent JavaScript access
    $samesite = 'Strict'; // prevent CSRF
    
    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
    
    // Start the session only if it hasn't already been started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Other security headers
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
}

function checkSession() {
    $timeout = 300; // 5 minutes

    // Check if session timeout variable exists
    if (isset($_SESSION['last_activity'])) {
        $inactive_time = time() - $_SESSION['last_activity'];
        
        if ($inactive_time >= $timeout) {
            session_unset();
            session_destroy();
            header("Location: lin.php?msg=timeout");
            exit();
        }
    }
    
    $_SESSION['last_activity'] = time();
}
?>