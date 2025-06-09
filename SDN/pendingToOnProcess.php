<?php
    session_start();
    include('../database/connection2.php');
    date_default_timezone_set('Asia/Manila');

    $currentDateTime = date('Y-m-d H:i:s');
    // $hpercode = $_POST['hpercode'];
    // if($_POST['from'] === 'incoming'){
    //     $sql = "UPDATE incoming_referrals SET status='On-Process' WHERE hpercode= '". $hpercode ."' ";
    // }else{
    //     $sql = "UPDATE incoming_referrals SET status_interdept='On-Process' WHERE hpercode= '". $hpercode ."' ";
    // }
    // $stmt = $pdo->prepare($sql);
    // $stmt->execute();


    $hpercode = $_POST['hpercode'];

    // if hpercode has duplicates, get the last referral by date_time
    $sql = "SELECT date_time, patlast, patmiddle, patfirst FROM incoming_referrals WHERE hpercode='". $hpercode ."' ORDER BY date_time DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $latest_referral = $stmt->fetch(PDO::FETCH_ASSOC);

    $dr_full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    
    if($_POST['from'] === 'incoming'){
        $sql = "UPDATE incoming_referrals SET status='On-Process', isLocked=1, whoLocked='".$dr_full_name."', dateLocked='".$currentDateTime."' WHERE hpercode= '". $hpercode ."' AND date_time='". $latest_referral['date_time'] ."'";
    }
    else{
        $sql = "UPDATE incoming_referrals SET status_interdept='On-Process' WHERE hpercode= '". $hpercode ."' ";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // history_log
    $pat_name = $latest_referral['patlast'] . ', ' . $latest_referral['patfirst'] . ' ' . $latest_referral['patmiddle'];
    $sql = "INSERT INTO history_log (hpercode, hospital_code, date, activity_type, action, pat_name, username) VALUES (?,?,?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hpercode , $_SESSION['hospital_code'], $currentDateTime , "pat_refer" , "Status Patient: On-Process", $pat_name, $_SESSION['user_name']]);

    $sql = "SELECT isLocked FROM incoming_referrals WHERE whoLocked!=? AND isLocked!=0 ORDER BY date_time DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['first_name'] . ' ' . $_SESSION['last_name']]);
    $isLocked = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($isLocked) > 0){
        $_SESSION['any_referral_locked'] = true;
    }else{
        $_SESSION['any_referral_locked'] = false;
    }

    echo $_SESSION['any_referral_locked'];
?>