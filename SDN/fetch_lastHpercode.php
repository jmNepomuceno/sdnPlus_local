<?php
    include("../database/connection2.php");
    session_start();

    $sql = "SELECT hpercode FROM incoming_referrals ORDER BY id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $hperson_data = $stmt->fetch(PDO::FETCH_ASSOC); 

    $dr_full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    
    $jsonData = json_encode([$hperson_data , $dr_full_name]);
    echo $jsonData;
?>