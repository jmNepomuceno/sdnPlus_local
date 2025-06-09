<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    $currentDateTime = date('Y-m-d H:i:s');

    // update the approved_details and set the time of approval on the database
    $arrival_details = filter_input(INPUT_POST, 'arrival_details');
    $global_single_hpercode = filter_input(INPUT_POST, 'global_single_hpercode');

    $sql = "UPDATE incoming_referrals SET status='Arrived', arrival_details=:arrival_details, arrival_time=:timer WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':arrival_details', $arrival_details, PDO::PARAM_STR); // currentDateTime
    $stmt->bindParam(':timer', $currentDateTime, PDO::PARAM_STR);
    $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
    $stmt->execute();

    // get all the pending or on-process status on the database to populate the data table after the approval
    $sql = "SELECT * FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND refer_to = '" . $_SESSION["hospital_name"] . "'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);  


    $jsonString = json_encode($data);
    echo $jsonString;

    // history log

    $sql = "SELECT patlast, patfirst, patmiddle FROM incoming_referrals WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $act_type = 'pat_refer';
    $action = 'Status Patient: Arriving';
    $pat_name = $data[0]['patlast'] . ' ' . $data[0]['patfirst'] . ' ' . $data[0]['patmiddle'];
    $sql = "INSERT INTO history_log (hpercode, hospital_code, date, activity_type, action, pat_name, username) VALUES (?,?,?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(1, $global_single_hpercode, PDO::PARAM_STR);
    $stmt->bindParam(2, $_SESSION['hospital_code'], PDO::PARAM_INT);
    $stmt->bindParam(3, $currentDateTime, PDO::PARAM_STR);
    $stmt->bindParam(4, $act_type, PDO::PARAM_STR);
    $stmt->bindParam(5, $action, PDO::PARAM_STR);
    $stmt->bindParam(6, $pat_name, PDO::PARAM_STR);
    $stmt->bindParam(7, $_SESSION['user_name'], PDO::PARAM_STR);

    $stmt->execute();

?>