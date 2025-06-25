<?php
    session_start();
    include('../database/connection2.php');

    $_SESSION['current_content'] = "pat_register";

    $sql = "SELECT classifications FROM classifications";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>'; print_r($data); echo '</pre>';

    if ($_SESSION['user_name'] === 'admin'){
        $user_name = 'Bataan General Hospital and Medical Center';
    }else{
        $user_name = $_SESSION['hospital_name'];
    }

    $classification_arr = array();
    for($i = 0; $i < count($data); $i++){
        array_push($classification_arr, $data[$i]['classifications']);
    }

    $sql = "SELECT COUNT(*) FROM doctors_list WHERE hospital_code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['hospital_code']]);
    $doctors_list = $stmt->fetch(PDO::FETCH_ASSOC);


    $sql = "UPDATE sdn_local.sdn_users SET permission='{\"setting\": true, \"history_log\": false, \"admin_function\": false, \"bucas_referral\": false, \"incoming_referral\": true, \"outgoing_referral\": true, \"patient_registration\": true}' WHERE role='rhu_account'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDN</title>

    <?php require "../header_link.php" ?>
    <link rel="stylesheet" href="../css/patient_register_form2.css">

</head>
<body>
    <input id="tertiary-case" type="hidden" name="tertiary-case" value="">
    <input id="hpercode-input" type="hidden" name="hpercode-input" value="">
    <input id="hpatcode-input" type="hidden" name="hpatcode-input" value=<?php echo $_SESSION["hospital_code"]?>>

    <main id="patient-reg-form-div">
        <div id="upper-part-div">
            <aside class="important-btns">
                <!-- FUNCTION BUTTONS -->
                <div class="patient-form-btns">       
                    <select id="classification-dropdown">
                        <option value="">Classification</option>
                        <?php for($i = 0; $i < count($classification_arr); $i++){ ?>
                            <!-- <option class="cursor-pointer" value=<?php echo strtolower($classification_arr[$i]) ?>><?php echo $classification_arr[$i] ?></option> -->
                            <option class="cursor-pointer" value=<?php echo $classification_arr[$i] ?>><?php echo $classification_arr[$i] ?></option>
                        <?php }?>
                    </select>

                    <button id="add-patform-btn-id">Add</button>
                    <button id="clear-patform-btn-id"">Clear</button>

                </div>
            </aside>

            
        
            <!-- <div id="privacy-reminder-div">
                <p> <span>Notice: </span>For data integrity purposes. Changing of Name and Birthday will be restricted. Please send an email to bataan.bghmc.ihomp@gmail.com for your request of patient name change. </p>
                <button>x</button>
            </div> -->

            <div class="right-side">
                <!-- <div class="already-registered-div">
                    <i id="data-found-i" class="fa-solid fa-circle-exclamation"></i>
                    <i class="fa-solid fa-circle-check"></i>
                    <button id="check-pat-registration-btn">Check if registered na sa ibang RHUs</button>

                    <button class="btn btn-primary" id="searching-btn" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Searching...
                    </button>

                    <button id="data-found-btn">Check the Data</button>
                </div> -->

                <button id="check-if-registered-btn">
                    <i  class="fa-solid fa-magnifying-glass"></i>   
                    <h3 id="check-if-registered-h3">Search Patient</h3>

                </button>
            </div>
        </div>
        
        <div id="check-if-registered-div">
            <div id="search-upper-div">
                <h4>
                    Search Patient
                    <i id="close-search-pat-btn" class="fa-solid fa-xmark"></i> 
                </h4>
                <div id="search-upper-sub-div">
                    <form action="">

                        <div id="lname-input-div">
                            <label for="search-lname"> Last Name</label>
                            <input id="search-lname" class="form-control" type="text" name="search-lname" autocomplete="off" placeholder="Last Name">
                        </div>

                        <div id="fname-input-div">
                            <label for="search-fname"> First Name</label>
                            <input id="search-fname" class="form-control" type="text" sname="search-fname" autocomplete="off" placeholder="First Name">
                        </div>

                        <div id="mname-input-div">
                            <label for="search-mname"> Middle Name</label>
                            <input id="search-mname" class="form-control" type="text" name="search-mname" autocomplete="off" placeholder="Middle Name">
                        </div>

                    </form>
                </div>

                <button id="search-patient-btn">
                    Search
                </button>

                <div id="name-bday-div">
                    <h3>Patient ID</h3>
                    <h3>Birthday</h3>
                </div>

            </div>
            <div id="search-result-div">
                <!-- <h1 id="no-pat-found">No Patient Found</h1> -->
                <!-- <div id="search-sub-div">
                    <div id="upper-part-sub-div">
                        <h1 id="pat-id-h1">Patient ID: 292719</h1>
                        <div>
                            <h1>9/22/2023</h1>
                            <span class="fa-solid fa-user"></span>
                        </div>
                    </div>
                    <div id="lower-part-sub-div">
                        <h3 id="pat-name">Nepomuceno, John Marvin Gomez</h3>
                        <h3 id="pat-stat">Status: Not yet referred</h3>
                    </div>
                    <label>Registered at: Pilar Medical Center</label>
                </div> -->

            </div>
            <div id="pagination-div"></div>

        </div>

        <div id="main-div">

        <!--  -->

            <form action="">
                <div id="patient-reg-form-div-1">
                    <!-- PERSONAL INFORMATION DIVSION -->
                    <div id="patient-form-sub-div-1">
                        <div id="form-title-div-1">
                            <h3>Personal Information</h3>
                        </div>
                        <!-- <div class="w-[98%] h-full border-2 border-[#bfbfbf] rounded-lg"> -->
                        <div id="form-body-div-1">

                            <div class="form-sub-divs-col">
                                <div class="">
                                    <label for="hperson-last-name"> Last Name </label>
                                </div>
                                <input id="hperson-last-name" class="input-txt-classes form-control" type="text" name="hperson-last-name" autocomplete="off" placeholder="Dela Cruz" required>
                            </div>

                            <div class="form-sub-divs-col">
                                <div>
                                    <label class="text-base ml-3" for="hperson-first-name"> First Name </label>
                                </div>
                                <input id="hperson-first-name" class="input-txt-classes form-control" type="text" name="hperson-first-name" autocomplete="off" placeholder="Juan" required>
                            </div>

                            <div class="form-sub-divs-row">
                                
                                <div class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-middle-name"> Middle Name </label>
                                    </div>
                                    <input id="hperson-middle-name" class="input-txt-classes form-control" type="text" name="hperson-middle-name" autocomplete="off" placeholder="Santos" required>
                                </div>
                                
                                <div class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-ext-name"> Name Ext. </label>
                                    </div>
                                    <input id="hperson-ext-name" class="input-txt-classes-non form-control" type="text" name="hperson-ext-name" autocomplete="off" placeholder="Jr.">
                                </div>
                            </div>

                            <div class="form-sub-divs-row">

                                <div class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-birthday"> Birthday </label>
                                    </div>
                                    <input id="hperson-birthday" class="input-txt-classes form-control" type="date" name="hperson-birthday" autocomplete="off" style="color:#666666" required>
                                </div>
                                
                                <div class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-age"> Age </label>
                                    </div>
                                    <input id="hperson-age" class="input-txt-classes-non form-control" tabindex="-1" disabled="disabled" type="number" name="hperson-age" autocomplete="off">
                                </div>

                            </div>

                            <div class="form-sub-divs-row">

                                <div class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-gender"> Gender </label>
                                    </div>
                                    <select name="hperson-gender" class="input-txt-classes" id="hperson-gender" autocomplete="off" required>
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                
                                <div class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-civil-status"> Civil Status </label>
                                    </div>
                                    <select name="hperson-civil-status" class="input-txt-classes" id="hperson-civil-status" autocomplete="off" required>
                                        <option value="">Select</option>
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                        <option value="Divorced">Divorced</option>
                                        <option value="Widowed">Widowed</option>
                                    </select>
                                </div>

                            </div>

                            <div class="form-sub-divs-col">
                                <div>
                                    <label for="hperson-religion"> Religion </label>
                                </div>
                                <input id="hperson-religion" class="input-txt-classes form-control" type="text" name="hperson-religion" autocomplete="off" placeholder="ex. Roman Catholic" required>
                            </div>

                            <div class="form-sub-divs-col">
                                <div>
                                    <label for="hperson-occupation"> Occupation </label>
                                </div>
                                <input id="hperson-occupation" class="input-txt-classes-non form-control" type="text" name="hperson-occupation" autocomplete="off" placeholder="ex. Doctor" required >
                            </div>

                            <div class="form-sub-divs-row">

                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-nationality"> Nationality </label>
                                    </div>
                                    <input id="hperson-nationality" class="input-txt-classes form-control" type="text" name="hperson-nationality" autocomplete="off" placeholder="ex. Filipino" required>
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-passport-no"> Passport No. </label>
                                    </div>
                                    <input id="hperson-passport-no" class="input-txt-classes-non form-control" type="text" name="hperson-passport-no" autocomplete="off" required>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- OTHERS -->
                    <div id="patient-form-sub-div-2">
                        <div id="form-title-div-2">
                            <h3>Others</h3>
                        </div>
                        <div id="form-body-div-2">
                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-hospital-no"> Hospital No. </label>
                                    </div>
                                    <span id="hperson-hospital-no" class="input-txt-classes-non form-control" name="hperson-hospital-no"  style="pointer-events: none; background:#bfbfbf"><?php echo $_SESSION['hospital_code'] ?></span>
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-phic">PHIC </label>
                                    </div>
                                    <input id="hperson-phic" class="input-txt-classes-non form-control" type="text" name="hperson-phic" autocomplete="off" placeholder="PhilHealth Number" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="patient-reg-form-div-2">
                    <div id="patient-form-sub-div-3">
                        <div id="form-title-div-3">
                            <h3>Permanent Address</h3>
                        </div>
                        <div id="form-body-div-3">

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-house-no-pa"> House No./Lot/Bldg </label>
                                    </div>
                                    <input id="hperson-house-no-pa" class="input-txt-classes-non form-control" type="text" name="hperson-house-no-pa" autocomplete="off" placeholder="Lot 1" required>
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-street-block-pa"> Street/Block </label>
                                    </div>
                                    <input id="hperson-street-block-pa" class="input-txt-classes-non form-control" type="text" name="hperson-street-block-pa" autocomplete="off" placeholder="Block 1" required>
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-region-select-pa"> Region </label>
                                    </div>
                                    <select id="hperson-region-select-pa" class="input-txt-classes" required onchange="getLocations('region', 'pa-region')" name="region" autocomplete="off" required>
                                        <option value="" class="">Select</option>
                                        <?php 
                                            $stmt = $pdo->query('SELECT region_code, region_description from region');
                                            while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
                                                echo '<option value="' , $data['region_code'] , '">' , $data['region_description'] , '</option>';
                                            }                                        
                                        ?>
                                    </select>
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label id="kingina" for="hperson-province-select-pa"> Province </label>
                                    </div>
                                    <select id="hperson-province-select-pa"  class="input-txt-classes" required onchange="getLocations('province', 'pa-province')" name="province" required>
                                        <option value="" class="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-city-select-pa"> Municipality / City </label>
                                    </div>
                                    <select id="hperson-city-select-pa" class="input-txt-classes" required onchange="getLocations('city', 'pa-city')" name="city" required>
                                        <option value="" class="">Select</option>
                                    </select>
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-brgy-select-pa"> Barangay </label>
                                    </div>
                                    <select id="hperson-brgy-select-pa" class="input-txt-classes" required name="brgy" required>
                                        <option value="" class="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-home-phone-no-pa"> Home Phone No. </label>
                                    </div>
                                    <input id="hperson-home-phone-no-pa" class="input-txt-classes-non form-control home-phone-inputs-class" type="text" name="hperson-home-phone-no-pa" autocomplete="off">
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-mobile-no-pa"> Mobile Phone No. </label>
                                    </div>
                                    <input id="hperson-mobile-no-pa" class="input-txt-classes form-control mobile-inputs-class" type="text" name="hperson-mobile-no-pa" autocomplete="off" required>
                                </div>
                            </div>

                            <div class="form-sub-divs-row" >
                                <div id="alone" class="form-sub-divs-row-left">
                                    <div>
                                        <label class="text-base ml-3" for="hperson-email-pa"> Email Address </label>
                                    </div>
                                    <input id="hperson-email-pa" class="input-txt-classes-non form-control" type="email" name="hperson-email-pa" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="patient-form-sub-div-4">
                        <div id="form-title-div-4">
                            <h3>Current Address</h3>
                            <h3 id="same-as-perma-btn">Same as permanent</h3>
                        </div>
                        <div id="form-body-div-4">
                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-house-no-ca"> House No./Lot/Bldg </label>
                                    </div>
                                    <input id="hperson-house-no-ca" class="input-txt-classes-non form-control" type="text" name="hperson-house-no-ca" autocomplete="off" placeholder="Lot 1">
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-street-block-ca"> Street/Block </label>
                                    </div>
                                    <input id="hperson-street-block-ca" class="input-txt-classes-non form-control" type="text" name="hperson-street-block-ca" autocomplete="off" placeholder="Block 1">
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-region-select-ca"> Region CA </label>
                                    </div>
                                    <select id="hperson-region-select-ca" class="input-txt-classes" required onchange="getLocations('region' , 'ca-region')" name="region" autocomplete="off">
                                        <option value="" class="">Select</option>
                                        <?php 
                                            $stmt = $pdo->query('SELECT region_code, region_description from region');
                                            while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
                                                echo '<option value="' , $data['region_code'] , '">' , $data['region_description'] , '</option>';
                                            }                                        
                                        ?>
                                    </select>
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left" >
                                    <div>
                                        <label for="hperson-province-select-ca"> Province </label>
                                    </div>
                                    <select id="hperson-province-select-ca" class="input-txt-classes" required onchange="getLocations('province' , 'ca-province')" name="province">
                                        <option value="" class="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-city-select-ca"> Municipality / City </label>
                                    </div>
                                    <select id="hperson-city-select-ca" class="input-txt-classes" required onchange="getLocations('city' , 'ca-city')" name="province">
                                        <option value="" class="">Select </option>
                                    </select>
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-brgy-select-ca"> Barangay </label>
                                    </div>
                                    <select id="hperson-brgy-select-ca" class="input-txt-classes" required name="province">
                                        <option value="" class="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-home-phone-no-ca"> Home Phone No. </label>
                                    </div>
                                    <input id="hperson-home-phone-no-ca" class="input-txt-classes-non form-control home-phone-inputs-class" type="text" name="hperson-home-phone-no-ca" autocomplete="off">
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-mobile-no-ca"> Mobile Phone No. </label>
                                    </div>
                                    <input id="hperson-mobile-no-ca" class="input-txt-classes form-control mobile-inputs-class" type="text" name="hperson-mobile-no-ca" autocomplete="off">
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="alone" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-email-ca"> Email Address </label>
                                    </div>
                                    <input id="hperson-email-ca" class="input-txt-classes-non form-control" type="email" name="hperson-email-ca" autocomplete="off">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                
                <div id="patient-reg-form-div-3">
                    <div id="patient-form-sub-div-5">
                        <div id="form-title-div-5">
                            <h3>Current Workplace Address</h3>
                        </div>
                        <div id="form-body-div-5">

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-house-no-cwa"> House No./Lot/Bldg </label>
                                    </div>
                                    <input id="hperson-house-no-cwa" class="input-txt-classes-non form-control" type="text" name="hperson-house-no-cwa" autocomplete="off">
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-street-block-cwa"> Street/Block </label>
                                    </div>
                                    <input id="hperson-street-block-cwa" class="input-txt-classes-non form-control" type="text" name="hperson-street-block-cwa" autocomplete="off">
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-region-select-cwa"> Region </label>
                                    </div>
                                    <select id="hperson-region-select-cwa" class="input-txt-classes-non" required onchange="getLocations('region' , 'cwa-region')" name="region" autocomplete="off">
                                        <option value="" class="">Select</option>
                                        <?php 
                                            $stmt = $pdo->query('SELECT region_code, region_description from region');
                                            while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
                                                echo '<option value="' , $data['region_code'] , '">' , $data['region_description'] , '</option>';
                                            }                                        
                                        ?>
                                    </select>
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-province-select-cwa"> Province </label>
                                    </div>
                                    <select id="hperson-province-select-cwa" class="input-txt-classes-non" required onchange="getLocations('province' , 'cwa-province')" name="province">
                                        <option value="" class="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-city-select-cwa"> Municipality / City </label>
                                    </div>
                                    <select id="hperson-city-select-cwa" class="input-txt-classes-non" required onchange="getLocations('city' , 'cwa-city')" name="city">
                                        <option value="" class="">Select</option>
                                    </select>
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-brgy-select-cwa"> Barangay </label>
                                    </div>
                                    <select id="hperson-brgy-select-cwa" class="input-txt-classes-non" name="brgy">
                                        <option value="" class="">Select</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-workplace-cwa"> Name of Workplace </label>
                                    </div>
                                    <input id="hperson-workplace-cwa" class="input-txt-classes-non form-control" type="text" name="hperson-workplace-cwa" autocomplete="off">
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-ll-mb-no-cwa"> Landline/Mobile No. </label>
                                    </div>
                                    <input id="hperson-ll-mb-no-cwa" class="input-txt-classes-non form-control home-phone-inputs-class" type="text" name="hperson-ll-mb-no-cwa" autocomplete="off">
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="alone" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-email-cwa"> Email Address </label>
                                    </div>
                                    <input id="hperson-email-cwa" class="input-txt-classes-non form-control" type="text" name="hperson-email-cwa" autocomplete="off">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div id="patient-form-sub-div-6">
                        <div id="form-title-div-6">
                            <h3>Address Outside the Philippines (For OFW only) </h3>
                        </div>
                        <div id="form-body-div-6">
                            <div class="form-sub-divs-row">
                                <div id="alone" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-emp-name-ofw"> Employers Name </label>
                                    </div>
                                    <input id="hperson-emp-name-ofw" class="input-txt-classes-non form-control" type="text" name="hperson-emp-name-ofw" autocomplete="off">
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-occupation-ofw"> Occupation </label>
                                    </div>
                                    <input id="hperson-occupation-ofw" class="input-txt-classes-non form-control" type="text" name="hperson-occupation-ofw" autocomplete="off">
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-right">
                                    <div>
                                        <label for="hperson-place-work-ofw"> Place of Work </label>
                                    </div>
                                    <input id="hperson-place-work-ofw" class="input-txt-classes-non form-control" type="text" name="hperson-place-work-ofw" autocomplete="off">
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-house-no-ofw"> House No./Lot/Bldg </label>
                                    </div>
                                    <input id="hperson-house-no-ofw" class="input-txt-classes-non form-control" type="text" name="hperson-house-no-ofw" autocomplete="off">
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-right">
                                    <div>
                                        <label for="hperson-street-ofw"> Street/Block </label>
                                    </div>
                                    <input id="hperson-street-ofw" class="input-txt-classes-non form-control" type="text" name="hperson-street-ofw" autocomplete="off">
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-region-select-ofw"> Region </label>
                                    </div>
                                    <input id="hperson-region-select-ofw" class="input-txt-classes-non form-control" type="text" name="hperson-region-select-ofw" autocomplete="off">
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-right">
                                    <div>
                                        <label for="hperson-province-select-ofw"> Province </label>
                                    </div>
                                    <input id="hperson-province-select-ofw" class="input-txt-classes-non form-control" type="text" name="hperson-province-select-ofw" class="w-[90%] h-[40%] border-2 border-[#bfbfbf] rounded-lg outline-none p-2" autocomplete="off">
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-city-select-ofw"> Municipality / City </label>
                                    </div>
                                    <input id="hperson-city-select-ofw" class="input-txt-classes-non form-control" type="text" name="hperson-city-select-ofw" autocomplete="off">
                                </div>
                                
                                <div id="fifty" class="form-sub-divs-row-right">
                                    <div>
                                        <label for="hperson-country-select-ofw"> Country </label>
                                    </div>
                                    <input id="hperson-country-select-ofw" class="input-txt-classes-non form-control" type="text" name="hperson-country-select-ofw" class="w-[90%] h-[40%] border-2 border-[#bfbfbf] rounded-lg outline-none p-2" autocomplete="off">
                                </div>
                            </div>

                            <div class="form-sub-divs-row">
                                <div id="fifty" class="form-sub-divs-row-left">
                                    <div>
                                        <label for="hperson-office-phone-no-ofw"> Office Phone No. </label>
                                    </div>
                                    <input id="hperson-office-phone-no-ofw" class="input-txt-classes-non form-control" type="text" name="hperson-office-phone-no-ofw" autocomplete="off">
                                </div>
                                <div id="fifty" class="form-sub-divs-row-right">
                                    <div>
                                        <label for="hperson-mobile-no-ofw"> Mobile Phone No. </label>
                                    </div>
                                    <input id="hperson-mobile-no-ofw" class="input-txt-classes-non form-control mobile-inputs-class" type="text" name="hperson-mobile-no-ofw" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
            </form>

            <?php include("../php/footer_php/footer.php") ?>
        </div>

    </main>

    <!-- Modal -->
    <div class="modal fade" id="myModal_pat_reg" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
                <button id="yes-modal-btn" type="button" data-bs-dismiss="modal">Yes</button>
            </div>
            </div>
        </div>
    </div>

    <!-- patient history modal -->
    <div class="modal fade" id="patHistoryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- <button>Print</button> -->
                    
                    <button id="close-pending-modal" data-bs-dismiss="modal">Close</button>
                </div>
                <div  class="modal-body-incoming">
                    <!-- patient information -->
                    <div class="pat-info-div">
                        <div class="num-refer-div">
                            <h6 id="referral-case-title">Referral Case: </h6>
                            <!-- <button class="ref-counter">#1. 06/05/24</button> -->
                        </div>
                        <!-- <h4 id="pat-info-title">Patient's Information</h4> -->
                        <div class="pat-info-sub-div">  

                            <h4 class="pat-info-title">Patient's Full Name</h4>
                            <div class="pat-info-sub-div-solo" id="pat-full-name-div">
                                <div class="input-div">
                                    <label class="input-title-lbl">Last Name:</label>
                                    <label class="info-inputs" id="info-input-lname" >Nepomuceno</label>
                                </div>

                                <div class="input-div">
                                    <label class="input-title-lbl">First Name:</label>
                                    <label class="info-inputs" id="info-input-fname">John Marvin</label>
                                </div>

                                <div class="input-div">
                                    <label class="input-title-lbl">Middle Name:</label>
                                    <label class="info-inputs" id="info-input-mname">Gomez</label>
                                </div>

                                <div class="input-div">
                                    <label class="input-title-lbl">Suffix Name:</label>
                                    <label class="info-inputs" id="info-input-sname">Gomez</label>
                                </div>
                            </div>

                            <h4 class="pat-info-title">Patient's Information</h4>
                            <div class="pat-info-sub-div-solo" id="pat-full-name-div">
                                <div class="input-div">
                                    <label class="input-title-lbl">Birthdate:</label>
                                    <label class="info-inputs" id="info-input-bdate">2001-04-28</label>
                                </div>                 

                                <div class="input-div">
                                    <label class="input-title-lbl">Age:</label>
                                    <label class="info-inputs" id="info-input-age">22</label>
                                </div> 

                                <div class="input-div">
                                    <label class="input-title-lbl">Sex:</label>
                                    <label class="info-inputs" id="info-input-sex">Male</label>
                                </div> 
                            </div>

                            <h4 class="pat-info-title">Patient's Address</h4>
                            <div class="pat-info-sub-div-solo" id="pat-full-name-div">
                                <div class="input-div">
                                    <label class="input-title-lbl">Barangay:</label>
                                    <label class="info-inputs" id="info-input-brgy">St. Francis 2</label>
                                </div> 

                                <div class="input-div">
                                    <label class="input-title-lbl">Municipality:</label>
                                    <label class="info-inputs" id="info-input-city">Limay</label>
                                </div> 

                                <div class="input-div">
                                    <label class="input-title-lbl">Province:</label>
                                    <label class="info-inputs" id="info-input-prov">Bataan</label>
                                </div> 

                                <div class="input-div">
                                    <label class="input-title-lbl">Region:</label>
                                    <label class="info-inputs" id="info-input-region">Region 3</label>
                                </div> 
                            </div>

                            <h4 class="pat-info-title">Contact Number:</h4>
                            <div class="pat-info-sub-div-solo" id="pat-full-name-div">
                                <div class="input-div">
                                    <label class="input-title-lbl">Email Address:</label>
                                    <label class="info-inputs" id="info-input-email">jmgnngmj@gmail.com</label>
                                </div> 

                                <div class="input-div">
                                    <label class="input-title-lbl">Mobile Phone No.: </label>
                                    <label class="info-inputs" id="info-input-mobile">09196044820</label>
                                </div> 

                                <div class="input-div">
                                    <label class="input-title-lbl">Telephone Number:</label>
                                    <label class="info-inputs" id="info-input-telephone">333-3333</label>
                                </div> 
                            </div>

                        </div>   
                    </div>

                    <!-- referral information -->
                    <div class="pat-ref-div">
                        <div class="pat-status-mini-div">
                            <h4 id="pat-ref-status">Status: <span id="pat-ref-status-span">Pending</span></h4>
                        </div>

                        <div class="pat-ref-sub-div">

                            <h4 class="pat-ref-title">Patient's Registration</h4>
                            <div class="pat-ref-sub-div-solo" id="pat-registration-div">
                                <div class="input-div">
                                    <label class="input-title-lbl">Recorded at: </label>
                                    <label class="info-inputs" id="info-input-rec_at">2023-10-16 10:10:16</label>
                                </div> 

                                <div class="input-div">
                                    <label class="input-title-lbl">Registered at: </label>
                                    <label class="info-inputs" id="info-input-reg_at">Limay Medical Center</label>
                                </div>
                            </div>

                            <h4 class="pat-ref-title" id="pat-ref-info-h4">Patient's Referral Information</h4>
                            <div class="pat-ref-sub-div-solo" id="pat-ref-info-div">
                                <div class="input-div">
                                    <label class="input-title-lbl">Referred Date:</label>
                                    <label class="info-inputs" id="info-input-ref-date">2023-10-12 02:24:22</label>
                                </div>

                                <div class="input-div">
                                    <label class="input-title-lbl">Referred By:</label>
                                    <label class="info-inputs" id="info-input-ref-by">Limay Medical Center</label>
                                </div>

                                <div class="input-div">
                                    <label class="input-title-lbl">Referred To:</label>
                                    <label class="info-inputs" id="info-input-ref-to">Bataan General Hospital and Medical Center</label>
                                </div>

                                <div class="input-div">
                                    <label class="input-title-lbl">Reason Referral:</label>
                                    <label class="info-inputs" id="info-input-reason-ref"></label>
                                </div>

                                <div class="input-div">
                                    <label class="input-title-lbl">Patient Type:</label>
                                    <label class="info-inputs" id="info-input-pat-type">ER</label>
                                </div>

                                <div class="input-div">
                                    <label class="input-title-lbl">ICD Diagnosis:</label>
                                    <label class="info-inputs" id="info-input-icd-type">ER</label>
                                </div>
                            </div>

                            <h4 class="pat-ref-title" id="pat-ref-info-h4">Patient's Approval Information</h4>
                            <div class="pat-ref-sub-div-solo" id="pat-ref-info-div">
                                <div class="input-div">
                                    <label class="input-title-lbl">Patient Classification:</label>
                                    <label class="info-inputs" id="info-input-pat-class">Tertiary</label>
                                </div>

                                <div class="input-div">
                                    <label class="input-title-lbl" id="status-time-lbl">Approved Time:</label>
                                    <label class="info-inputs" id="info-input-approve-time">2023-10-12 02:24:22</label>
                                </div>

                                <div class="input-div" id="approval-details-div">
                                    <label class="input-title-lbl">Approval Details:</label>
                                    <label class="info-inputs" id="info-input-approve-details">
                                    John Marvin Gomez Nepomuceno John Marvin Gomez Nepomuceno John Marvin Gomez Nepomuceno John Marvin Gomez Nepomuceno
                                    John Marvin Gomez Nepomuceno John Marvin Gomez Nepomuceno John Marvin Gomez Nepomuceno John Marvin Gomez Nepomuceno</label>
                                </div>
                            </div>

                            <h4 class="pat-ref-title" id="pat-ref-info-h4">Patient's Discharged Information</h4>
                            <div class="pat-ref-sub-div-solo" id="pat-ref-info-div">
                                <div class="input-div">
                                    <label class="input-title-lbl">Discharged Time:</label>
                                    <label class="info-inputs" id="info-input-pat-discharge">Tertiary</label>
                                </div>
                            </div>

                        </div>   
                    </div>
                </div>

                <div class="modal-footer">
                    
                </div>
            </div>
        </div>  
    </div>

    <div class="modal fade" id="myModal_prompt" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
                Fill in first the Referring Doctors List
            </div>
            <div class="modal-footer">
                <button id="ok-modal-btn" type="button" data-bs-dismiss="modal">OK</button>
                <button id="redirect-modal-btn" type="button" data-bs-dismiss="modal">Redirect</button>
            </div>
            </div>
        </div>
    </div>

     <!-- <div class="modal fade" id="tutorialModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered custom-modal-width" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title-div">
                            <h5 id="modal-title" class="modal-title" id="exampleModalLabel">Verification</h5>

                            <button type="button" data-bs-dismiss="modal" aria-label="Close" style="color:white">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                    <div id="modal-body" class="modal-body">
                        <div id="tutorial-carousel" class="carousel slide">
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#tutorial-carousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                <button type="button" data-bs-target="#tutorial-carousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                <button type="button" data-bs-target="#tutorial-carousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                            </div>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="./assets/tutorial_images/login_imgs/landing.jpg" class="d-block w-100" alt="image">
                                </div>
                                <div class="carousel-item">
                                    <img src="./assets/tutorial_images/login_imgs/registration.jpg" class="d-block w-100" alt="image">
                                </div>
                                <div class="carousel-item">
                                    <img src="./assets/tutorial_images/login_imgs/otp.jpg" class="d-block w-100" alt="image">
                                </div>
                                <div class="carousel-item">
                                    <img src="./assets/tutorial_images/login_imgs/authorization.jpg" class="d-block w-100" alt="image">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#tutorial-carousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#tutorial-carousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <div id="stopwatch-sub-div" style="display:none">
        Processing: <span class="stopwatch"></span>
    </div>

    <!-- document.querySelectorAll('.stopwatch')[index].textContent = formattedTime; -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script src="../js/patient_register_form2.js?v=<?php echo time(); ?>"></script>
    <script src="../js/search_name_2.js?v=<?php echo time(); ?>"></script>

    <script>
        var doctor_list = <?php echo $doctors_list['COUNT(*)']; ?>
    </script>
</body>
</html>

<!-- services.csc.gov.ph -->