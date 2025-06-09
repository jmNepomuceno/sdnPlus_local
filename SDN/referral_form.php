<?php 
    session_start();
    include('../database/connection2.php');

    $type = $_GET['type'];
    $type = str_replace('"', '', $type);

    $code = $_GET['code'];
    if (isset($_POST['newValue'])) {
        // Retrieve the new value from the AJAX request
        $newValue = $_POST['newValue'];
    
        // Set the new value in the session
        $_SESSION['prompt'] = $newValue;
    
        // You can send a response back to the client if needed
        echo 'Value saved successfully.';
    } else {
        // Handle errors
        // echo 'Error saving value.';
    }

    $sql = "SELECT hospital_name FROM sdn_hospital WHERE hospital_name != 'bgh' ORDER BY hospital_name ASC;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>'; print_r($data); echo '</pre>';

    // echo $data[0]['hospital_name']
    $hospital_names = $data;

    $date_today =  date("Y-m-d H:i:s");
    
    //get age
    $sql = "SELECT pat_age FROM hperson WHERE hpercode=?;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$code]);
    $pat_age_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT last_name, first_name, middle_name, mobile_number FROM doctors_list WHERE hospital_code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['hospital_code']]);
    $doctors_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/referral_form.css">
    <link rel="stylesheet" href="../css/main_style_mediaq.css">
    <title>Document</title>
