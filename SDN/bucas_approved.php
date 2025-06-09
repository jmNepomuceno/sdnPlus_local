<?php
    include("../database/connection2.php");
    session_start();

    $sql = "UPDATE incoming_referrals SET status='Approved', pat_class=:pat_class, processed_by=:processed_by WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time=:date_time";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
    $stmt->bindParam(':pat_class', $pat_class, PDO::PARAM_STR);
    $stmt->bindParam(':processed_by', $processed_by, PDO::PARAM_STR);
    $stmt->bindParam(':date_time', $latest_referral['date_time'], PDO::PARAM_STR);
    // $latest_referral
    $stmt->execute();

    echo json_encode($_POST);
?>