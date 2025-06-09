<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    $hpercode = $_POST['hpercode'];
    $department = $_POST['department'];                                                                      
    $rejected_by = $_POST['rejected_by'];                                                                      
    $rejected_time = $_POST['rejected_time'];                                                                      
    $rejected_date = $_POST['rejected_date'];    

    if($_POST['action'] == "Add"){
        $sql = "INSERT INTO reject_interdept (department, hpercode, rejected_by, rejected_time, rejected_date) VALUES (?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$department, $hpercode, $rejected_by, $rejected_time, $rejected_date]);
    }else{
        $sql = "SELECT hpercode FROM reject_interdept WHERE hpercode=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$hpercode]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);  

        if(count($data) > 0){
            $sql = "SELECT rejected_time FROM reject_interdept WHERE hpercode=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$hpercode]);
            $rejected_time = $stmt->fetch(PDO::FETCH_ASSOC);  

            $sql = "SELECT sent_interdept_time FROM incoming_referrals WHERE hpercode=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$hpercode]);
            $init_time = $stmt->fetch(PDO::FETCH_ASSOC);  

            $merge_data = array_merge($rejected_time, $init_time);

            echo json_encode($merge_data);
        }
        
    }


?>