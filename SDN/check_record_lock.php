<?php
    session_start();
    include('../database/connection2.php');
    date_default_timezone_set('Asia/Manila');

    $hpercode = $_POST['hpercode'];

    $sql = "SELECT whoLocked FROM incoming_referrals WHERE hpercode='". $hpercode ."' ORDER BY date_time DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $whoLocked = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND refer_to=? ORDER BY date_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["hospital_name"]]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $_SESSION['prev_inc_ref_total'] = count($data);

    if($whoLocked['whoLocked'] == $_SESSION['first_name'] . ' ' . $_SESSION['last_name']){
        echo json_encode([]);
    }else{
        echo json_encode($whoLocked);
    }


?>