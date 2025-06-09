<?php
    session_start();
    include('../database/connection2.php');
    date_default_timezone_set('Asia/Manila');

    $currentDateTime = date('Y-m-d H:i:s');
    $hpercode = $_POST['hpercode'];

    $sql = 'SELECT status_interdept, sent_interdept_time FROM incoming_referrals WHERE hpercode=:hpercode';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = 'SELECT hpercode,curr_time, department, final_progress_time, final_progress_date, approved_by, rejected_by FROM incoming_interdept WHERE hpercode=:hpercode ORDER BY id DESC LIMIT 1' ;
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    $stmt->execute();
    $data_2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "UPDATE incoming_referrals SET last_update=:currentDateTime WHERE hpercode=:hpercode";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':currentDateTime', $currentDateTime, PDO::PARAM_STR);
    $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    $stmt->execute();

    $data[0]['currentDateTime'] = $currentDateTime;
    $response = array_merge($data, $data_2);
    $response = json_encode($response);
    echo $response;

    $_SESSION['update_current_date'] = $currentDateTime;
?>  