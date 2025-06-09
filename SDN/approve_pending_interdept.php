<?php
    session_start();
    include('../database/connection2.php');
    date_default_timezone_set('Asia/Manila');

    $currentDateTime = date('Y-m-d H:i:s');
    $hpercode = $_POST['hpercode'];
    $final_time = $_POST['final_time'];

    $sql = 'UPDATE incoming_referrals SET status_interdept="Approved" WHERE hpercode=:hpercode';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    $stmt->execute();

    $sql = 'UPDATE incoming_interdept SET final_progress_date=:final_progress_date , final_progress_time=:final_progress_time WHERE hpercode=:hpercode';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    $stmt->bindParam(':final_progress_date', $currentDateTime, PDO::PARAM_STR);
    $stmt->bindParam(':final_progress_time', $final_time, PDO::PARAM_STR);
    $stmt->execute();

    $sql = "UPDATE incoming_referrals SET last_update=:currentDateTime WHERE hpercode=:hpercode";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':currentDateTime', $currentDateTime, PDO::PARAM_STR);
    $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    $stmt->execute();

    $_SESSION['update_current_date'] = $currentDateTime;
?>  