</head>
<body>
    <div class="container">
        
        <input id="type-input" type="hidden" name="type-input" value=<?php echo $type; ?>>
        <input id="code-input" type="hidden" name="code-input" value=<?php echo $code; ?>>
        <input id="prompt" type="hidden" name="prompt" value=''>
        <input id="hospital_code" type="hidden" name="hospital_code" value=<?php echo $_SESSION['hospital_code']; ?>>

        <div class="referral-title" style="width:100%">
            <label>
                <?php echo $type; ?> Referral Form
            </label>

            <div class="refer-form-btns-div">
                <button id="submit-referral-btn-id" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded mr-2" data-bs-toggle="modal" data-bs-target="#myModal-referral">Submit</button>
                <button id="cancel-referral-btn-id" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded h-[40px]" data-bs-toggle="modal" data-bs-target="#myModal-referral">Cancel</button>
            </div>
        </div>

        <!-- hd admitting surgery -->

        <div class="referral-body">
            <div class="first-part">
                <div class="first-sub-div-part">
                    <div class="refer-to-div">
                        <label>Refer to <span>*</span></label>    
                        <select class="form-control"  id="refer-to-select">
                            <option value="Bataan General Hospital and Medical Center">Bataan General Hospital and Medical Center</option> -->
                            <?php for($i = 0; $i < count($hospital_names); $i++) { ?>
                                <?php echo "<option value='" . $hospital_names[$i]['hospital_name'] . "'" . ">" . $hospital_names[$i]['hospital_name'] . "</option>" ?>
                            <?php } ?>
                        </select>
                    </div>  
                            <!-- bg-[#1f292e] -->
                    <div class="sensitive-case-div">
                        <div class="sensitive-div">
                            <h1>Sensitive Case <span>*</span> </h1>
                            <button id="guide-btn"> ? </button>
                        </div>
                        <div class="sensitive-rbs">
                            <input type="radio" name="sensitive_case" class="" value="true"> 
                            <label>Yes</label>
                            <input type="radio" name= "sensitive_case" value="false">
                            <label>No</label>
                        </div>
                    </div>
                </div>

                
                <div class="icd-main-div"> 
                    <span> ICD-10 Diagnosis </span>
                    <div class="icd-input-main-div">
                        <div> 
                            <label> <i class="fa-solid fa-magnifying-glass"></i> </label>
                            <input type="text" class="icd-inputs icd-10" id="icd-10-code-inp" name="icd10_code" placeholder="ICD-10 Code" autocomplete="off"/>
                        </div>

                        <div> 
                            <label> <i class="fa-solid fa-magnifying-glass"></i> </label>
                            <input type="text" class="icd-inputs icd-10" id="icd-10-title-inp" name="icd10_title" placeholder="ICD-10 Title" autocomplete="off"/>
                        </div>
                        
                        <input type="text" class="icd-inputs icd-11" id="icd-11-code-inp" name="icd11_code" placeholder="ICD-11 Code" autocomplete="off" style="display:none;"/>
                        <input type="text" class="icd-inputs icd-11" id="icd-11-title-inp" name="icd11_title" placeholder="ICD-11 Title" autocomplete="off" style="display:none;"/>
                    </div>
                    <select id="icd-select" size="7">
                    </select>
                </div>
            </div>   

            <div class="second-part">
                <div class="second-part-divs">
                    <label>Parent/Guardian(If minor) <?php if($pat_age_data['pat_age'] < 18){ echo '<span>*</span>'; };?> </label>
                    <input class="form-control" id="parent-guard-input" type="text" autocomplete="off">
                </div>
                
                <div class="second-part-divs">
                    <label>PHIC Member? <span>*</span></label>
                    <select class="form-control" id="phic-member-select">
                        <option value="">Select</option>
                        <option value="true"> Yes</option>
                        <option value="false"> No </option>
                    </select>
                </div>

                <div class="second-part-divs">
                    <label>Mode of Transport <span>*</span></label>
                    <select class="form-control" id="transport-select">
                        <option value="">Select</option>
                        <option value="Ambulance"> Ambulance </option>
                        <option value="Private Car"> Private Car </option>
                        <option value="Commute"> Commute </option>
                    </select>
                </div>

                <div class="second-part-divs">
                    <label>Date/Time Admitted <span></span></label>
                    <input class="form-control"  id="date-input" type="text" value=<?php echo $date_today ?> >
                </div>   

                
            </div>

            <div class="third-part">

                <div class="left-side">
                    
                    <div class="left-sub-div-1">
                        <label>Referring Doctor <span>*</span></label>
                        <select class="form-control" id="referring-doctor-select">
                            <option value="Disabled Selected">Select</option>
                            <?php for($i = 0; $i < count($doctors_list); $i++){ ?>
                                <option id="<?php echo $doctors_list[$i]['mobile_number'] ?>" value="<?php echo $doctors_list[$i]['last_name'] . ', ' . $doctors_list[$i]['first_name'] . ' ' . $doctors_list[$i]['middle_name'] ?>">
                                    <?php echo $doctors_list[$i]['last_name'] . ', ' . $doctors_list[$i]['first_name'] . ' ' . $doctors_list[$i]['middle_name'] ?>
                                </option>
                            <?php }?>
                        </select>

                        <!-- <input class="form-csontrol" id="referring-doc-input" type="textbox"> -->
                    </div>

                    <div class="left-sub-div-2">
                        <label>Chief Complaint and History (Subjective) <span>*</span></label>
                        <textarea class="form-control" id="complaint-history-input" autocomplete="off"></textarea>

                        <label>Reason for Referral (Plan) <span>*</span></label>
                        <textarea class="form-control" id="reason-referral-input" autocomplete="off"></textarea>

                        <label>Diagnosis (Assessment) <span>*</span></label>
                        <textarea class="form-control" id="diagnosis" autocomplete="off"></textarea>

                        <label>Remarks <span>*</span></label>
                        <textarea class="form-control" id="remarks" autocomplete="off"></textarea>
                    </div>


                    <!-- only for OB -->

                    <?php 
                        if($type === "OB"){
                            echo '<div class="ob-part">
                                    <div class="ob-first">
                                        <label>Fetal Heart Tone<span>*</span></label>
                                        <input class="form-control" type="text" id="fetal-heart-inp" autocomplete="off"/>
                                    </div>

                                    <div class="ob-second">
                                        <label>Fundal Height<span>*</span></label>
                                        <input class="form-control" type="text" id="fundal-height-inp" autocomplete="off"/>
                                    </div>

                                    <div class="ob-third">
                                        <label>Cervical Dilation<span>*</span></label>
                                        <input class="form-control" type="text" id="cervical-dilation-inp" autocomplete="off"/>
                                    </div> 

                                    <div class="ob-fourth">
                                        <label>Bag of Water<span>*</span></label>
                                        <input class="form-control" type="text" id="bag-water-inp" autocomplete="off"/>
                                    </div>
                                </div>';
                        }
                    ?>
                    
                </div>

                <div class="right-side">
                    <label id="phy-exam-lbl">Physical Examination</label>
                    <div class="right-side-main-div">
                        <div class="right-side-main-div-1">
                            <div class="right-side-main-div-1-sub-1" style="width:20%">
                                <div>
                                    <h1>BP <span>*</span></h1>
                                    <button>i</button>
                                </div>
                                <input class="form-control" id='bp-input' type="text" autocomplete="off">                      
                            </div>

                            <div class="right-side-main-div-1-sub-2" style="width:20%; margin-left:1%">
                                <div>
                                    <h1>HR <span>*</span></h1>
                                    <button>i</button>
                                </div>
                                <input class="form-control" id='hr-input' type="text" autocomplete="off">     
                            </div>

                            <div class="right-side-main-div-1-sub-3" style="width:20%; margin-left:1%">
                                <div>
                                    <h1>RR <span>*</span></h1>
                                    <button>i</button>
                                </div>
                                <input class="form-control" id='rr-input' type="text" autocomplete="off">     
                            </div>

                            <div class="right-side-main-div-1-sub-4" style="width:20%; margin-left:1%">
                                <div>
                                    <h1>Temp (°C) <span>*</span></h1>
                                    <button>i</button>
                                </div>
                                <input class="form-control" id='temp-input' type="text" autocomplete="off"> 
                            </div>

                            <div class="right-side-main-div-1-sub-5" style="width:20%; margin-left:1%">
                                <div>
                                    <h1>WT. (kg) <span>*</span></h1>
                                    <button>i</button>
                                </div>
                                <input class="form-control" id='weight-input' type="text" autocomplete="off"> 
                            </div>
                        </div>

                        <!-- <div class="flex flex-col w-[20%] h-[12%] mt-[10px] justify-center items-left ">
                            <div class="ml-1 flex flex-row justify-start items-center font-bold mt-3">
                                <h1>WT.(kg) <span class="text-red-600 font-bold text-xl">*</span></h1>
                                <button class="ml-1 w-4 h-4 rounded-full bg-blue-500 hover:bg-blue-700 focus:outline-none cursor-pointer text-black flex flex-row justify-center items-center">
                                    <h4 class="text-xs text-white">i</h4>
                                </button>
                            </div>
                            <input id='weight-input' type="text" class="border-2  border-[#bfbfbf] w-[98%] outline-none" autocomplete="off"> 
                        </div>  -->

                        <div class="right-side-main-div-2">
                            <label>Pertinent PE Findings (Objective) <span>*</span>       </label>
                            <textarea class="form-control" id="pe-findings-input" autocomplete="off"></textarea>
                        </div>

                        <!-- only for OB -->
                        <?php 
                            if($type === "OB"){
                                echo '
                                <div class="right-side-main-div-3">
                                    <div>
                                        <label>Presentation<span>*</span></label>
                                        <input class="form-control" type="text" id="presentation-ob-inp" autocomplete="off"/>
                                    </div>
        
                                    <div>
                                        <label>Others<span>*</span></label>
                                        <textarea class="form-control" id="others-ob-inp" autocomplete="off"></textarea>
                                    </div>
                                </div>
                                ';
                            }
                        ?>
                    </div>

                </div>

                
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal-referral" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-div">
                        <i id="modal-icon" class="fa-solid fa-triangle-exclamation"></i>
                        <h5 id="modal-title" class="modal-title" id="exampleModalLabel">Warning</h5>
                        <!-- <i class="fa-solid fa-circle-check"></i> -->
                    </div>
                </div>
                <div id="modal-body" class="modal-body">
                    Please fill out the required fields.
                </div>
                <div class="modal-footer">
                    <button id="ok-modal-btn" type="button" data-bs-dismiss="modal">OK</button>
                    <button id="yes-modal-btn" type="button" data-bs-dismiss="modal" style="display:none;">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="guide-referral" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-div d-flex align-items-center gap-2">
                        <i id="modal-icon" class="fa-solid fa-triangle-exclamation text-warning"></i>
                        <h5 id="modal-title" class="modal-title mb-0">Guidelines for Marking Sensitive Referrals</h5>
                    </div>
                </div>
                <div id="modal-body" class="modal-body">
                    <p class="mb-3">Please mark the referral as <strong>Sensitive</strong> if it falls under any of the following categories:</p>
                    <ul>
                        <li><strong>Psychiatric or Mental Health Cases</strong><br>
                            – Patients diagnosed or suspected with mental health disorders.<br>
                            – Cases involving self-harm or suicide attempts.
                        </li>
                        <li><strong>Sexual and Reproductive Health</strong><br>
                            – Victims of sexual abuse, assault, or rape.<br>
                            – Patients with sexually transmitted infections (STIs).<br>
                            – Minors involved in pregnancy or reproductive health issues.
                        </li>
                        <li><strong>Violence and Abuse</strong><br>
                            – Victims of domestic violence, child abuse, or elder abuse.<br>
                            – Any case under investigation by social welfare or law enforcement.
                        </li>
                        <li><strong>HIV/AIDS</strong><br>
                            – Any referral involving known or suspected HIV-positive status.
                        </li>
                        <li><strong>Substance Abuse</strong><br>
                            – Patients undergoing treatment or needing evaluation for drug/alcohol dependence.
                        </li>
                        <li><strong>High-profile or Media-sensitive Cases</strong><br>
                            – Patients involved in legal cases, political figures, or those with media attention.
                        </li>
                    </ul>

                </div>
                <div class="modal-footer">
                    <button id="ok-modal-guide-btn" type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                    <button id="yes-modal-guide-btn" type="button" class="btn btn-success" data-bs-dismiss="modal" style="display:none;">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <div id="stopwatch-sub-div" style="display:none">
        Processing: <span class="stopwatch"></span>
    </div>
    
    <script>
        var pat_age_data = <?php echo $pat_age_data['pat_age']; ?>;
        var case_type = "<?php echo $type; ?>";
    </script>
    <script src="../js/referral_form.js?v=<?php echo time(); ?>"></script>

</body>
</html>