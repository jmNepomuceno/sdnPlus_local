<?php
    session_start();
    include('../database/connection2.php');
    date_default_timezone_set('Asia/Manila');

    $hpercode = $_POST['hpercode'];
    $newStatus = $_POST['newStatus'];
    $currentDateTime = date('Y-m-d H:i:s');

    $sql = "UPDATE incoming_referrals SET status=?, discharged_time=? WHERE hpercode=? ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$newStatus, $currentDateTime, $hpercode]);

    $sql = "UPDATE hperson SET status=? WHERE hpercode=? ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$newStatus, $hpercode]);

    // include
    echo "success";
?>