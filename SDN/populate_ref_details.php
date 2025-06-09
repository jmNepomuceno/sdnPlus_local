<?php
    include("../database/connection2.php");
    
    $hpercode = $_POST['hpercode'];

    $sql = "SELECT * FROM hperson WHERE hpercode=:hpercode";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $finalJsonString = json_encode($data);
    echo $finalJsonString;
?>