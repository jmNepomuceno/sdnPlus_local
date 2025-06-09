<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    $currentDateTime = date('Y-m-d H:i:s');

    // update the approved_details and set the time of approval on the database
    $sql = "UPDATE incoming_referrals SET status='Deferred', deferred_details='". $_POST['arrival_details']."', arrival_time='". $currentDateTime ."' WHERE hpercode='". $_POST['global_single_hpercode']."' AND refer_to = '" . $_SESSION["hospital_name"] . "'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // get all the pending or on-process status on the database to populate the data table after the approval
    $sql = "SELECT * FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND refer_to = '" . $_SESSION["hospital_name"] . "'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);  

    $jsonString = json_encode($data);
    echo $jsonString;
?>