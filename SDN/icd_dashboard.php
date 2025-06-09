<?php 

    session_start();
    include("../database/connection2.php");

    $icd_code = $_POST['icd_code'];
   
   
    $sql = "SELECT COUNT(*) as count FROM incoming_referrals WHERE icd_diagnosis=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$icd_code]);
    $total_icd = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($total_icd);
?>