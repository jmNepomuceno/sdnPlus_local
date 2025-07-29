<?php 
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    $referralId = $_POST['referralId'];
    $currentDateTime = date('Y-m-d H:i:s');
    $doctor = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

    $sql = "UPDATE incoming_referrals SET status='Duplicate', final_progressed_timer='00:00:00', deferred_time=:curr_date, deferred_details='Duplicate Referral', reception_time=:curr_date, dateLocked=:curr_date, whoLocked=:doctor, processed_by=:doctor WHERE referral_id=:referralId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':curr_date', $currentDateTime);
    $stmt->bindParam(':doctor', $doctor);
    $stmt->bindParam(':referralId', $referralId);

    $stmt->execute();

    // how to echo the success message
    // echo json_encode(["success" => true]);
    $sql = "SELECT classifications FROM classifications";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_classifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $color = ["#d77707" , "#22c45e" , "#0368a1" , "#cf3136" , "#919122" , "#999966" , "#6666ff"];
    $dynamic_classification = [];
    for($i = 0; $i < count($data_classifications); $i++){
        $dynamic_classification[$data_classifications[$i]['classifications']] = $color[$i];
    } 

    $indexing = 0;
    
    $sql = "SELECT ir.*, sh.hospital_director, sh.hospital_director_mobile, sh.hospital_point_person, sh.hospital_point_person_mobile
            FROM incoming_referrals ir
            LEFT JOIN sdn_hospital sh ON ir.referred_by = sh.hospital_name
            WHERE (ir.status = 'Pending' OR ir.status = 'On-Process') 
            AND ir.refer_to = ?
            ORDER BY ir.date_time ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["hospital_name"]]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $index = 0;
    $previous = 0;
    $loop = 0;
    // Loop through the data and generate table rows
    $hpercodeCounts = array_count_values(array_column($data, 'hpercode'));
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
        if($row['sent_interdept_time'] == "00:00:00"){
            if($_SESSION['running_timer'] != "" && $row['status'] == 'On-Process'){
                $seconds  = (int)$_SESSION['running_timer'] + 1;
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $remainingSeconds = floor($seconds % 60);

                $stopwatch = sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
                // $stopwatch = gettype($_SESSION['running_timer']);

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
                    <p> " . $row['patlast'] . " , " . $row['patfirst'] . "  " . $row['patmiddle'] . "</p>
                    <input class='sensitive-hpercode' type='hidden' name='sensitive-hpercode' value= '" . $row['hpercode'] . "'>
                </div>
            ";
        }

        $interdept_section = "Interdept";
        $interdept_referred_time = "0000:00:00 00:00:00";
        $interdept_recept_time = "0000:00:00 00:00:00";

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
                        
                        echo '<input class="hpercode" type="hidden" name="hpercode" value= ' . $row['hpercode'] . '>

                    </div>
                </td>
                <td colspan="8" id="dt-action">
                        <button type="button" class="btn btn-secondary toggle-contact-btn">More Details</button>';

                        if ($hpercodeCounts[$row['hpercode']] > 1) {
                            echo '<button type="button" class="btn btn-danger delete-duplicate-btn" data-referral_id="' . $row['referral_id'] . '">Delete Duplicate</button>';
                        }

                echo '</td>
            </tr>';


        $previous = $row['reference_num'];
        $loop += 1;
    }

    // echo json_encode($data)
?>

