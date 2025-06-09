<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');
    //PERSONAL INFORMATIONS // 16

    // generation for hpercode or referral patient code
    // get the last value of hpercode from the database
    $sql = "SELECT hpercode FROM hperson ORDER BY hpercode DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if($data == "" || $data == null ){
        $hpercode = "PAT000001";
    }else{
        $last_number = substr($data['hpercode'], 3);

        $hpercodePrefix = "PAT"; // Set the prefix
        $new_number = $last_number + 1; // Increment the last number

        $zeros = "0";

        if($new_number <= 9){
            $zeros = "00000";
        }else if($new_number <= 99){
            $zeros = "0000";
        }else if($new_number <= 999){
            $zeros = "000";
        }else if($new_number <= 9999){
            $zeros = "00";
        }else if($new_number <= 99999){
            $zeros = "0";
        }else if($new_number <= 999999){
            $zeros = "";
        }

        $hpercode = $hpercodePrefix . $zeros . $new_number;
    }

    $hpatcode = $_POST['hpatcode']; //2
    $patlast = $_POST['patlast']; //3
    $patfirst = $_POST['patfirst']; //4
    $patmiddle = $_POST['patmiddle']; //5
    $patsuffix = $_POST['patsuffix']; //6
    $pat_bdate = $_POST['pat_bdate']; //7
    $pat_age = $_POST['pat_age']; 
    $patsex = $_POST['patsex']; //8
    $patcstat = $_POST['patcstat']; //9
    $relcode = $_POST['relcode']; //10
    $pat_occupation = $_POST['pat_occupation']; //11
    $natcode = $_POST['natcode']; //12
    $pat_passport_no = $_POST['pat_passport_no']; //13
    $hospital_code = $_POST['hospital_code']; //14
    $phicnum = $_POST['phicnum']; //15

    // PERMANENT ADDRESS // 9
    $pat_bldg_pa = $_POST['pat_bldg_pa'];
    $hperson_street_block_pa = $_POST['hperson_street_block_pa'];
    $pat_region_pa = $_POST['pat_region_pa'];
    $pat_province_pa = $_POST['pat_province_pa'];
    $pat_municipality_pa = $_POST['pat_municipality_pa'];
    $pat_barangay_pa = $_POST['pat_barangay_pa'];
    $pat_email_pa = $_POST['pat_email_pa'];
    $pat_homephone_no_pa = $_POST['pat_homephone_no_pa'];
    $pat_mobile_no_pa = $_POST['pat_mobile_no_pa'];

    // CURRENT ADDRESS // 9
    $pat_bldg_ca = $_POST['pat_bldg_ca'];
    $hperson_street_block_ca = $_POST['hperson_street_block_ca'];
    $pat_region_ca = $_POST['pat_region_ca'];
    $pat_province_ca = $_POST['pat_province_ca'];
    $pat_municipality_ca = $_POST['pat_municipality_ca'];
    $pat_barangay_ca = $_POST['pat_barangay_ca'];
    $pat_email_ca = $_POST['pat_email_ca'];
    $pat_homephone_no_ca = $_POST['pat_homephone_no_ca'];
    $pat_mobile_no_ca = $_POST['pat_mobile_no_ca'];

    // // CURRENT WORKPLACE ADDRESS // 9
    $pat_bldg_cwa = $_POST['pat_bldg_cwa'];
    $hperson_street_block_pa_cwa = $_POST['hperson_street_block_pa_cwa'];
    $pat_region_cwa = $_POST['pat_region_cwa'];
    $pat_province_cwa = $_POST['pat_province_cwa'];
    $pat_municipality_cwa = $_POST['pat_municipality_cwa'];
    $pat_barangay_cwa = $_POST['pat_barangay_cwa'];
    $pat_namework_place = $_POST['pat_namework_place'];
    $pat_landline_no = $_POST['pat_landline_no'];
    $pat_email_ca = $_POST['pat_email_ca'];

    // OFW // 10
    $pat_emp_name = $_POST['pat_emp_name'];
    $pat_occupation_ofw = $_POST['pat_occupation_ofw'];
    $pat_place_work = $_POST['pat_place_work'];
    $pat_bldg_ofw = $_POST['pat_bldg_ofw'];
    $hperson_street_block_ofw = $_POST['hperson_street_block_ofw'];
    $pat_region_ofw = $_POST['pat_region_ofw'];
    $pat_province_ofw = $_POST['pat_province_ofw'];
    $pat_city_ofw = $_POST['pat_city_ofw'];
    $pat_country_ofw = $_POST['pat_country_ofw'];
    $pat_office_mobile_no_ofw = $_POST['pat_office_mobile_no_ofw'];
    $pat_mobile_no_ofw = $_POST['pat_mobile_no_ofw'];

    $created_at =  $_POST['created_at'];


    
    ////////////////////////////////////////////////
    //  , ofw_occupation, ofw_place_of_work, ofw_bldg, ofw_street, ofw_region, ofw_province, ofw_municipality, ofw_office_phone_no, ofw_mobile_phone_no
    // $sql = "INSERT INTO hperson (hpercode, hpatcode, ofw_employers_name, ofw_occupation, ofw_place_of_work, ofw_bldg, ofw_street, ofw_region, ofw_province, ofw_municipality, ofw_office_phone_no, ofw_mobile_phone_no)
    //         VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    
    // $stmt = $pdo->prepare($sql);

    // $stmt->bindParam(1, $hpercode, PDO::PARAM_STR);
    // $stmt->bindParam(2, $hpatcode, PDO::PARAM_STR);

    // $stmt->bindParam(3, $pat_emp_name, PDO::PARAM_STR);
    // $stmt->bindParam(4, $pat_occupation_ofw, PDO::PARAM_STR);
    // $stmt->bindParam(5, $pat_place_work, PDO::PARAM_STR);
    // $stmt->bindParam(6, $pat_bldg_ofw, PDO::PARAM_STR);
    // $stmt->bindParam(7, $hperson_street_block_ofw, PDO::PARAM_STR);
    // $stmt->bindParam(8, $pat_region_ofw, PDO::PARAM_STR);
    // $stmt->bindParam(9, $pat_province_ofw, PDO::PARAM_STR);
    // $stmt->bindParam(10, $pat_city_ofw, PDO::PARAM_STR);
    // $stmt->bindParam(11, $pat_office_mobile_no_ofw, PDO::PARAM_INT);
    // $stmt->bindParam(12, $pat_mobile_no_ofw, PDO::PARAM_INT);

    // $stmt->execute();

    // check first for duplicate patient information
    $sql = "SELECT patlast, patfirst, patmiddle, patbdate FROM hperson WHERE patlast = ? AND patfirst = ? AND patmiddle = ? AND patbdate = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$patlast, $patfirst, $patmiddle, $pat_bdate]);
    $duplicate = $stmt->fetch(PDO::FETCH_ASSOC);

    if($duplicate == null){
        $sql = "INSERT INTO hperson (hpercode, hpatcode, patlast, patfirst, patmiddle, patsuffix, patbdate, pat_age, patsex, patcstat, natcode, pat_occupation, pat_passport_no, relcode, hospital_code, phicnum,
                pat_bldg, pat_street_block, pat_region, pat_province, pat_municipality, pat_barangay, pat_email, pat_homephone_no, pat_mobile_no,
                pat_curr_bldg , pat_curr_street, pat_curr_region, pat_curr_province , pat_curr_municipality, pat_curr_barangay, pat_email_ca, pat_curr_homephone_no, pat_curr_mobile_no, 
                pat_work_bldg, pat_work_street, pat_work_region, pat_work_province, pat_work_municipality, pat_work_barangay,pat_namework_place, pat_work_landline_no, pat_work_email_add, 
                ofw_employers_name, ofw_occupation, ofw_place_of_work, ofw_bldg, ofw_street, ofw_region, ofw_province, ofw_municipality, ofw_country , ofw_office_phone_no, ofw_mobile_phone_no,
                created_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,  ?,?,?,?,?,?,?,?,?,  ?,?,?,?,?,?,?,?,?,  ?,?,?,?,?,?,?,?,?, ?,?,?,?,?,?,?,?,?,?,?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(1, $hpercode, PDO::PARAM_STR);
        $stmt->bindParam(2, $hpatcode, PDO::PARAM_STR);
        $stmt->bindParam(3, $patlast, PDO::PARAM_STR);
        $stmt->bindParam(4, $patfirst, PDO::PARAM_STR);
        $stmt->bindParam(5, $patmiddle, PDO::PARAM_STR);
        $stmt->bindParam(6, $patsuffix, PDO::PARAM_STR);
        $stmt->bindParam(7, $pat_bdate, PDO::PARAM_STR);
        $stmt->bindParam(8, $pat_age, PDO::PARAM_STR);
        $stmt->bindParam(9, $patsex, PDO::PARAM_STR);
        $stmt->bindParam(10, $patcstat, PDO::PARAM_STR);

        $stmt->bindParam(11, $natcode, PDO::PARAM_STR);
        $stmt->bindParam(12, $pat_occupation, PDO::PARAM_STR);
        $stmt->bindParam(13, $pat_passport_no, PDO::PARAM_STR);
        $stmt->bindParam(14, $relcode, PDO::PARAM_STR);
        $stmt->bindParam(15, $hospital_code, PDO::PARAM_STR);
        $stmt->bindParam(16, $phicnum, PDO::PARAM_STR);

        $stmt->bindParam(17, $pat_bldg_pa, PDO::PARAM_STR);
        $stmt->bindParam(18, $hperson_street_block_pa, PDO::PARAM_STR);
        $stmt->bindParam(19, $pat_region_pa, PDO::PARAM_STR);
        $stmt->bindParam(20, $pat_province_pa, PDO::PARAM_STR);
        $stmt->bindParam(21, $pat_municipality_pa, PDO::PARAM_STR);
        $stmt->bindParam(22, $pat_barangay_pa, PDO::PARAM_STR);
        $stmt->bindParam(23, $pat_email_pa, PDO::PARAM_STR);
        $stmt->bindParam(24, $pat_homephone_no_pa, PDO::PARAM_INT);
        $stmt->bindParam(25, $pat_mobile_no_pa, PDO::PARAM_INT);

        $stmt->bindParam(26, $pat_bldg_ca, PDO::PARAM_STR);
        $stmt->bindParam(27, $hperson_street_block_ca, PDO::PARAM_STR);
        $stmt->bindParam(28, $pat_region_ca, PDO::PARAM_STR);
        $stmt->bindParam(29, $pat_province_ca, PDO::PARAM_STR);
        $stmt->bindParam(30, $pat_municipality_ca, PDO::PARAM_STR);
        $stmt->bindParam(31, $pat_barangay_ca, PDO::PARAM_STR);
        $stmt->bindParam(32, $pat_email_ca, PDO::PARAM_STR);
        $stmt->bindParam(33, $pat_homephone_no_ca, PDO::PARAM_STR);
        $stmt->bindParam(34, $pat_mobile_no_ca, PDO::PARAM_STR);

        $stmt->bindParam(35, $pat_bldg_cwa, PDO::PARAM_STR);
        $stmt->bindParam(36, $hperson_street_block_pa_cwa, PDO::PARAM_STR);
        $stmt->bindParam(37, $pat_region_cwa, PDO::PARAM_STR);
        $stmt->bindParam(38, $pat_province_cwa, PDO::PARAM_STR);
        $stmt->bindParam(39, $pat_municipality_cwa, PDO::PARAM_STR);
        $stmt->bindParam(40, $pat_barangay_cwa, PDO::PARAM_STR);
        $stmt->bindParam(41, $pat_namework_place, PDO::PARAM_STR);
        $stmt->bindParam(42, $pat_landline_no, PDO::PARAM_STR);
        $stmt->bindParam(43, $pat_email_ca, PDO::PARAM_STR);

        $stmt->bindParam(44, $pat_emp_name, PDO::PARAM_STR);
        $stmt->bindParam(45, $pat_occupation_ofw, PDO::PARAM_STR);
        $stmt->bindParam(46, $pat_place_work, PDO::PARAM_STR);
        $stmt->bindParam(47, $pat_bldg_ofw, PDO::PARAM_STR);
        $stmt->bindParam(48, $hperson_street_block_ofw, PDO::PARAM_STR);
        $stmt->bindParam(49, $pat_region_ofw, PDO::PARAM_STR);
        $stmt->bindParam(50, $pat_province_ofw, PDO::PARAM_STR);
        $stmt->bindParam(51, $pat_city_ofw, PDO::PARAM_INT);
        $stmt->bindParam(52, $pat_country_ofw, PDO::PARAM_INT);
        $stmt->bindParam(53, $pat_office_mobile_no_ofw, PDO::PARAM_STR);
        $stmt->bindParam(54, $pat_mobile_no_ofw, PDO::PARAM_STR);
        $stmt->bindParam(55, $created_at, PDO::PARAM_STR);

        $stmt->execute();
        
        $act_type = 'pat_form';
        $pat_name = $patlast . ', ' . $patfirst . ' ' . $patmiddle;
        $action = 'Register Patient: ';
        $sql = "INSERT INTO history_log (hpercode, hospital_code, date, activity_type, action, pat_name, username) VALUES (?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(1, $hpercode, PDO::PARAM_STR);
        $stmt->bindParam(2, $_SESSION['hospital_code'], PDO::PARAM_INT);
        $stmt->bindParam(3, $created_at, PDO::PARAM_STR);
        $stmt->bindParam(4, $act_type, PDO::PARAM_STR);
        $stmt->bindParam(5, $action, PDO::PARAM_STR);
        $stmt->bindParam(6, $pat_name, PDO::PARAM_STR);
        $stmt->bindParam(7, $_SESSION['user_name'], PDO::PARAM_STR);

        $stmt->execute();
    }else{
        echo "duplicate";
    }

?>