<?php

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

// Add more utility functions as needed

?>