<?php
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

// Function to generate a CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Function to validate a CSRF token
function validateCSRFToken($token) {
    if (!isset($_SESSION['_csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['_csrf_token'], $token);
}

// Function to sanitize user input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Set session-related configurations
ini_set('session.cookie_lifetime', 0); 
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_httponly', true);
ini_set('session.cookie_secure', true);
ini_set('session.cookie_samesite', 'Lax');

// Define a constant for the CSRF token key (change this to a unique name)
define('CSRF_TOKEN_KEY', '_csrf_token');

// Start session and set timezone
session_start();
date_default_timezone_set('Asia/Manila');

// Generate CSRF token if not already set
if (!isset($_SESSION['_csrf_token'])) {
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}

// Monitor the session and echo _csrf_token
// if (isset($_SESSION['_csrf_token'])) {
//     echo "CSRF Token: " . $_SESSION['_csrf_token'];
// } else {
//     echo "CSRF Token not set";
// }

// Get the session ID
$sessionID = session_id();

// Set the session ID as a cookie
setcookie('PHPSESSID', $sessionID, 0, '/', '', true, true);

// Monitor the session and echo the session ID and CSRF token
// echo "Session ID: " . $sessionID . "<br>";
// echo "CSRF Token: " . $_SESSION['_csrf_token'];

?>
