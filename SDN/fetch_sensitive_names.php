<?php
    session_start();
    include("../database/connection2.php");

    $hpercode = $_POST['hpercode'];

    $sql = "SELECT patlast, patfirst, patmiddle, patsuffix FROM incoming_referrals WHERE hpercode=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hpercode]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    // echo '<pre>'; print_r($data); echo '</pre>';
    echo json_encode($data);
?>