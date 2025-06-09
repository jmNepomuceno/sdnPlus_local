<?php 
    session_start();
    include("../database/connection2.php");

    $from_where = $_POST['where'];

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

    if($from_where === "search"){
        $ref_no = $_POST['ref_no'];
        $last_name = $_POST['last_name'];
        $first_name = $_POST['first_name'];
        $middle_name = $_POST['middle_name'];
        $case_type = $_POST['case_type'];
        $agency = $_POST['agency'];
        $status = $_POST['status'];
        // $status = 'Pending';
        if(isset($_POST['hpercode_arr'])){
            $_SESSION['fifo_hpercode'] = $_POST['hpercode_arr'];   
        }

        $sql = "SELECT 
            ir.hpercode, ir.status, ir.type, ir.reference_num, ir.date_time, 
            ir.reception_time, ir.sent_interdept_time, ir.approved_time, 
            ir.deferred_time, ir.final_progressed_timer, ir.sensitive_case, 
            ir.patlast, ir.patfirst, ir.patmiddle, ir.referred_by, 
            ir.landline_no, ir.mobile_no,
            
            hp.pat_age,
            prov.province_description,
            city.municipality_description,
            brgy.barangay_description
        FROM incoming_referrals AS ir
        LEFT JOIN hperson AS hp ON ir.hpercode = hp.hpercode
        LEFT JOIN provinces AS prov ON hp.pat_province = prov.province_code
        LEFT JOIN city AS city ON hp.pat_municipality = city.municipality_code
        LEFT JOIN barangay AS brgy ON hp.pat_barangay = brgy.barangay_code
        WHERE ";

        $conditions = array();
        $others = false;

        if (!empty($ref_no)) {
            $conditions[] = "ir.reference_num LIKE '%". $ref_no ."%'";
            $others = true;
        }

        if (!empty($last_name)) {
            $conditions[] = "ir.patlast LIKE '%". $last_name ."%' ";
            $others = true;
        }

        if (!empty($first_name)) {
            $conditions[] = "ir.patfirst LIKE '%". $first_name ."%' ";
            $others = true;
        }

        if (!empty($middle_name)) {
            $conditions[] = "ir.patmiddle LIKE '%". $middle_name ."%' ";
            $others = true;
        }

        if (!empty($case_type)) {
            $conditions[] = "ir.type = '" . $case_type . "'"; 
            $others = true;
        }

        if (!empty($agency)) {
            $conditions[] = "ir.referred_by = '" . $agency . "'";
            $others = true;
        } 

        if($status != "default" && $status!="All"){
            $conditions[] = "ir.status = '" . $status . "'";
            $others = false;
        }

        if (count($conditions) > 0) {
            $sql .= implode(" AND ", $conditions);
        }
        
        $hospital_condition = '';
        if ($_POST['where_type'] == 'incoming') {
            $hospital_condition = "refer_to = '" . $_SESSION["hospital_name"] . "'";
        } else {
            $hospital_condition = "referred_by = '" . $_SESSION["hospital_name"] . "'";
        }
        
        // Append hospital condition properly
        if (count($conditions) > 0) {
            $sql .= " AND " . $hospital_condition;
        } else {
            $sql .= $hospital_condition;
        }
        
        $sql .= " ORDER BY date_time DESC";

        
    
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // echo $sql;
        // $jsonString = json_encode($data);
        // echo $jsonString;

        
        $index = 0;
        $previous = 0;
        $loop = 0;
        $accord_index = 0;
        // Loop through the data and generate table rows`

        if($_POST['where_type'] == 'incoming'){
            $sql = "SELECT * FROM incoming_referrals WHERE (status='On-Process' OR status='Pending') AND refer_to = '" . $_SESSION["hospital_name"] . "' ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $on_process = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            foreach ($on_process as $row) {
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
                // if($loop != 0 &&  $row['status'] === 'Pending'){
                //     $style_tr = 'opacity:0.5; pointer-events:none;';
                // }
    
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
                    </tr>';
    
            
                $previous = $row['reference_num'];
                $loop += 1;
            }
    
            
            foreach ($data as $row) {
                if(isset($_POST['hpercode_arr'])){
                    if(in_array($row['hpercode'], $_SESSION['fifo_hpercode']) && $row['status'] != 'Approved'){
                        continue;
                    }
                }
    
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
                
                $sql = "SELECT final_progress_time FROM incoming_interdept WHERE hpercode=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$row['hpercode']]);
                $interdept_time = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $total_time = "00:00:00";
                                    
                if(!$interdept_time){
                    $interdept_time[0]['final_progress_time'] = "00:00:00";
                    $row['sent_interdept_time'] = "00:00:00";
                }
    
    
                if($row['approved_time'] == ""){
                    $row['approved_time'] = "0000-00-00 00:00:00";
                }
    
                if($row['deferred_time'] === "" || $row['deferred_time'] === null){
                    $row['deferred_time'] = "0000-00-00 00:00:00";
                }
    
                if($interdept_time[0]['final_progress_time'] == ""){
                    $interdept_time[0]['final_progress_time'] = "00:00:00";
                }
    
                $sdn_processed_value = "";
                if($row['sent_interdept_time'] == ""){
                    $row['sent_interdept_time'] = "00:00:00";
                    $sdn_processed_value = $row['final_progressed_timer'];
                }else{
                    $sdn_processed_value =  $row['sent_interdept_time'];
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
    
                
                $interdept_section = "Interdept";
                $interdept_referred_time = "0000:00:00 00:00:00";
                $interdept_recept_time = "0000:00:00 00:00:00";
                $total_time = $row['final_progressed_timer'];
    
                $date1 = new DateTime($row['date_time']);
                $date2 = new DateTime($row['reception_time']);
    
                // Calculate the difference
                $interval = $date2->getTimestamp() - $date1->getTimestamp();
    
                // Convert the difference to minutes and seconds
                $totalMinutes = floor($interval / 60);
                $seconds = $interval % 60;
    
                // Format the result to mm:ss or mmm:ss
                $reception_addition = sprintf("%d:%02d", $totalMinutes, $seconds);
                $reception_addition_style = "orange";
                // $reception_addition_style = ($interval >= 900) ? "red" : "green";
    
    
                $total_time_style = "";
                if($total_time != "00:00:00"){
                    list($hours, $minutes, $seconds) = array_map('intval', explode(':', $total_time));
                    $total_time_in_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
                    if($total_time_in_seconds >= 900){
                        $total_time_style = "color:red; font-weight:bold;";
                    }else{
                        $total_time_style = "color:green; font-weight:bold;";
                    }
                }   
    
                //
    
                
                echo'<tr class="tr-incoming" style="'.$style_tr.'">
                        <td id="dt-refer-no"> ' . $row['reference_num'] . ' - '.$index.' </td>
                        <td id="dt-patname">' . $pat_full_name . ' 
                            <span id="pat-address-span"> Address: '.$row['province_description'] .', '. $row['municipality_description'].' , '. $row['barangay_description'].' </span> 
                            <span id="pat-age-span"> Age: '.$row['pat_age'].' </span> 
                        </td>
                        <td id="dt-type" style="background:' . $type_color . ' ">' . $row['type'] . '</td>
                        <td id="dt-phone-no">
                            <div class="">
                                <p> Referred by: ' . $row['referred_by'] . '  </p>
                                <p> Landline: ' . $row['landline_no'] . ' </p>
                                <p> Mobile: ' . $row['mobile_no'] . ' </p>
                            </div>
                        </td>
                            <td id="dt-turnaround"> 
                                <i id="accordion-id- '.$accord_index.'" class="accordion-btn fa-solid fa-plus"></i>
    
                                <p class="referred-time-lbl"> Referred: ' . $row['date_time'] . ' </p>
                                <p class="reception-time-lbl"> Reception: '. $row['reception_time'] .' <span style="color:'.$reception_addition_style.'; font-size:0.65rem;"> +'. $reception_addition.' </span></p>
                                <p class="sdn-proc-time-lbl"> SDN Processed: <span style="'. $total_time_style.'">'. $total_time .' </span> </p>
                                
                                <div class="breakdown-div">
                                    <p> Approval: '.$row['approved_time'] .'  </p>  
                                    <p> Deferral: '.$row['deferred_time'] .'  </p>  
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
                        </tr>';
    
                
                $previous = $row['reference_num'];
                $loop += 1;
                $accord_index += 1;
            }
        }
        else{
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
                $pat_full_name = "
                    <div class='pat-full-name-div'>
                        <button class='sensitive-case-btn' style='display:none;'> <i class='sensitive-lock-icon fa-solid fa-lock'></i> Sensitive Case </button>
                        <label> " . $row['patlast'] . " , " . $row['patfirst'] . "  " . $row['patmiddle'] . "</label>
                        <input class='sensitive-hpercode' type='hidden' name='sensitive-hpercode' value= '" . $row['hpercode'] . "'>
                    </div>
                ";

                echo '<tr class="tr-incoming">
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
                                <p> Approval: '.$row['approved_time'] .'  </p>  
                                <p> Deferral: 0000-00-00 00:00:00  </p>  
                                <p> Cancelled: 0000-00-00 00:00:00  </p>  
                            </div>
                        </td>

                        <td id="dt-status">
                            <div> 
                                <p class="pat-status-incoming">' . $row['status'] . '</p>';
                                echo'<i class="pencil-btn fa-solid fa-pencil" style="color:#cc9900;"></i>';
                                
                                echo '<input class="hpercode" type="hidden" name="hpercode" value= ' . $row['hpercode'] . '>
                                <input class="date-referral" type="hidden" value="' . $row['date_time'] . '">

                            </div>
                        </td>

                        <td id="dt-cancel">
                            <button class="referral-cancel-btns" id="referral-cancel-btn"> Cancel </button>
                        </td>
                    </tr>';

            
                    $previous = $row['reference_num'];
                $loop += 1;
            }
        }

       
    }
    else{
        try{
            $sql = "SELECT * FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND refer_to=? ORDER BY date_time ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION["hospital_name"]]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $_SESSION['prev_inc_ref_total'] = count($data);
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

                    $total_time = $row['final_progressed_timer'];

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
    }


?>