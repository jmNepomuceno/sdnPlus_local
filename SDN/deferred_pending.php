<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    $timer = $_POST['timer'];
    $currentDateTime = date('Y-m-d H:i:s');
    // global_single_hpercode: BGHMC-0050 <br>timer: 00:00:04 <br>approve_details:  <br>case_category:  <br>action: Approve <br>
    // echo json_encode($_SESSION['approval_details_arr']);
    // echo '<pre>'; print_r($_SESSION['approval_details_arr']); echo '</pre>';

    // ORDER BY date_time DESC LIMIT 1

    $index = 0;
    if($_POST['type_approval'] === 'true'){
        $global_single_hpercode = filter_input(INPUT_POST, 'global_single_hpercode');
        $defer_details = filter_input(INPUT_POST, 'defer_details');

    }else{
        foreach ($_SESSION['approval_details_arr'] as $index => $element) {
            if ($element['hpercode'] == $_POST['global_single_hpercode']) {
                // Found the matching element
                break; // Stop looping once found
            }else{
                $index += 1;
            }
        }
        // C:\Users\ACER\Documents\dumps
    
        // $_SESSION['approval_details_arr'][] = array(
        //     'hpercode' => $_POST['global_single_hpercode'],
        //     'category' => $_POST['case_category'] , 
        //     'defer_details' => $_POST['defer_details']
        // );

        $global_single_hpercode = $_SESSION['approval_details_arr'][$index]['hpercode'];
        $defer_details = $_SESSION['approval_details_arr'][$index]['defer_details'];
    }

    // if hpercode has duplicates
    $sql = "SELECT date_time FROM incoming_referrals WHERE hpercode='". $global_single_hpercode ."' ORDER BY date_time DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $latest_referral = $stmt->fetch(PDO::FETCH_ASSOC);  

    if($_POST['action'] === "Defer"){ 
        $sql = "UPDATE incoming_referrals SET status='Deferred' WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time=:date_time";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
        $stmt->bindParam(':pat_class', $pat_class, PDO::PARAM_STR);
        $stmt->bindParam(':date_time', $latest_referral['date_time'], PDO::PARAM_STR);
        // $latest_referral
        $stmt->execute();
    }else{
        $sql = "UPDATE incoming_referrals SET status='Deferred', pat_class=:pat_class WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time=:date_time";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
        $stmt->bindParam(':pat_class', $pat_class, PDO::PARAM_STR);
        $stmt->bindParam(':date_time', $latest_referral['date_time'], PDO::PARAM_STR);
        $stmt->execute();
    }

    $timer = filter_input(INPUT_POST, 'timer');
    $sql_b = "UPDATE incoming_referrals SET final_progressed_timer=:timer WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time=:date_time";
    $stmt_b = $pdo->prepare($sql_b);
    $stmt_b->bindParam(':timer', $timer, PDO::PARAM_STR);
    $stmt_b->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
    $stmt_b->bindParam(':date_time', $latest_referral['date_time'], PDO::PARAM_STR);
    $stmt_b->execute();

    // update the approved_details and set the time of approval on the database
    if($_POST['action'] === "Approve"){
        $sql = "UPDATE incoming_referrals SET approval_details=:approve_details, approved_time=:approved_time, progress_timer=NULL, refer_to_code=NULL WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time=:date_time";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':approve_details', $approve_details, PDO::PARAM_STR); // currentDateTime
        $stmt->bindParam(':approved_time', $currentDateTime, PDO::PARAM_STR);
        $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
        $stmt->bindParam(':date_time', $latest_referral['date_time'], PDO::PARAM_STR);
        $stmt->execute();
    }else{
        $sql = "UPDATE incoming_referrals SET deferred_details=:approve_details, deferred_time=:approved_time WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time=:date_time";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':approve_details', $approve_details, PDO::PARAM_STR); // currentDateTime
        $stmt->bindParam(':approved_time', $currentDateTime, PDO::PARAM_STR);
        $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
        $stmt->bindParam(':date_time', $latest_referral['date_time'], PDO::PARAM_STR);
        $stmt->execute();
    }

    // echo $global_single_hpercode . "---" . $_POST['action'] . "---";

    // update also the status of the patient on the hperson table
    $sql = "SELECT type FROM incoming_referrals WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if($_POST['action'] === "Approve"){
        $sql = "UPDATE hperson SET status='Approved', type='". $data['type'] ."' WHERE hpercode=:hpercode ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
        $stmt->execute();
    }
    else{
        $sql = "UPDATE hperson SET status='Deferred', type='". $data['type'] ."' WHERE hpercode=:hpercode ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
        $stmt->execute();
    }

    $sql = "SELECT patlast, patfirst, patmiddle FROM incoming_referrals WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // updating for history log
    $act_type = 'pat_refer';
    $history_stats = "";
    if($_POST['action'] === "Approve"){
        $history_stats = "Approved";
    }else{
        $history_stats = "Deferred";
    }
    $action = 'Status Patient: ' . $history_stats;
    $pat_name = $data[0]['patlast'] . ' ' . $data[0]['patfirst'] . ' ' . $data[0]['patmiddle'];
    $sql = "INSERT INTO history_log (hpercode, hospital_code, date, activity_type, action, pat_name, username) VALUES (?,?,?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(1, $global_single_hpercode, PDO::PARAM_STR);
    $stmt->bindParam(2, $_SESSION['hospital_code'], PDO::PARAM_INT);
    $stmt->bindParam(3, $currentDateTime, PDO::PARAM_STR);
    $stmt->bindParam(4, $act_type, PDO::PARAM_STR);
    $stmt->bindParam(5, $action, PDO::PARAM_STR);
    $stmt->bindParam(6, $pat_name, PDO::PARAM_STR);
    $stmt->bindParam(7, $_SESSION['user_name'], PDO::PARAM_STR);
    $stmt->execute();

    //get all the pending or on-process status on the database to populate the data table after the approval
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
        $sql = "SELECT * FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND refer_to='". $_SESSION["hospital_name"] ."' ORDER BY date_time ASC";
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
                $pat_full_name = $row['patlast'] . ", " . $row['patfirst'] . " " . $row['patmiddle'];
            }

            echo '<tr class="tr-incoming" style="'. $style_tr .'">
                    <td id="dt-refer-no"> ' . $row['reference_num'] . ' - '.$index.' </td>
                    <td id="dt-patname">' . $pat_full_name . '</td>
                    <td id="dt-type" style="background:' . $type_color . ' ">' . $row['type'] . '</td>
                    <td id="dt-phone-no">
                        <div class="">
                            <p> Referred: ' . $row['referred_by'] . '  </p>
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
                    <td id="dt-stopwatch">
                        <div id="stopwatch-sub-div">
                            Processing: <span class="stopwatch">'.$stopwatch.'</span>
                        </div>
                    </td>
                    
                    <td id="dt-status">
                        <div> 
                            <label class="pat-status-incoming">' . $row['status'] . '</label>';
                            if ($row['sensitive_case'] === 'true') {
                                echo '<i class="pencil-btn fa-solid fa-pencil" style="pointer-events:none; opacity:0.3; color:#cc9900;"></i>';
                            }else{
                                echo'<i class="pencil-btn fa-solid fa-pencil" style="color:#cc9900;"></i>';
                            }
                            
                            echo '<input class="hpercode" type="hidden" name="hpercode" value= ' . $row['hpercode'] . '>

                        </div>
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

    if(count($data) === 0){
        $_SESSION["running_timer"] = "";
    }

    if($_POST['type_approval'] === 'false'){
        foreach ($_SESSION['approval_details_arr'] as $index => $element) {
            if ($element['hpercode'] == $_POST['global_single_hpercode']) {
                // Found the matching element, delete it
                unset($_SESSION['approval_details_arr'][$index]);
                break; // Stop looping once found
            }
        }
        $_SESSION['approval_details_arr'] = array_values($_SESSION['approval_details_arr']);
    }

    // refresh the value of the session timers
    $_SESSION['running_timer'] = 0; // elapsedTime
    $_SESSION['running_bool'] = false;
    $_SESSION['running_startTime'] = null;

    $_SESSION['running_hpercode'] = "";
    $_SESSION['running_index'] = null;
    $_SESSION['datatable_index'] = null;

    // if($_SESSION['datatable_index'] != "" && $_SESSION['datatable_index'] != null){
    //     $_SESSION['datatable_index'] = (int)$_SESSION['datatable_index'] - 1;
    // }else{
    //     $_SESSION['datatable_index'] = null;
    // }

    // if($_SESSION['running_index'] != "" && $_SESSION['running_index'] != null){
    //     $_SESSION['running_index'] = (int)$_SESSION['running_index'] - 1;
    // }else{
    //     $_SESSION['running_index'] = null;
    // }

    $_SESSION['datatable_index'] = 0;
    $_SESSION['running_index'] = 0;

?>
