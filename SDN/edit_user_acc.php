<?php 
    include("../database/connection2.php");
    session_start();

    $prev_first_name = $_POST['prev_first_name'];
    $prev_last_name = $_POST['prev_last_name'];
    $prev_middle_name = $_POST['prev_middle_name'];
    $prev_username = $_POST['prev_username'];
    $prev_password = $_POST['prev_password'];

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $hospital_code = (int)$_POST['hospital_code'];   

    $sql = "UPDATE sdn_users 
        SET 
            user_lastname = :last_name, 
            user_firstname = :first_name,
            user_middlename = :middle_name,
            username = :username,
            password = :password 
        WHERE 
            hospital_code = :hospital_code 
            AND user_lastname = :prev_last_name 
            AND user_firstname = :prev_first_name 
            AND user_middlename = :prev_middle_name 
            AND username = :prev_username 
            AND password = :prev_password";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hospital_code', $hospital_code, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
        $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
        $stmt->bindParam(':middle_name', $middle_name, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);

        $stmt->bindParam(':prev_last_name', $prev_last_name, PDO::PARAM_STR);
        $stmt->bindParam(':prev_first_name', $prev_first_name, PDO::PARAM_STR);
        $stmt->bindParam(':prev_middle_name', $prev_middle_name, PDO::PARAM_STR);
        $stmt->bindParam(':prev_username', $prev_username, PDO::PARAM_STR);
        $stmt->bindParam(':prev_password', $prev_password, PDO::PARAM_STR);

        $stmt->execute();


        $sql = "SELECT * FROM sdn_users WHERE hospital_code =2002";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $fdsa = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($fdsa);
?>