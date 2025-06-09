<?php 
    session_start();
    include('../database/connection2.php');

    if($_POST['from'] == 'incoming'){
        $_SESSION['running_timer'] = $_POST['formattedTime'];
        $_SESSION['running_hpercode'] = $_POST['hpercode'];
        $_SESSION['patient_status'] = "";
    }else{
        $_SESSION['running_timer'] = $_POST['formattedTime'];
        $_SESSION['running_hpercode'] = $_POST['hpercode'];

        $formattedTime = $_POST['formattedTime'];
        $hpercode = $_POST['hpercode'];

        $sql = "UPDATE incoming_interdept SET curr_time=:formattedTime WHERE hpercode=:hpercode";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':formattedTime', $formattedTime, PDO::PARAM_STR);
        $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
        $stmt->execute();
    }
    echo $_SESSION['running_timer'];
?>