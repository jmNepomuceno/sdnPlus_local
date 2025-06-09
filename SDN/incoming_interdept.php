<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    // check first if the hpercode is already on the session array
    
    $_SESSION['approval_details_arr'][] = array(
        'hpercode' => $_POST['hpercode'],
        'category' => $_POST['case_category'] , 
        'approve_details' => $_POST['approve_details']
    );
    // insert the data into incoming_interdept
    $dept = $_POST['dept'];
    $currentDateTime = date('Y-m-d H:i:s');
    $hpercode = $_POST['hpercode'];
    $pause_time = $_POST['pause_time'];
    $pat_class = $_POST['case_category'];

    $sql = "INSERT INTO incoming_interdept (department, hpercode, referred_time, recept_time, unRead, interdept_status) VALUES (?,?,?,NULL,1,'Pending')";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(1, $dept, PDO::PARAM_STR);
    $stmt->bindParam(2, $hpercode, PDO::PARAM_STR);
    $stmt->bindParam(3, $currentDateTime, PDO::PARAM_STR);
    $stmt->execute();

    //update the status of the patient in the table of incoming_referrals
    $sql = "UPDATE incoming_referrals SET status_interdept='Pending' , sent_interdept_time=:pause_time, pat_class=:pat_class WHERE hpercode=:hpercode";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    $stmt->bindParam(':pause_time', $pause_time, PDO::PARAM_STR);
    $stmt->bindParam(':pat_class', $pat_class, PDO::PARAM_STR);
    $stmt->execute();

    $sql = "SELECT * FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND refer_to='". $_SESSION["hospital_name"] ."' ORDER BY date_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT patfirst, patmiddle, patlast FROM incoming_referrals WHERE hpercode=? AND refer_to='". $_SESSION["hospital_name"] ."'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hpercode]);
    $latest_referral = $stmt->fetch(PDO::FETCH_ASSOC);

     // history_log
     $pat_name = $latest_referral['patlast'] . ', ' . $latest_referral['patfirst'] . ' ' . $latest_referral['patmiddle'];
     $sql = "INSERT INTO history_log (hpercode, hospital_code, date, activity_type, action, pat_name, username) VALUES (?,?,?,?,?,?,?)";
     $stmt = $pdo->prepare($sql);
     $stmt->execute([$hpercode , $_SESSION['hospital_code'], $currentDateTime , "pat_refer" , "Status Patient: Pending - " . $dept, $pat_name, $_SESSION['user_name']]);

    // refresh the value of the session timers
    $_SESSION['running_timer'] = 0; // elapsedTime
    $_SESSION['running_bool'] = false;
    $_SESSION['running_startTime'] = null;

    $_SESSION['running_hpercode'] = "";
    $_SESSION['running_index'] = null;
    
    $arr = [$_SESSION['running_timer'] , $_SESSION['running_bool'] , $_SESSION['running_startTime'] , $_SESSION['running_hpercode'] , $_SESSION['running_index'] , $pause_time];

    $response = json_encode($arr);
    echo $response;
?>