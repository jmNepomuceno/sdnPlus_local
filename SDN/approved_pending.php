<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');
    ob_start();

    $timer = $_POST['timer'];
    $currentDateTime = date('Y-m-d H:i:s');
    $processed_by = $_POST['processed_by'];
    $dr_full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    $indexToDelete = $_POST['index'];
    $hpercode = $_POST['hpercode'];
    $index_unset = 0;
    
    $value_to_delete = $indexToDelete; // Replace with the value you want to delete

    for($i = 0; $i < count($_SESSION['running_index']); $i++){
        if($_SESSION['running_index'][$i] == $value_to_delete){
            $index_unset = $i;
        }
    }
    
    // Find the index of the value to delete
    $index_to_delete = array_search($value_to_delete, $_SESSION['running_index']);
    if ($index_to_delete === false) {
        // Value not found in the array
        echo "Value not found.";
    } else {
        // Check if the value to delete is closer to the left or the right
        if ($index_to_delete <= floor(count($_SESSION['running_index']) / 2)) {
            // Value is closer to the left
            unset($_SESSION['running_index'][$index_to_delete]); // Remove the specified value
            $_SESSION['running_index'] = array_values($_SESSION['running_index']); // Re-index the array
    
            // Adjust the remaining elements
            $_SESSION['running_index'] = array_map(function ($item) use ($value_to_delete) {
                return $item > $value_to_delete ? $item - 1 : $item;
            }, $_SESSION['running_index']);
        } else {
            // Value is closer to the right
            unset($_SESSION['running_index'][$index_to_delete]); // Remove the specified value
            $_SESSION['running_index'] = array_values($_SESSION['running_index']); // Re-index the array
        }
    }

    // [0,2,7]
    // [17, 28, 25]
    //  0    1   2

    unset($_SESSION['running_timer'][$index_unset]);
    unset($_SESSION['running_startTime'][$index_unset]);
    unset($_SESSION['running_hpercode'][$index_unset]);


    $index = 0;
    if($_POST['type_approval'] === 'true'){
        $pat_class = $_POST['case_category'];
        $global_single_hpercode = filter_input(INPUT_POST, 'global_single_hpercode');
        $approve_details = filter_input(INPUT_POST, 'approve_details');

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
        //     'approve_details' => $_POST['approve_details']
        // );

        $pat_class = $_SESSION['approval_details_arr'][$index]['category'];
        $global_single_hpercode = $_SESSION['approval_details_arr'][$index]['hpercode'];
        $approve_details = $_SESSION['approval_details_arr'][$index]['approve_details'];
    }

    // if hpercode has duplicates
    $sql = "SELECT date_time FROM incoming_referrals WHERE hpercode='". $global_single_hpercode ."' ORDER BY date_time DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $latest_referral = $stmt->fetch(PDO::FETCH_ASSOC);  

    if($_POST['action'] === "Approve"){ 
        $sql = "UPDATE incoming_referrals SET status='Approved', pat_class=:pat_class, processed_by=:processed_by WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time=:date_time";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
        $stmt->bindParam(':pat_class', $pat_class, PDO::PARAM_STR);
        $stmt->bindParam(':processed_by', $processed_by, PDO::PARAM_STR);
        $stmt->bindParam(':date_time', $latest_referral['date_time'], PDO::PARAM_STR);
        // $latest_referral
        $stmt->execute();
    }else{
        $sql = "UPDATE incoming_referrals SET status='Deferred', pat_class=:pat_class, processed_by=:processed_by WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time=:date_time";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
        $stmt->bindParam(':pat_class', $pat_class, PDO::PARAM_STR);
        $stmt->bindParam(':processed_by', $processed_by, PDO::PARAM_STR);
        $stmt->bindParam(':date_time', $latest_referral['date_time'], PDO::PARAM_STR);
        $stmt->execute();
    }

    $timer = filter_input(INPUT_POST, 'timer');
    // 
    $sql_b = "UPDATE incoming_referrals SET final_progressed_timer=:timer WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time=:date_time";
    $stmt_b = $pdo->prepare($sql_b);
    $stmt_b->bindParam(':timer', $timer, PDO::PARAM_STR);
    $stmt_b->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
    $stmt_b->bindParam(':date_time', $latest_referral['date_time'], PDO::PARAM_STR);
    $stmt_b->execute();

    // update the approved_details and set the time of approval on the database
    if($_POST['action'] === "Approve"){
        $sql = "UPDATE incoming_referrals SET approval_details=:approve_details, approved_time=:approved_time, progress_timer=NULL, refer_to_code=NULL , isLocked=False WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time=:date_time";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':approve_details', $approve_details, PDO::PARAM_STR); // currentDateTime
        $stmt->bindParam(':approved_time', $currentDateTime, PDO::PARAM_STR);
        $stmt->bindParam(':hpercode', $global_single_hpercode, PDO::PARAM_STR);
        $stmt->bindParam(':date_time', $latest_referral['date_time'], PDO::PARAM_STR);
        $stmt->execute();
    }else{
        $sql = "UPDATE incoming_referrals SET deferred_details=:approve_details, deferred_time=:approved_time , isLocked=False WHERE hpercode=:hpercode AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time=:date_time";
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

    $sql = "SELECT isLocked FROM incoming_referrals WHERE whoLocked!=? AND isLocked!=0 ORDER BY date_time DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['first_name'] . ' ' . $_SESSION['last_name']]);
    $isLocked = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($isLocked) > 0){
        $_SESSION['any_referral_locked'] = true;
    }else{
        $_SESSION['any_referral_locked'] = false;

    }

    // SQL query to fetch data from your table
    $indexing = 0;
    try{
        $sql = "SELECT ir.*, sh.hospital_director, sh.hospital_director_mobile, sh.hospital_point_person, sh.hospital_point_person_mobile
                FROM incoming_referrals ir
                LEFT JOIN sdn_hospital sh ON ir.referred_by = sh.hospital_name
                WHERE (ir.status = 'Pending' OR ir.status = 'On-Process') 
                AND ir.refer_to = ?
                ORDER BY ir.date_time ASC";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION["hospital_name"]]);
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
                $sql = "SELECT department FROM incoming_interdept WHERE hpercode='". $row['hpercode'] ."' ORDER BY id DESC LIMIT 1";
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
            
            if(!$interdept_time){
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
            // if($row['sent_interdept_time'] == "00:00:00"){
            //     if(isset($_SESSION['running_timer'][$indexing]) && $row['status'] == 'On-Process'){
            //         $stopwatch  = $_SESSION['running_timer'][$indexing];
            //     }
            // }else{
            //     $stopwatch  = $row['sent_interdept_time'];
            // }

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
                        <p> " . $row['patlast'] . " , " . $row['patfirst'] . "  " . $row['patmiddle'] . "</p>
                        <input class='sensitive-hpercode' type='hidden' name='sensitive-hpercode' value= '" . $row['hpercode'] . "'>
                    </div>
                ";
            }

            // $interdept_section = "Interdept";
            // $interdept_referred_time = "0000:00:00 00:00:00";
            // $interdept_recept_time = "0000:00:00 00:00:00";

            // $sql = "SELECT * FROM incoming_interdept WHERE hpercode=:hpercode";
            // $stmt = $pdo->prepare($sql);
            // $stmt->bindParam(':hpercode', $row['hpercode'], PDO::PARAM_STR);
            // $stmt->execute();
            // $interdept_dept = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // if(isset($interdept_dept[0])){
            //     $interdept_section = $interdept_dept[0]['department'];
            //     $interdept_referred_time = $interdept_dept[0]['referred_time'];
            //     $interdept_recept_time = $interdept_dept[0]['recept_time'];
                
               

            //     $start = new DateTime($interdept_dept[0]['final_progress_date']);
            //     $end = new DateTime($interdept_recept_time);

            //     $interval = $start->diff($end);

            //     $interdept_time[0]['final_progress_time'] = $interval->format('%H:%I:%S');

            //     list($hours1, $minutes1, $seconds1) = array_map('intval', explode(':', $interdept_time[0]['final_progress_time']));
            //     list($hours2, $minutes2, $seconds2) = array_map('intval', explode(':', $row['sent_interdept_time']));

            //     // Create DateTime objects in UTC with the provided hours, minutes, and seconds
            //     $date1 = new DateTime('1970-01-01 ' . $hours1 . ':' . $minutes1 . ':' . $seconds1, new DateTimeZone('UTC'));
            //     $date2 = new DateTime('1970-01-01 ' . $hours2 . ':' . $minutes2 . ':' . $seconds2, new DateTimeZone('UTC'));

            //     // Calculate the total milliseconds
            //     $totalMilliseconds = $date1->getTimestamp() * 1000 + $date2->getTimestamp() * 1000;

            //     // Create a new DateTime object in UTC with the total milliseconds
            //     $newDate = new DateTime('@' . ($totalMilliseconds / 1000), new DateTimeZone('UTC'));

            //     // Format the result in UTC time "HH:mm:ss"
            //     $total_time = $newDate->format('H:i:s');
            // }else{
            //     $total_time = $row['final_progressed_timer'];
            // }

            $sql = "SELECT pat_province, pat_barangay, pat_municipality, pat_age FROM hperson WHERE hpercode=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$row['hpercode']]);
            $data_pat_municipality = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = "SELECT municipality_description FROM city WHERE municipality_code=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data_pat_municipality['pat_municipality']]);
            $data_city = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = "SELECT barangay_description FROM barangay WHERE barangay_code=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data_pat_municipality['pat_barangay']]);
            $data_brgy = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = "SELECT province_description FROM provinces WHERE province_code=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data_pat_municipality['pat_province']]);
            $data_province = $stmt->fetch(PDO::FETCH_ASSOC);

            echo '<tr class="tr-incoming" style="'.$style_tr.'">
                    <td id="dt-refer-no"> ' . $row['reference_num'] . ' - '.$index.' </td>
                    <td id="dt-patname">' . $pat_full_name . ' 
                        <span id="pat-address-span"> Address: '.$data_province['province_description'] .', '. $data_city['municipality_description'].' , '. $data_brgy['barangay_description'].' </span> 
                        <span id="pat-age-span"> Age: '.$data_pat_municipality['pat_age'].' </span> 
                    </td>
                    <td id="dt-type" style="background:' . $type_color . ' ">' . $row['type'] . '</td>
                    <td id="dt-phone-no">
                        <div class="">
                            <p> <b>Referred by:</b> ' . $row['referred_by'] . '  </p>
                            <p> <b>Landline:</b> ' . $row['landline_no'] . ' </p>
                            <p> <b>Mobile:</b> ' . $row['mobile_no'] . ' </p>

                            <div class="contact-extra" style="display:none;">
                                <p> <b>Director:</b> ' . $row['hospital_director'] . '  </p>
                                <p> <b>Director No.:</b> ' . $row['hospital_director_mobile'] . '  </p>
                                <p> <b>Point Person:</b> ' . $row['hospital_point_person'] . ' </p>
                                <p> <b>Point Person No.:</b> ' . $row['hospital_point_person_mobile'] . ' </p>
                            </div>

                        </div>
                    </td>
                    <td id="dt-turnaround"> 
                        <p class="referred-time-lbl"> Referred: ' . $row['date_time'] . ' </p>
                        <p class="reception-time-lbl"> Reception: '. $row['reception_time'] .'</p>
                        <p class="sdn-proc-time-lbl"> SDN Processed: '. $row['sent_interdept_time'] .'</p>
                        
                        <div class="contact-extra" style="display:none;">
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
                            <p class="pat-status-incoming">' . $row['status'] . '</p>';
                            if ($row['sensitive_case'] === 'true') {
                                echo '<i class="pencil-btn fa-solid fa-pencil" style="pointer-events:none; opacity:0.3; color:#cc9900;"></i>';
                            }else{
                                echo'<i class="pencil-btn fa-solid fa-pencil" style="color:#cc9900;"></i>';
                            }
                            
                            echo '<input class="hpercode" type="hidden" name="hpercode" value="' . $row["hpercode"] . '">
                                    <input class="referral_id" type="hidden" name="referral_id" value="' . $row["referral_id"] . '">
                        </div>
                    </td>
                    <td colspan="8" id="dt-action">
                        <button type="button" class="btn btn-secondary toggle-contact-btn">More Details</button>
                    </td>
                </tr>';

            $previous = $row['reference_num'];
            $loop += 1;
            $indexing += 1;
        }

        // Close the database connection
        $pdo = null;
    }
    catch(PDOException $e){
        echo "asdf";
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

    $table_html = ob_get_clean(); 
    $response = [
        "table_html" => $table_html, // The HTML table rows
        "running_timer_var" => array_values($_SESSION['running_timer']),
        "running_startTime_var" => array_values($_SESSION['running_startTime']),
        "running_hpercode_var" => array_values($_SESSION['running_hpercode']),
        "running_index_var" => array_values($_SESSION['running_index']),
        "key" => count($isLocked)
    ];

    echo json_encode($response);

    $_SESSION['prev_inc_ref_total'] = $_SESSION['prev_inc_ref_total'] - 1;

?>