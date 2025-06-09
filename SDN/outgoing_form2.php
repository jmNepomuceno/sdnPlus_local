<?php
    session_start();
    include('../database/connection2.php');

    $_SESSION['current_content'] = "outgoing_referral";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <?php require "../header_link.php" ?>
    <link rel="stylesheet" href="../css/incoming_form.css">
</head>
<body>

    <div class="incoming-container">
        <h1>Outgoing Referral Patients</h1>
        <div class="search-main-div">
            <div class="refer-no-div">
                <label>Referral No.</label>
                <input id="incoming-referral-no-search" type="textbox">
            </div>
        
            <div class="lname-search-div">
                <label>Last Name</label>
                <input id="incoming-last-name-search" type="textbox">
            </div>

            <div class="fname-search-div">
                <label>First Name</label>
                <input id="incoming-first-name-search" type="textbox">
            </div>

            <div class="mname-search-div">
                <label>Middle Name</label>
                <input id="incoming-middle-name-search" type="textbox">
            </div>

            <div class="caseType-search-div">
                <label>Case Type</label>
                <select id='incoming-type-select'>
                    <?php 
                        $stmt = $pdo->prepare('SELECT classifications FROM classifications');
                        $stmt->execute();

                        echo '<option value=""> None </option>';
                        while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
                            echo '<option value="' , $data['classifications'] , '">' , $data['classifications'] , '</option>';
                        } 
                    ?>
                </select>
            </div>


            <div class="agency-search-div">
                <label>Agency</label>
                <select id='incoming-agency-select'>
                   <?php 
                    $stmt = $pdo->prepare('SELECT hospital_name FROM sdn_hospital');
                    $stmt->execute();
            
                    echo '<option value=""> None </option>';
                    while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
                        echo '<option value="' , $data['hospital_name'] , '">' , $data['hospital_name'] , '</option>';
                    } 
                   ?>
                </select>
            </div>


            <div class="status-search-div">
                <label>Status</label>
                <select id='incoming-status-select'>
                    <option value="default">Select</option>
                    <option value="Pending">Pending</option>
                    <option value="All"> All</option>
                    <option value="On-Process"> On-Process</option>
                    <option value="Deferred"> Deferred</option>
                    <option value="Approved"> Approved</option>
                    <option value="Cancelled"> Cancelled</option>
                </select>
            </div>

            <div class="search-clear-btns-div">
                <button id='incoming-clear-search-btn'>Clear</button>
                <button id='incoming-search-btn'>Search</button>
            </div>
        </div>

        <section class="incoming-table">
            <table id="myDataTable" class="display table table-bordered custom-search-modal" style="width: 100%; border-spacing: -1px;">
                <thead>
                    <tr class="text-center">
                        <th id="refer-no">Reference No. </th>
                        <th>Patient's Name</th>
                        <th>Type</th>
                        <th>Agency</th>
                        <th>Date/Time</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="incoming-tbody">
                <?php
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
                                                if ($row['sensitive_case'] === 'true') {
                                                    echo '<i class="pencil-btn fa-solid fa-pencil" style="pointer-events:none; opacity:0.3; color:#cc9900;"></i>';
                                                }else{
                                                    echo'<i class="pencil-btn fa-solid fa-pencil" style="color:#cc9900;"></i>';
                                                }
                                                
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

                            // Close the database connection
                            $pdo = null;
                        }
                        catch(PDOException $e){
                            echo "asdf";
                        }
                    ?>
                </tbody>
            </table>

            <?php include("../php/footer_php/footer.php") ?>
        </section>
    </div>

    <!-- MODAL -->
    

    
    <div class="modal fade" id="pendingModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl pendingModalSize" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- <button>Print</button>
                    <button id="close-pending-modal" data-bs-dismiss="modal">Close</button> -->
                    PATIENT REFERRAL INFORMATION
                </div>
                
                <div class="modal-body-incoming">
                    <div class="container">
                        <div class="left-div"> 
                        </div>
                        <div class="right-div">
                        </div>  
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" id="print-modal-btn-incoming" class="btn btn-primary" >Print</button>
                    <button type="button" id="close-modal-btn-incoming" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal-incoming" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-div">
                    <i id="modal-icon" class="fa-solid fa-triangle-exclamation"></i>
                    <h5 id="modal-title-incoming" class="modal-title-incoming" id="exampleModalLabel">Warning</h5>
                    <!-- <i class="fa-solid fa-circle-check"></i> -->
                </div>
                <!-- <button type="button" class="close text-3xl" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button> -->
            </div>
            <div id="modal-body-incoming" class="modal-body-incoming ml-2">
                Please input at least one value in any field.
            </div>
            <div class="modal-footer">
                <button id="ok-modal-btn-incoming" type="button" data-bs-dismiss="modal" data-target=".sensitive-case-btn">OK</button>
                <button id="yes-modal-btn-incoming" type="button" data-bs-dismiss="modal">Yes</button>
            </div>
            </div>
        </div>
    </div>

    <div id="stopwatch-sub-div" style="display:none">
        Processing: <span class="stopwatch"></span>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script type="text/javascript"  charset="utf8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>

    <script src="../js/outgoing_form2.js?v= <?php echo time(); ?>"></script>

    <script>
    // $(document).ready(function () {
    //     $('#myDataTable').DataTable();
    // });
        var jsonData = <?php echo $jsonData; ?>;
        // var logout_data =  echo $logout_data; ?>;
        var login_data = "<?php echo $_SESSION['login_time']; ?>";

    
    </script>
</body>
</html>

<?php 

?>