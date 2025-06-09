<?php
    session_start();
    include("../database/connection2.php");

    $action = $_POST['action'];

    if($action === 'add'){
        $fname = $_POST['first_name'];
        $lname = $_POST['last_name'];
        $mname = $_POST['middle_name'];
        $mobile = $_POST['mobile'];

        $sql = "INSERT INTO doctors_list (last_name, first_name, middle_name, hospital_code, mobile_number, status) VALUES (?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$lname, $fname, $mname, $_SESSION['hospital_code'], $mobile, "Active"]);
    }
    else{
        $index = $_POST['index'];

        $sql = "DELETE FROM doctors_list WHERE mobile_number=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$index]);
    }

    $sql = "SELECT * FROM doctors_list WHERE hospital_code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['hospital_code']]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $temp_1 = "";
    $temp_2 = "";   
    $temp_3 = "";

    for($i = 0; $i < count($data); $i++){
        $name = $data[$i]['last_name'] . ', ' . $data[$i]['first_name'] . ' ' . $data[$i]['middle_name'] . ' ';
        $mobile_number = $data[$i]['mobile_number'];
        $status = $data[$i]['status'];

        $style_color = "#ffffff";
        $text_color = "#1f292e";
        if($i % 2 == 1){
            $style_color = "#d3dbde"; 
            $text_color = "#ffffff";
        }

        echo '
            <div class="doctor-div" style="background: '. $style_color .'">
                <div>
                    <h3> '. $name .' </h3>
                </div>

                <div>
                    <h3 class="mobile-num-h3">'. $mobile_number .'</h3>
                </div>

                <div>
                    <button class="text-base"> <span id="status-login">'. $status .'</span></button>
                </div>

                <div>
                    <button class="remove-doctor-btn">Remove</button>
                </div>
            </div>
        ';
    }

?>