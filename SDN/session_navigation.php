<?php 
    session_start();
    include('../database/connection2.php');

    $_SESSION['session_navigation'] = $_POST['nav_path'];
    echo json_encode($_SESSION['session_navigation']);
    
?>