<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    $hpercode = $_POST['hpercode'];
    $incoming_referrals_data = [];
    $sql = "SELECT * FROM incoming_referrals WHERE hpercode='". $hpercode ."' ORDER BY date_time DESC LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);  
    $jsonString = $data;

    $incoming_referrals_data = $data;

    $sql = "SELECT * FROM hperson WHERE hpercode='". $hpercode ."' ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);  
    // echo '<pre>'; print_r($data); echo '</pre>';
    $jsonString_2 = $data;

    $mergedObj = array_merge($jsonString, $jsonString_2);

    // FOR ADDRESS CODE CONVERTION
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    // $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>'; print_r($data); echo '</pre>';

    // if the query is slow, remove the region/province/city/brgy code and directly save the name of the regions/province/city/brgy.
    // FROM REGION CODE TO REGION DESCRIPTION QUERY
    // permanent address
    $sql_province = 'SELECT province_description FROM provinces WHERE province_code="'. $mergedObj[1]["pat_province"] .'" ';
    $stmt_province = $pdo->prepare($sql_province);
    $stmt_province->execute();
    $data_province = $stmt_province->fetchAll(PDO::FETCH_ASSOC);
    
    $sql_city = 'SELECT municipality_description FROM city WHERE municipality_code="'. $mergedObj[1]["pat_municipality"] .'" ';
    $stmt_city = $pdo->prepare($sql_city);
    $stmt_city->execute();
    $data_city = $stmt_city->fetchAll(PDO::FETCH_ASSOC);

    $sql_brgy = 'SELECT barangay_description FROM barangay WHERE barangay_code="'. $mergedObj[1]["pat_barangay"] .'" ';
    $stmt_brgy = $pdo->prepare($sql_brgy);
    $stmt_brgy->execute();
    $data_brgy = $stmt_brgy->fetchAll(PDO::FETCH_ASSOC);

    $mergedObj[1]["pat_province"] = $data_province[0]['province_description'];
    $mergedObj[1]["pat_municipality"] = $data_city[0]['municipality_description'];
    $mergedObj[1]["pat_barangay"] = $data_brgy[0]['barangay_description'];

    // current address
    $sql_province_ca = 'SELECT province_description FROM provinces WHERE province_code="'. $mergedObj[1]["pat_curr_province"] .'" ';
    $stmt_province_ca = $pdo->prepare($sql_province_ca);
    $stmt_province_ca->execute();
    $data_province_ca = $stmt_province_ca->fetchAll(PDO::FETCH_ASSOC);
    
    $sql_city_ca = 'SELECT municipality_description FROM city WHERE municipality_code="'. $mergedObj[1]["pat_curr_municipality"] .'" ';
    $stmt_city_ca = $pdo->prepare($sql_city_ca);
    $stmt_city_ca->execute();
    $data_city_ca = $stmt_city_ca->fetchAll(PDO::FETCH_ASSOC);

    $sql_brgy_ca = 'SELECT barangay_description FROM barangay WHERE barangay_code="'. $mergedObj[1]["pat_curr_barangay"] .'" ';
    $stmt_brgy_ca = $pdo->prepare($sql_brgy_ca);
    $stmt_brgy_ca->execute();
    $data_brgy_ca = $stmt_brgy_ca->fetchAll(PDO::FETCH_ASSOC);

    $mergedObj[1]["pat_curr_province"] = $data_province_ca[0]['province_description'];
    $mergedObj[1]["pat_curr_municipality"] = $data_city_ca[0]['municipality_description'];
    $mergedObj[1]["pat_curr_barangay"] = $data_brgy_ca[0]['barangay_description'];

    // current workplace address
    if($mergedObj[1]["pat_work_province"] != "N/A"){
        $sql_province_cwa = 'SELECT province_description FROM provinces WHERE province_code="'. $mergedObj[1]["pat_work_province"] .'" ';
        $stmt_province_cwa = $pdo->prepare($sql_province_cwa);
        $stmt_province_cwa->execute();
        $data_province_cwa = $stmt_province_cwa->fetchAll(PDO::FETCH_ASSOC);
        
        $sql_city_cwa = 'SELECT municipality_description FROM city WHERE municipality_code="'. $mergedObj[1]["pat_work_municipality"] .'" ';
        $stmt_city_cwa = $pdo->prepare($sql_city_cwa);
        $stmt_city_cwa->execute();
        $data_city_cwa = $stmt_city_cwa->fetchAll(PDO::FETCH_ASSOC);

        $sql_brgy_cwa = 'SELECT barangay_description FROM barangay WHERE barangay_code="'. $mergedObj[1]["pat_work_barangay"] .'" ';
        $stmt_brgy_cwa = $pdo->prepare($sql_brgy_cwa);
        $stmt_brgy_cwa->execute();
        $data_brgy_cwa = $stmt_brgy_cwa->fetchAll(PDO::FETCH_ASSOC);

        $mergedObj[1]["pat_work_province"] = $data_province_cwa[0]['province_description'];
        $mergedObj[1]["pat_work_municipality"] = $data_city_cwa[0]['municipality_description'];
        $mergedObj[1]["pat_work_barangay"] = $data_brgy_cwa[0]['barangay_description'];
    }

    $reject_det_sql = 'SELECT rejected_details FROM incoming_interdept WHERE hpercode=?';
    $reject_det_stmt = $pdo->prepare($reject_det_sql);
    $reject_det_stmt->execute([$hpercode]);
    $reject_det_data = $reject_det_stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = $mergedObj;


    // $response = json_encode($mergedObj);
    // echo $response;
    
    // print mo lang lahat ng need i print sa incoming_form.js bukas. gege
    // gl hf tomorrow! :)))))) <333333

    if($_POST['from'] == 'incoming' && $response[0]['status'] == 'Pending'){
        $sql = "UPDATE hperson SET status='On-Process' WHERE hpercode=:hpercode";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
        $stmt->execute();
    }

    $left_html = '';
    $right_html = '';
    $pat_status = "";
    $selected_response = "Select";
    $select_style = "pointer-events:auto; background: none;";

    if($response[0]['status_interdept'] != '' || $response[0]['status_interdept'] != null ){
        $pat_status = $response[0]['status'] . " - " .$response[0]['status_interdept'] . ": Interdept";
        $pat_status = "Interdept: " . $response[0]['status_interdept'];

        $selected_response = "Interdepartamental";
        $select_style = "pointer-events:none; background: #a5d6a7;";
    }else{
        $pat_status = ($response[0]['status'] === "Pending") ? "On-Process" : $response[0]['status'];
    }

    if($response[0]['status_interdept'] == null && $response[0]['final_progressed_timer'] != null){
        $selected_response = "Approve";
        $select_style = "pointer-events:none; background: #a5d6a7;";
    }   


    // Left-side content
    $left_html .= '<div class="left-sub-div"> <label>Patient ID:</label><span id="pat-id"> '. $response[1]['hpercode'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Referral Status:</label><span id="pat-id"> '.  $pat_status .'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Referring Agency:</label><span id="refer-agency"> '. $response[0]['referred_by'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Referred By:</label><span id="refer-agency"> '. $response[0]['referring_doctor'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Mobile Number:</label><span id="refer-agency"> '. $response[0]['referred_by_no'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Last Name:</label><span id="pat-last"> '. $response[1]['patlast'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>First Name:</label><span id="pat-first"> '. $response[1]['patfirst'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Middle Name:</label><span id="pat-middle"> '. $response[1]['patmiddle'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Extension Name:</label><span id="pat-exten"> '. $response[1]['patsuffix'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Gender:</label><span id="pat-gender"> '. $response[1]['patsex'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Civil Status:</label><span id="pat-cstat"> '. $response[1]['patcstat'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Religion:</label><span id="pat-rel"> '. $response[1]['relcode'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Contanct No.:</label><span id="pat-phone-no"> '. $response[1]['pat_mobile_no'].'</span> </div>';

    $left_html .= '<div class="left-sub-div"> <h4>Physical Examination</h4></div>';
    $left_html .= '<div class="left-sub-div"> <label>Blood Pressure:</label><span id="pat-bp"> '. $response[0]['bp'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Heart Rate (HR):</label><span id="pat-hr"> '. $response[0]['hr'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Respiratory Rate (RR):</label><span id="pat-rr"> '. $response[0]['rr'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Body Temperature:</label><span id="pat-temp"> '. $response[0]['temp'].'</span> </div>';
    $left_html .= '<div class="left-sub-div"> <label>Weight:</label><span id="pat-weight"> '. $response[0]['weight'].'</span> </div>';

    if($response[0]['type'] == 'OB'){
        $left_html .= '<div class="left-sub-div"> <label>Fetal Heart Tone (OB-GYNE):</label><span id="pat-fht"> '. $response[0]['fetal_heart_tone'].'</span> </div>';
        $left_html .= '<div class="left-sub-div"> <label>Fundal Height (OB-GYNE):</label><span id="pat-fh"> '. $response[0]['fundal_height'].'</span> </div>';

        $left_html .= '<div class="left-sub-div"> <h4>Internal Examination (OB-GYNE)</h4></div>';
        $left_html .= '<div class="left-sub-div"> <label>Cervical Dilation:</label><span id="pat-cervical"> '. $response[0]['cervical_dilation'].'</span> </div>';
        $left_html .= '<div class="left-sub-div"> <label>Bag of Water:</label><span id="pat-water"> '. $response[0]['bag_water'].'</span> </div>';
        $left_html .= '<div class="left-sub-div"> <label>Presentation:</label><span id="pat-present"> '. $response[0]['presentation'].'</span> </div>';
        $left_html .= '<div class="left-sub-div"> <label>Others:</label><span id="pat-others"> '. $response[0]['others_ob'].'</span> </div>';
    }

    // $left_html .= '<div class="left-sub-div"> <label>Remarks:</label><span id="pat-remarks"> '. $response[0]['remarks'].' </span> </div>';

    // $left_html .= '<div class="left-sub-div"> <label>Referred By:</label><span id="pat-refered-by"> Juan Dela Cruz</span> </div>';

    // Right-side content
    $right_html = '
        <div id="right-sub-div-a">
            <div id="right-sub-div-a-1"> 
                <div class="right-sub-div" id="case-no-main-div"> 
                    <label>Case Number:</label><span id="case-no"> ' . $response[0]['referral_id'] . '</span> 
                </div>
                <div class="right-sub-div"  id="pat-age-main-div"> 
                    <label>Age:</label><span id="pat-age"> ' . $response[1]['pat_age'] . '</span> 
                </div>
            </div>
            <div id="right-sub-div-a-2">
    ';

    // Here you add the conditional PHP logic outside of the HTML string
    if ($_POST['from'] === 'incoming') {
        $right_html .= '
            <div id="right-sub-div-b">
                <div id="right-sub-div-b-1">
                    <div class="right-sub-div"> 
                        <label>Select Response Status:</label>
                        <select class="form-control" id="select-response-status" style="' . $select_style . '">
                            <option value="">' . $selected_response . '</option>
                            <option value="Approved">Approve</option>
                            <option value="Deferred">Defer</option> 
                            <option value="Interdepartamental" disabled>Interdepartamental Referral</option>
                        </select>
                    </div>

                    <div class="right-sub-div"> 
                        <label>Process Date/Time:</label><span id="refer-agency"> ' . $response[0]['reception_time'] . '</span> 
                    </div>
                    <div class="right-sub-div"> 
                        <label>Processed By:</label><span id="processed-by-span">' . $_SESSION['last_name'] . ', ' . $_SESSION['first_name'] . '</span> 
                    </div>
                </div>
            </div>
        ';
    }

    if ($_POST['from'] === 'outgoing') {
        $right_html .= '
            <div id="right-sub-div-b-outgoing">
                <div class="right-sub-div-outgoing"> 
                    <label>Process Date/Time:</label><span id="refer-agency"> ' . $response[0]['reception_time'] . '</span> 
                </div>
                <div class="right-sub-div-outgoing"> 
                    <label>Processed By:</label><span id="processed-by-span">' . $response[0]['processed_by'] . '</span> 
                </div>
            </div>
        ';
    }

    // Close the main div
    $right_html .= '
            </div>
        </div>
    ';

    $icd_sql = 'SELECT icd10_code, icd10_title FROM icd_code_mapping WHERE icd10_code=?';
    $icd_stmt = $pdo->prepare($icd_sql);
    $icd_stmt->execute([$response[0]['icd_diagnosis']]);
    $icd_data = $icd_stmt->fetch(PDO::FETCH_ASSOC);
    $response[0]['icd_title'] = $icd_data['icd10_title'];

    $right_html .= '
    <div class="right-sub-div-2"> 
        <span> ICD-10 Diagnosis </span>
        <h1> <span id="icd-span-code"> '.$icd_data['icd10_code'] .' </span>: <span id="icd-span-title">'. $icd_data['icd10_title']  .'</span> </h1>
        </select>
    </div>';
    

    // chief_complaint_history
    $right_html .= '<div class="right-sub-div textarea-div" id="subjective-id"> <label>SUBJECTIVE:</label><textarea readonly class="form-control" id="pat-subjective"> '. $response[0]['chief_complaint_history'].' </textarea> </div>';
    $right_html .= '<div class="right-sub-div textarea-div" id="objective-id"> <label>OBJECTIVE:</label><textarea readonly class="form-control" id="pat-objective"> '. $response[0]['pertinent_findings'].' </textarea> </div>';
    $right_html .= '<div class="right-sub-div textarea-div" id="assessment-id"> <label>ASSESSMENT:</label><textarea readonly class="form-control" id="pat-assessment"> '. $response[0]['diagnosis'].' </textarea> </div>';
    $right_html .= '<div class="right-sub-div textarea-div" id="plan-id"> <label>PLAN:</label><textarea readonly class="form-control" id="pat-plan"> '. $response[0]['reason'].' </textarea> </div>';
    $right_html .= '<div class="right-sub-div textarea-div" id="remarks-id"> <label>REMARKS:</label><textarea readonly class="form-control" id="pat-remarks"> '. $response[0]['remarks'].' </textarea> </div>';
    
     $right_html .= '
    <div id="right-sub-div-c">
        <div id="approval-form">
            <label id="approval-title-div">Approval Form</label>
                
            <div class="approval-main-content"> 
                <label id="case-cate-title">Case Category</label>
                <select id="approve-classification-select">
                    <option value="">Select</option>
                    <option value="Primary">Primary</option>
                    <option value="Secondary">Secondary</option> 
                    <option value="Tertiary">Tertiary</option>
                </select>

                <label id="admin-action-title">Emergency Room Administrator Action</label>
                <textarea id="eraa"></textarea>

                <div id="pre-text">
                    <label class="pre-emp-text">+ May transfer patient once stable.</label>
                    <label class="pre-emp-text">+ Please attach imaging and laboratory results to the referral letter.</label>
                    <label class="pre-emp-text">+ Hook to oxygen support and maintain saturation at >95%.</label>
                    <label class="pre-emp-text">+ Start venoclysis with appropriate intravenous fluids.</label>
                    <label class="pre-emp-text">+ Insert nasogastric tube(NGT).</label>
                    <label class="pre-emp-text">+ Insert indwelling foley catheter(IFC).</label>
                    <label class="pre-emp-text">+ Thank you for your referral.</label>
                </div>

            </div> 

            <div id="approval-form-btns">
                <button id="inter-dept-referral-btn"> Interdepartamental Referral </button>
                <button id="imme-approval-btn"> Approval </button>
                <button id="imme-defer-btn"> Defer </button>
            </div>
        </div>

        <div class="interdept-div">
            <div id="inter-dept-stat-form-div" class="status-form-div">
                <label id="status-bg-div">Inter-Department Referral </label>
            </div>
            <label for="" id="inter-dept-lbl">Department: </label>
            <select id="inter-depts-select" style="cursor:pointer;">
                <option value="">Select</option>
                <option value="SURGERY"> Surgery </option>
                <option value="OB-GYNE"> OB-GYNE </option>s
                <option value="IM"> Internal Medicine </option>
                <option value="FAMILY MEDICINE"> Family Medicine </option>
                <option value="ANESTHESIA"> Anesthesia </option>
                <option value="OTOLARYNGOLOGY"> Otolaryngology </option>
                <option value="PEDIATRICS"> Pediatrics </option>
                <option value="OPHTHALMOLOGY"> Ophthalmology </option>
                <option value="PHYSICAL REHAB"> Physical Rehab </option>
                <option value="IHOMP"> IHOMP </option>
            </select>
            <div class="int-dept-btn-div">
                <button id="int-dept-btn-forward">Send / Forward</button>
            </div>
        </div>
    </div>
    ';

    if($response[0]['approval_details'] != "" || $response[0]['approval_details'] != NULL){
        $right_html .= '
            <div id="right-sub-div-d">
                <h5 id="approval-details-div">Approval Details</h5>
                <div class="appr-det-sub-container">
                    <div class="appr-det-sub-div"> <label>Case Category:</label><span> '. $response[0]['pat_class'].'</span> </div>
                    <div class="appr-det-sub-div"> 
                        <label>Update Status:</label>
                        <select id="update-stat-select" autocomplete="off" required>
                            <option value="" disabled selected hidden>Status</option>
                            <option class="custom-select" value="Cancelled"> Cancelled</option>
                            <option class="custom-select" value="Arrived"> Arrived</option>
                            <option class="custom-select" value="Checked"> Checked</option>
                            <option class="custom-select" value="Admitted"> Admitted</option>
                            <option class="custom-select" value="Discharged"> Discharged</option>
                            <option class="custom-select" value="For follow"> For follow up</option>
                            <option class="custom-select" value="Referred"> Referred Back</option>
                        </select>
                        <i id="update-stat-check-btn" class="fa-solid fa-square-check"></i>
                    </div>
                    <div class="appr-det-sub-div"> <label>Emergency Room Administrator Action:</label><span> '. $response[0]['approval_details'].'</span> </div>
                </div>
            </div>
        ';
    }else{
        $right_html .= '
            <div id="right-sub-div-d">
                <h5 id="approval-details-div">Deferral Details</h5>
                <div class="appr-det-sub-container">
                    <div class="appr-det-sub-div"> <label>Case Category:</label><span> '. $response[0]['pat_class'].'</span> </div>
                    <div class="appr-det-sub-div"> 
                        <label>Update Status:</label>
                        <select id="update-stat-select" autocomplete="off" required>
                            <option value="" disabled selected hidden>Status</option>
                            <option class="custom-select" value="Cancelled"> Cancelled</option>
                            <option class="custom-select" value="Arrived"> Arrived</option>
                            <option class="custom-select" value="Checked"> Checked</option>
                            <option class="custom-select" value="Admitted"> Admitted</option>
                            <option class="custom-select" value="Discharged"> Discharged</option>
                            <option class="custom-select" value="For follow"> For follow up</option>
                            <option class="custom-select" value="Referred"> Referred Back</option>
                        </select>
                        <i id="update-stat-check-btn" class="fa-solid fa-square-check"></i>
                    </div>
                    <div class="appr-det-sub-div"> <label>Emergency Room Administrator Action:</label><span> '. $response[0]['deferred_details'].'</span> </div>
                </div>
            </div>
        ';
    }

    

    // $right_html .= '
    // <div id="right-sub-div-e">
    //     <div class="interdept-div-v2">
    //         <div id="inter-dept-stat-form-div" class="status-form-div">
    //             <label id="status-bg-div">Interdepartment: Status </label>
    //         </div>
    //         <label id="v2-stat">
    //             <span id="span-dept">Surgery</span>
    //             <span id="span-status">Pending</span>
    //             <span id="span-time">00:00:00</span>
    //         </label>
    //         <label id="v2-update-stat">Updated 0 second(s) ago...</label>
    //         <div class="seen-div">
    //             <label id="seen-by-lbl">Seen by: <span>John Marvin Nepomuceno</span></label>
    //             <label id="seen-date-lbl">Seen date: <span>04/08/24 11:11:11</span></label>
    //         </div>';

    //         // Check if reject details exist and add the content conditionally
    //         if ($reject_det_data != null) {
    //             $right_html .= '
    //                     <div id="rejection-details-text">
    //                         <p>' . $reject_det_data['rejected_details'] . '</p>
    //                     </div>';
    //         }

    //         $right_html .= '
    //         <div class="int-dept-btn-div-v2">
    //             <button id="final-approve-btn">Proceed to Approval</button>
    //             <button id="refer-again-btn" style="display:none">Refer Again</button>
    //             <button id="reject-details-btn" style="display:none">Rejection Details</button>
    //         </div>
    //     </div>
    // </div>';

    if ($_POST['from'] === 'outgoing') {
        $right_html .= '<div id="right-sub-div-d-outgoing">';
    
        if ($response[0]['status'] == "Approved") {
            $right_html .= '
                <h5 id="approval-details-div">Approval Details</h5>
                <div class="appr-det-sub-container">
                    <div class="appr-det-sub-form-outgoing"> 
                        <div class="appr-det-sub-div"><label>Case Category:</label><span>' . $response[0]['pat_class'] . '</span></div>
                        <div class="appr-det-sub-div"><label>Status:</label><span>' . $response[0]['status'] . '</span></div>
                    </div>
                    <div class="appr-det-sub-div-details"><label>Emergency Room Administrator Action:</label><span>' . $response[0]['approval_details'] . '</span></div>
                </div>
            ';
        } else {
            $right_html .= '
                <h5 id="approval-details-div">Deferral Details</h5>
                <div class="appr-det-sub-form-outgoing"> 
                        <div class="appr-det-sub-div"><label>Case Category:</label><span>Deferred Category</span></div>
                        <div class="appr-det-sub-div"><label>Status:</label><span>' . $response[0]['status'] . '</span></div>
                </div>
                <div class="appr-det-sub-div-details"><label>Emergency Room Administrator Action:</label><span>' . $response[0]['deferred_details'] . '</spa
            ';
        }
    
        $right_html .= '</div>';
    }
    


    echo json_encode(['left_html' => $left_html, 'right_html' => $right_html , 'responseData' => $response]);

    // update the date of the reception time or, when did the user click the pencil or open the referral form
    if(($incoming_referrals_data[0]['reception_time'] == null || $incoming_referrals_data[0]['reception_time'] == "") && $_POST['from'] === 'incoming'){
        $reception_time = date('Y-m-d H:i:s');
        $sql = "UPDATE incoming_referrals SET reception_time=:reception_time WHERE hpercode=:hpercode ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':reception_time', $reception_time, PDO::PARAM_STR);
        $stmt->bindParam(':hpercode', $hpercode, PDO::PARAM_STR);
        $stmt->execute();
    }
?>