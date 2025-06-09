<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    $currentDateTime = date('Y-m-d H:i:s');
    $drFullName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

    // $sql = "SELECT COUNT(*) FROM incoming_referrals WHERE status='On-Process' AND refer_to='Bataan General Hospital and Medical Center' AND whoLocked='".$drFullName."'";
    if($_SESSION['user_role'] != 'rhu_account' && $_SESSION['user_role'] != 'admin'){
        $sql = "SELECT COUNT(*) FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND refer_to='Bataan General Hospital and Medical Center'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $on_process_count = $stmt->fetch(PDO::FETCH_ASSOC);

        echo $on_process_count['COUNT(*)'];
    }else{
        echo 0;
    }

?>