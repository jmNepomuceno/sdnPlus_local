<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    $what = $_POST['what'];


    if($what === 'save'){
        $currentDate = date("Y-m-d H:i:s"); // Current date and time

        $sql = "UPDATE sdn_users SET user_lastLoggedIn=:curr_date, user_isActive='0' WHERE username=:username AND password=:password";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $_SESSION['user_name'], PDO::PARAM_STR);
        $stmt->bindParam(':password', $_SESSION['user_password'], PDO::PARAM_STR);
        $stmt->bindParam(':curr_date', $currentDate, PDO::PARAM_STR);
        $stmt->execute();

        if($_POST['sub_what'] === 'logout'){
            $act_type = 'user_login';
            $pat_name = " ";
            $hpercode = " ";
            $action = 'offline';
            $user_name = $_SESSION['user_name'];
            $sql = "INSERT INTO history_log (hpercode, hospital_code, date, activity_type, action, pat_name, username) VALUES (?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(1, $hpercode, PDO::PARAM_STR);
            $stmt->bindParam(2, $_SESSION['hospital_code'], PDO::PARAM_INT);
            $stmt->bindParam(3, $currentDate, PDO::PARAM_STR);
            $stmt->bindParam(4, $act_type, PDO::PARAM_STR);
            $stmt->bindParam(5, $action, PDO::PARAM_STR);
            $stmt->bindParam(6, $pat_name, PDO::PARAM_STR);
            $stmt->bindParam(7, $user_name, PDO::PARAM_STR);

            $stmt->execute();

            $_SESSION = []; // Clear session variables

            // Expire session cookie if it exists
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
        }

        // else if($_POST['sub_what'] === 'history_log'){
        //     $_SESSION["sub_what"] = 'history_log';
        // }

        // $sql = "UPDATE history_log SET date=:curr_date, user_isActive='0' WHERE username=:username AND password=:password";
        // $stmt = $pdo->prepare($sql);
        // $stmt->bindParam(':username', $_SESSION['user_name'], PDO::PARAM_STR);
        // $stmt->bindParam(':password', $_SESSION['user_password'], PDO::PARAM_STR);
        // $stmt->bindParam(':curr_date', $currentDate, PDO::PARAM_STR);
        // $stmt->execute();

        
        // echo $_SESSION['user_name'] . " " . $_SESSION['user_password'];
    }

    // if($what === 'continue'){
    //     $_SESSION['running_bool'] = "true";

    //     $sql = "SELECT hpercode,status,progress_timer,logout_date FROM incoming_referrals WHERE progress_timer!='' AND refer_to = '" . $_SESSION["hospital_name"] . "'";
    //     $stmt = $pdo->prepare($sql);
    //     $stmt->execute();
    //     $data = $stmt->fetchAll(PDO::FETCH_ASSOC);



    //     for($i = 0; $i < count($data); $i++){
    //         $givenDate = $data[$i]['logout_date'];

    //         // Create DateTime objects for the given date and the current date
    //         $givenDateTime = DateTime::createFromFormat('Y/m/d H:i:s', $givenDate);
    //         $currentDateTime = new DateTime(); // This gets the current date and time

    //         // Calculate the difference between the two DateTime objects
    //         $interval = $currentDateTime->diff($givenDateTime);

    //         // Convert the difference to seconds
    //         $differenceInSeconds = (
    //             ($interval->days * 24 * 60 * 60) +      // Days to seconds
    //             ($interval->h * 60 * 60) +              // Hours to seconds
    //             ($interval->i * 60) +                   // Minutes to seconds
    //             $interval->s                            // Seconds
    //         );

    //         $data[$i]['progress_timer'] = (int)$data[$i]['progress_timer'] + $differenceInSeconds;
    //     }
        
    //     $jsonString = json_encode($data);
    //     echo $jsonString;

    //     // delete the progress_timer after sending back the request
    //     // dapat yung referralID kukunin mo hindi yung hpercode

    //     for($i = 0; $i < count($data); $i++){
    //         $sql = "UPDATE incoming_referrals SET progress_timer = NULL WHERE hpercode = ?";
    //         $stmt = $pdo->prepare($sql);
    //         $stmt->execute([$data[$i]['hpercode']]);
    //     }
        
    // }

?>