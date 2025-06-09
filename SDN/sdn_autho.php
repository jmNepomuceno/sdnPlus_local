<?php
    include("../database/connection2.php");
    // include("./csrf/session.php");

    // echo $_SESSION['_csrf_token'];

    $hospital_validity = false;

    $hospital_code = $_POST['hospital_code'];
    // $cipher_key =  $_SESSION['_csrf_token'];
    $cipher_key =  $_POST['cipher_key'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $extension_name = $_POST['extension_name'];

    $user_name = $_POST['user_name'];
    $pass_word = $_POST['pass_word'];
    $confirm_pw = $_POST['confirm_password'];

    $user_count = 0;

    $stmt_hospital_validity = $pdo->prepare('SELECT hospital_code FROM sdn_hospital WHERE hospital_code = ?');
    $stmt_hospital_validity->execute([$hospital_code]);
    $data_hospital_validity = $stmt_hospital_validity->fetchAll(PDO::FETCH_ASSOC);

    if(count($data_hospital_validity) >= 1){
        $hospital_validity = true;
        $stmt = $pdo->prepare('SELECT hospital_code FROM sdn_users WHERE hospital_code = ?');
        $stmt->execute([$hospital_code]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $user_count = count($data) + 1;
    }

    $user_type = $_POST['user_type'];
    $user_isActive= $_POST['user_isActive'];
    $created_at = $_POST['created_at'];
    $permissions = '{"setting": true, "history_log": false, "admin_function": false, "incoming_referral": false, "outgoing_referral": true, "patient_registration": true, "bucas_referral": false}';
    $role = 'rhu_account';

    $sql = "SELECT hospital_autKey FROM sdn_hospital WHERE hospital_code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hospital_code]);
    $cipher_key_given = $stmt->fetch(PDO::FETCH_ASSOC);

    if($hospital_validity == false){
        echo "not valid";
    }
    else if($cipher_key_given['hospital_autKey'] == null){
       echo "no cipher key";
    }
    else{
        if($confirm_pw == $pass_word && $user_count <= 2 && $hospital_validity == true && $cipher_key == $cipher_key_given['hospital_autKey']){
            try{
                $sql = "INSERT INTO sdn_users (hospital_code, user_lastname, user_firstname, user_middlename, user_extname, username, password, user_type, user_count, user_isActive, user_created, role, permission)
                        VALUES (?,?,?,?,?,?,?, ?,?,?,?,?,?)";
    
                // $sql = "INSERT INTO sdn_users (hospital_code, user_lastname, user_firstname, user_middlename, user_extname, username, password)
                //         VALUES (?,?,?,?,?,?,?)";
    
                // //user_type // user_count // user_isActive // user_created
    
                $stmt = $pdo->prepare($sql);
    
                $stmt->bindParam(1, $hospital_code, PDO::PARAM_INT);
                $stmt->bindParam(2, $last_name, PDO::PARAM_STR);
                $stmt->bindParam(3, $first_name, PDO::PARAM_STR);
                $stmt->bindParam(4, $middle_name, PDO::PARAM_STR);
                $stmt->bindParam(5, $extension_name, PDO::PARAM_STR);
                $stmt->bindParam(6, $user_name, PDO::PARAM_STR);
                $stmt->bindParam(7, $pass_word, PDO::PARAM_STR);
    
                $stmt->bindParam(8, $user_type, PDO::PARAM_STR);
                $stmt->bindParam(9, $user_count, PDO::PARAM_INT);
                $stmt->bindParam(10, $user_isActive, PDO::PARAM_BOOL);
                $stmt->bindParam(11, $created_at, PDO::PARAM_STR);
                $stmt->bindParam(12, $role , PDO::PARAM_STR);
                $stmt->bindParam(13, $permissions, PDO::PARAM_STR);
    
                $stmt->execute();
                echo "success";
            }catch(PDOException $e){
                // echo "Error: " . $e->getMessage();
                echo 'same_username';
            }
    
        }else{
            if($user_count > 2){
                echo "maximum";
            }else if($hospital_validity == false){
                echo "not valid";
            }else if($cipher_key != $cipher_key_given['hospital_autKey']){
                echo "invalid cipher key";
            }
        }
    }

    

    
?>