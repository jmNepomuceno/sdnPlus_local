<?php
    session_start();
    include("../database/connection2.php");

    $classification = $_POST['classification'];
    $what = $_POST['what'];

    if($what == 'add'){
        $sql = "INSERT INTO classifications (classifications, class_code) VALUES (?,?)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(1, $classification, PDO::PARAM_STR);
        $stmt->bindParam(2, $classification, PDO::PARAM_STR);
        $stmt->execute();
    }else{
        $sql = "DELETE FROM classifications WHERE classifications=:classification";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':classification' => $classification]);
    }

    $sql = "SELECT * FROM classifications";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo '<pre>'; print_r($data); echo '</pre>';

?>