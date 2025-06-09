<?php 
    session_start();
    include("../database/connection2.php");

    $ref_no = $_POST['ref_no'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $case_type = $_POST['case_type'];
    $agency = $_POST['agency'];
    $status = $_POST['status'];
    // $status = 'Pending';

    $sql = "SELECT * FROM incoming_referrals WHERE ";

    $conditions = array();
    $others = false;

    if (!empty($ref_no)) {
        $conditions[] = "reference_num LIKE '%". $ref_no ."%'";
        $others = true;
    }

    if (!empty($last_name)) {
        $conditions[] = "patlast LIKE '%". $last_name ."%' ";
        $others = true;
    }

    if (!empty($first_name)) {
        $conditions[] = "patfirst LIKE '%". $first_name ."%' ";
        $others = true;
    }

    if (!empty($middle_name)) {
        $conditions[] = "patmiddle LIKE '%". $middle_name ."%' ";
        $others = true;
    }

    if (!empty($case_type)) {
        $conditions[] = "type = '" . $case_type . "'"; 
        $others = true;
    }

    if (!empty($agency)) {
        $conditions[] = "referred_by = '" . $agency . "'";
        $others = true;
    } 

    if($status != "default" && $status!="All"){
        $conditions[] = "status = '" . $status . "'";
        $others = false;
    }

    if ($others === false) {
        if($status === 'All' || $status === 'default'){
            $conditions[] = "(status='Pending' OR status='On-Process' OR status='Deferred' OR status='Approved' OR status='Cancelled'
            OR status='Arrived' OR status='Checked' OR status='Admitted' OR status='Discharged' OR status='For Follow Up' OR status='Referred Back')";
        }
        else if($status === 'default'){
            $conditions[] = "status='Pending' OR status='On-Process'";
        }
        else{
            // $conditions[] = "(status='Pending' OR status='On-Process')";
            $conditions[] = "status = '" . $status . "'";
        }
    }

    if($others === true){
        $conditions[] = "(status='Pending' OR status='On-Process' OR status='Deferred' OR status='Approved' OR status='Cancelled'
            OR status='Arrived' OR status='Checked' OR status='Admitted' OR status='Discharged' OR status='For Follow Up' OR status='Referred Back')";
    }

    if (count($conditions) > 0) {
        $sql .= implode(" AND ", $conditions);
    } else {
        $sql .= "1";  // Always true condition if no input values provided.
    }
    
    $sql .= " AND referred_by = '" . $_SESSION["hospital_name"] . "'";
    // echo $sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // $jsonString = json_encode($data);
    // echo $jsonString;

    $index = 0;
    $previous = 0;
    $loop = 0;
    // Loop through the data and generate table rows


    foreach ($data as $row) {
        $type_color;
        if($row['type'] == 'OPD'){
            $type_color = '#d77707';
        }else if($row['type'] == 'OB'){
            $type_color = '#22c45e';
        }else if($row['type'] == 'ER'){
            $type_color = '#0368a1';
        }else if($row['type'] == 'PCR' || $row['type'] == 'Toxicology'){
            $type_color = '#cf3136';
        }

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
 
        // $waiting_time = "--:--:--";
        $date1 = new DateTime($row['date_time']);
        $waiting_time_bd = "";
        if($row['reception_time'] != null){
            $date2 = new DateTime($row['reception_time']);
            $waiting_time = $date1->diff($date2);
            $waiting_time_bd .= sprintf('%02d:%02d:%02d', $waiting_time->h, $waiting_time->i, $waiting_time->s);

        }else{
            $waiting_time_bd = "00:00:00";
        }

        if($row['reception_time'] == ""){
            $row['reception_time'] = "00:00:00";
        }

        echo '<tr class="tr-incoming" style="'. $style_tr .' border-bottom:1px solid #bfbfbf;">
                <td id="dt-refer-no"> ' . $row['reference_num'] . ' - '.$index.' </td>
                <td id="dt-patname">' . $row['patlast'] , ", " , $row['patfirst'] , " " , $row['patmiddle']  . '</td>
                <td id="dt-type" style="background:' . $type_color . ' ">' . $row['type'] . '</td>
                <td id="dt-phone-no">
                    <label> Referred: ' . $row['referred_by'] . '  </label>
                    <label> Landline: ' . $row['landline_no'] . ' </label>
                    <label> Mobile: ' . $row['mobile_no'] . ' </label>
                </td>
                <td id="dt-turnaround"> 
                    <i class="accordion-btn fa-solid fa-plus"></i>

                    <label class="referred-time-lbl"> Referred: ' . $row['date_time'] . ' </label>
                    <label class="queue-time-lbl"> Queue Time: ' . $waiting_time_bd . ' </label>
                    <label class="reception-time-lbl"> Reception: '. $row['reception_time'] .'</label>
                    
                    <div class="breakdown-div">
                        <label class="processed-time-lbl"> Processed: 00:00:00  </label>  
                        <label> Approval: 0000-00-00 00:00:00  </label>  
                        <label> Deferral: sdf  </label>  
                        <label> Cancelled: 0000-00-00 00:00:00  </label>  
                    </div>
                </td>
                <td id="dt-stopwatch">
                    <div id="stopwatch-sub-div">
                        Processing: <span class="stopwatch">00:00:00</span>
                    </div>
                </td>
                
                <td id="dt-status">
                    <div> 
                        
                        <label class="pat-status-incoming">'. $row['status'] .'</label>
                        <i class="pencil-btn fa-solid fa-pencil"></i>
                        <input class="hpercode" type="hidden" name="hpercode" value= ' . $row['hpercode'] . '>

                    </div>
                </td>
            </tr>';


        $previous = $row['reference_num'];
        $loop += 1;
    }

?>