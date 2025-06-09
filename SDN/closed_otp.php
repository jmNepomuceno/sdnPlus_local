<?php
    include("../database/connection2.php");

    $hospital_code = $_POST['hospital_code'];

    $sql = "DELETE FROM sdn_hospital WHERE hospital_code=:hospital_code";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hospital_code', $hospital_code, PDO::PARAM_INT);
    if ($stmt->execute()) { 
        echo 'deleted';
    }
?>