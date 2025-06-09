<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    $currentDateTime = date('Y-m-d H:i:s');

    // update the approved_details and set the time of approval on the database
    $cancel_details = filter_input(INPUT_POST, 'cancel_details');
    $global_single_hpercode = filter_input(INPUT_POST, 'global_single_hpercode');
    
    $sql = "UPDATE incoming_referrals SET status='Cancelled', cancellation_details=:cancel_details, cancellation_time=:timer WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':timer', $currentDateTime, PDO::PARAM_STR);
    $stmt->bindParam(':cancel_details', $cancel_details, PDO::PARAM_STR); // currentDateTime
    $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
    $stmt->execute();

    // get all the pending or on-process status on the database to populate the data table after the approval
    $sql = "SELECT * FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND refer_to = '" . $_SESSION["hospital_name"] . "'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);  


    $jsonString = json_encode($data);
    echo $jsonString;

?>