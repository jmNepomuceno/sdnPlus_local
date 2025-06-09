<?php 

    session_start();
    include("../database/connection2.php");

    $start_date = $_POST['from_date'];
    $end_date = $_POST['to_date'];
    $end_date_adjusted = date('Y-m-d', strtotime($end_date . ' +1 day'));

    // get all the refer from hospitals
    $sql = "SELECT referred_by FROM incoming_referrals WHERE refer_to = :hospital_name AND date_time >= '$start_date' AND date_time < '$end_date_adjusted' ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hospital_name', $_SESSION['hospital_name']); 
    $stmt->execute();
    $dataReferFrom = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // $dataReferFrom_json = json_encode($dataReferFrom);

    $sql = "SELECT pat_class FROM incoming_referrals WHERE refer_to = :hospital_name AND date_time >= '$start_date' AND date_time < '$end_date_adjusted' ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hospital_name', $_SESSION['hospital_name']); 
    $stmt->execute();
    $dataPatClass = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // $dataPatClass_json = json_encode($dataPatClass);

    $sql = "SELECT type FROM incoming_referrals WHERE refer_to = :hospital_name AND date_time >= '$start_date' AND date_time < '$end_date_adjusted' ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hospital_name', $_SESSION['hospital_name']); 
    $stmt->execute();
    $dataPatType = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // $dataPatType_json = json_encode($dataPatType);

    $curr_array  = array_merge($dataReferFrom, $dataPatClass , $dataPatType);
    $curr_array_json = json_encode($curr_array);
    echo $curr_array_json;
?>