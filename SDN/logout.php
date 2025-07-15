<?php 
    session_start();
    session_unset();
    session_destroy();
    $_SESSION = array();

    // Expire the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000, '/');
    }

    exit()
?>