<?php 
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    $currentDateTime = date('Y-m-d H:i:s');

    $hpercode = $_POST['hpercode'];
    $datatable_index = $_POST['datatable_index'];

    // history log
    $sql = "SELECT patlast, patfirst, patmiddle FROM incoming_referrals WHERE hpercode=:hpercode AND referred_by = '" . $_SESSION["hospital_name"] . "'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $act_type = 'pat_ref_cancel';
    $action = 'Status Patient: Cancelled';
    $pat_name = $data[0]['patlast'] . ' ' . $data[0]['patfirst'] . ' ' . $data[0]['patmiddle'];
    $sql = "INSERT INTO history_log (hpercode, hospital_code, date, activity_type, action, pat_name, username) VALUES (?,?,?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(1, $hpercode, PDO::PARAM_STR);
    $stmt->bindParam(2, $_SESSION['hospital_code'], PDO::PARAM_INT);
    $stmt->bindParam(3, $currentDateTime, PDO::PARAM_STR);
    $stmt->bindParam(4, $act_type, PDO::PARAM_STR);
    $stmt->bindParam(5, $action, PDO::PARAM_STR);
    $stmt->bindParam(6, $pat_name, PDO::PARAM_STR);
    $stmt->bindParam(7, $_SESSION['user_name'], PDO::PARAM_STR);
    $stmt->execute();
    

    $sql = "UPDATE incoming_referrals SET cancellation_request='Pending' WHERE hpercode=:hpercode";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    $stmt->execute();

    // either we delete the whole row of the referral request or, just archived
    $sql = "DELETE FROM incoming_referrals WHERE hpercode=:hpercode";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':hpercode' => $hpercode]);
    
    $sql = "UPDATE hperson SET referral_id=null, type=null, status=null WHERE hpercode=:hpercode";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    $stmt->execute();

    // fetch current outgoing
    // get the classification names
    $sql = "SELECT classifications FROM classifications";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_classifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $color = ["#d77707" , "#22c45e" , "#0368a1" , "#cf3136" , "#919122" , "#999966" , "#6666ff"];
    $dynamic_classification = [];
    for($i = 0; $i < count($data_classifications); $i++){
        $dynamic_classification[$data_classifications[$i]['classifications']] = $color[$i];
    }

    // SQL query to fetch data from your table
    try{
        $sql = "SELECT * FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND referred_by='". $_SESSION["hospital_name"] ."' ORDER BY date_time ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // echo count($data);
        $jsonData = json_encode($data);

        $index = 0;
        $previous = 0;
        $loop = 0;
        // Loop through the data and generate table rows
        foreach ($data as $row) {
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
            
            // $style_tr = 'background:#33444d; color:white;';
            $style_tr = '';
            if($loop != 0 &&  $row['status'] === 'Pending'){
                $style_tr = 'opacity:0.5; pointer-events:none;';
            }

            // $waiting_time = "--:--:--";
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
                }
            }else{
                $interdept_time[0]['final_progress_time'] = "00:00:00";
                $row['sent_interdept_time'] = "00:00:00";
                // $total_time = $row['final_progressed_timer'];
            }

            // echo($total_time);

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

                    $seconds = (int)$stopwatch;

                        // // Calculate hours, minutes, and seconds
                        $hours = floor($seconds / 3600);
                        $minutes = floor((int)($seconds / 60) % 60);
                        $seconds = $seconds % 60;

                        // // Format the time as HH:MM:SS
                        $stopwatch = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                }
            }else{
                $stopwatch  = $row['sent_interdept_time'];
            }

            // for sensitive case
            $pat_full_name = ""; 
            if($row['sensitive_case'] === 'true'){
                $pat_full_name = "
                    <div class='pat-full-name-div'>
                        <button class='sensitive-case-btn'> <i class='sensitive-lock-icon fa-solid fa-lock'></i> Sensitive Case </button>
                        <input class='sensitive-hpercode' type='hidden' name='sensitive-hpercode' value= '" . $row['hpercode'] . "'>
                    </div>
                ";
            }else{
                // $pat_full_name = $row['patlast'] . ", " . $row['patfirst'] . " " . $row['patmiddle'];
                $pat_full_name = "
                    <div class='pat-full-name-div'>
                        <button class='sensitive-case-btn' style='display:none;'> <i class='sensitive-lock-icon fa-solid fa-lock'></i> Sensitive Case </button>
                        <label> " . $row['patlast'] . " , " . $row['patfirst'] . "  " . $row['patmiddle'] . "</label>
                        <input class='sensitive-hpercode' type='hidden' name='sensitive-hpercode' value= '" . $row['hpercode'] . "'>
                    </div>
                ";
            }

            echo '<tr class="tr-incoming" style="'. $style_tr .'">
                    <td id="dt-refer-no"> ' . $row['reference_num'] . ' - '.$index.' </td>
                    <td id="dt-patname">' . $pat_full_name . '</td>
                    <td id="dt-type" style="background:' . $type_color . ' ">' . $row['type'] . '</td>
                    <td id="dt-phone-no">
                        <div class="">
                            <p> Referred by: ' . $row['referred_by'] . '  </p>
                            <p> Landline: ' . $row['landline_no'] . ' </p>
                            <p> Mobile: ' . $row['mobile_no'] . ' </p>
                        </div>
                    </td>
                    <td id="dt-turnaround"> 
                        <i class="accordion-btn fa-solid fa-plus"></i>

                        <p class="referred-time-lbl"> Referred: ' . $row['date_time'] . ' </p>
                        <p class="reception-time-lbl"> Reception: '. $row['reception_time'] .'</p>
                        <p class="sdn-proc-time-lbl"> SDN Processed: '. $row['sent_interdept_time'] .'</p>
                        
                        <div class="breakdown-div">
                            <p class="interdept-proc-time-lbl"> Interdept Processed: '. $interdept_time[0]['final_progress_time'].'</p>
                            <p class="processed-time-lbl"> Total Processed: '.$total_time.'  </p>  
                            <p> Approval: '.$row['approved_time'] .'  </p>  
                            <p> Deferral: 0000-00-00 00:00:00  </p>  
                            <p> Cancelled: 0000-00-00 00:00:00  </p>  
                        </div>
                    </td>

                    <td id="dt-status">
                        <div> 
                            <p class="pat-status-incoming">' . $row['status'] . '</p>';
                            if ($row['sensitive_case'] === 'true') {
                                echo '<i class="pencil-btn fa-solid fa-pencil" style="pointer-events:none; opacity:0.3; color:#cc9900;"></i>';
                            }else{
                                echo'<i class="pencil-btn fa-solid fa-pencil" style="color:#cc9900;"></i>';
                            }
                            
                            echo '<input class="hpercode" type="hidden" name="hpercode" value= ' . $row['hpercode'] . '>

                        </div>
                    </td>

                    <td id="dt-cancel">
                        <button> Cancel </button>
                    </td>
                </tr>';

        
                $previous = $row['reference_num'];
            $loop += 1;
        }

        // Close the database connection
        $pdo = null;
    }
    catch(PDOException $e){
        echo "asdf";
    }

    // history log
    // $sql = "SELECT patlast, patfirst, patmiddle FROM incoming_referrals WHERE hpercode=:hpercode AND referred_by = '" . $_SESSION["hospital_name"] . "'";
    // $stmt = $pdo->prepare($sql);
    // $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
    // $stmt->execute();
    // $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>