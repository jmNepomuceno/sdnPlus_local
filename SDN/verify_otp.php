<?php 
    include("../database/connection2.php");
    session_start();

    $otp_number = $_POST['otp_number'];
    $hospital_code = $_SESSION['hospital_code'];
    $verify = true;
    //FETCH THE WHOLE ROW
    $sql = "SELECT hospital_OTP FROM sdn_hospital WHERE hospital_code=:code ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':code', $hospital_code, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>'; print_r($data); echo '</pre>';
    $user_OTP = $data[0]['hospital_OTP'];

    if($user_OTP == $otp_number){
        //update the row with verified = TRUE
        $sql = "UPDATE sdn_hospital SET hospital_isVerified = :verify WHERE hospital_code=:hospital_code";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hospital_code', $hospital_code, PDO::PARAM_INT);
        $stmt->bindParam(':verify', $verify, PDO::PARAM_BOOL);

        $stmt->execute();
        echo "verified";
    }else{
        echo "not verified";
    }
?>