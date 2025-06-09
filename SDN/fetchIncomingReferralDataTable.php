<?php
    session_start();
    include('../database/connection2.php');

    $sql = "SELECT classifications FROM classifications";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_classifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $color = ["#d77707" , "#22c45e" , "#0368a1" , "#cf3136" , "#919122" , "#999966" , "#6666ff"];
    $dynamic_classification = [];
    for($i = 0; $i < count($data_classifications); $i++){
        $dynamic_classification[$data_classifications[$i]['classifications']] = $color[$i];
    }

    // echo '<pre>'; print_r($data); echo '</pre>';

    try{
        $sql = "SELECT * FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND refer_to='". $_SESSION["hospital_name"] ."' ORDER BY date_time ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data_index = 0;
        $index = 0;
        $previous = 0;
        $loop = 0;

        foreach ($data as $row){
            
            $type_color = $dynamic_classification[$row['type']];

            if($previous == 0){
                $index += 1;
            }else{
                if($row['reference_num'] == $previous){
                    $index += 1;
                }else{
                    $index = 1;
                }  
            }


            $style_tr = '';
            if($loop != 0 &&  $row['status'] === 'Pending'){
                $style_tr = 'opacity:0.5; pointer-events:none;';
            }

            $date1 = new DateTime($row['date_time']);
            $waiting_time_bd = "";
            if($row['reception_time'] != null){
                $date2 = new DateTime($row['reception_time']);
                $waiting_time = $date1->diff($date2);

                // if ($waiting_time->days > 0) {
                //     $differenceString .= $waiting_time->days . ' days ';
                // }

                $waiting_time_bd .= sprintf('%02d:%02d:%02d', $waiting_time->h, $waiting_time->i, $waiting_time->s);

            }else{
                $waiting_time_bd = "00:00:00";
            }

            if($row['reception_time'] == ""){
                $row['reception_time'] = "00:00:00";
            }

            if($row['status_interdept'] != "" && $row['status_interdept'] != null){
                $sql = "SELECT department FROM incoming_interdept WHERE hpercode='". $row['hpercode'] ."'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $data = $stmt->fetch(PDO::FETCH_ASSOC);

                $row['status'] = $row['status_interdept'] . " - " . strtoupper($data['department']);
            }
            // processed time = progress time ng admin + progress time ng dept
            // maiiwan yung timer na naka print, once na send na sa interdept
            
            $sql = "SELECT final_progress_time FROM incoming_interdept WHERE hpercode=:hpercode";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':hpercode', $row['hpercode'], PDO::PARAM_STR);
            $stmt->execute();
            $interdept_time = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total_time = "00:00:00";
            if($interdept_time){
                if($interdept_time[0]['final_progress_time'] != "" && $row['sent_interdept_time'] != ""){
                    list($hours1, $minutes1, $seconds1) = array_map('intval', explode(':', $interdept_time[0]['final_progress_time']));
                    list($hours2, $minutes2, $seconds2) = array_map('intval', explode(':', $row['sent_interdept_time']));

                    // Create DateTime objects in UTC with the provided hours, minutes, and seconds
                    $date1 = new DateTime('1970-01-01 ' . $hours1 . ':' . $minutes1 . ':' . $seconds1, new DateTimeZone('UTC'));
                    $date2 = new DateTime('1970-01-01 ' . $hours2 . ':' . $minutes2 . ':' . $seconds2, new DateTimeZone('UTC'));

                    // Calculate the total milliseconds
                    $totalMilliseconds = $date1->getTimestamp() * 1000 + $date2->getTimestamp() * 1000;

                    // Create a new DateTime object in UTC with the total milliseconds
                    $newDate = new DateTime('@' . ($totalMilliseconds / 1000), new DateTimeZone('UTC'));

                    // Format the result in UTC time "HH:mm:ss"
                    $total_time = $newDate->format('H:i:s');

                    
                    $data[$data_index]['type_color'] = $type_color;
                    $data[$data_index]['style_tr'] = $style_tr;
                    $data[$data_index]['waiting_time_bd'] = $waiting_time_bd;
                    $data[$data_index]['total_time'] = $total_time;
                }
            }else{
                $interdept_time[0]['final_progress_time'] = "00:00:00";
                $row['sent_interdept_time'] = "00:00:00";

                $data[$data_index]['type_color'] = $type_color;
                $data[$data_index]['style_tr'] = $style_tr;
                $data[$data_index]['waiting_time_bd'] = $waiting_time_bd;
                $data[$data_index]['interdept_time'] = "00:00:00";
            }


            if($row['approved_time'] == ""){
                $row['approved_time'] = "0000-00-00 00:00:00";
            }

            if($interdept_time[0]['final_progress_time'] == ""){
                $interdept_time[0]['final_progress_time'] = "00:00:00";
            }

            if($row['sent_interdept_time'] == ""){
                $row['sent_interdept_time'] = "00:00:00";
            }

            $stopwatch = "00:00:00";
            if($row['sent_interdept_time'] == "00:00:00"){
                if($_SESSION['running_timer'] != "" && $row['status'] == 'On-Process'){
                    $stopwatch  = $_SESSION['running_timer'];
                }
            }else{
                $stopwatch  = $row['sent_interdept_time'];
            }

            $data[$data_index]['stopwatch'] = $stopwatch;

            // for sensitive case
            $pat_full_name = "";
            if($row['sensitive_case'] === 'true'){
                $pat_full_name = "<button id='sensitive-case-btn'> Sensitive Case </button>";
            }else{
                $pat_full_name = $row['patlast'] . ", " . $row['patfirst'] . " " . $row['patmiddle'];
            }

            $data[$data_index]['pat_full_name'] = $pat_full_name;
            $data[$data_index]['index'] = $index;
            $data[$data_index]['reception_time'] = $row['reception_time'];
            $data[$data_index]['sent_interdept_time'] = $row['sent_interdept_time'];
            $data[$data_index]['interdept_time'] = $interdept_time[0]['final_progress_time'];
            $data[$data_index]['total_time'] = $total_time;
            $data[$data_index]['approved_time'] = $row['approved_time'];

            $data_index += 1;
            $previous = $row['reference_num'];
            $loop += 1;
        }

        // echo '<pre>'; print_r($data); echo '</pre>';
    }catch(PDOException $e){
        echo "asdf";
    }

    echo json_encode($data);
?>