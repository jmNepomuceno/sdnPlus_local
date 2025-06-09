<?php
    include("../database/connection2.php");
    session_start();
    
    $hpercode = $_POST['hpercode'];

    $sql = "SELECT patbdate, pat_age, patsex, pat_curr_barangay, pat_curr_municipality, pat_curr_province, pat_curr_region, pat_email, pat_mobile_no, pat_homephone_no, created_at, hpatcode  FROM hperson WHERE hpercode=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hpercode]);
    $hperson_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // decode the code address from barangay to region
    $sql = "SELECT barangay_description FROM barangay WHERE barangay_code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hperson_data[0]['pat_curr_barangay']]);
    $decoded = $stmt->fetch(PDO::FETCH_ASSOC);
    $hperson_data[0]['pat_curr_barangay'] = $decoded['barangay_description'];

    $sql = "SELECT municipality_description FROM city WHERE municipality_code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hperson_data[0]['pat_curr_municipality']]);
    $decoded = $stmt->fetch(PDO::FETCH_ASSOC);
    $hperson_data[0]['pat_curr_municipality'] = $decoded['municipality_description'];

    $sql = "SELECT province_description FROM provinces WHERE province_code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hperson_data[0]['pat_curr_province']]);
    $decoded = $stmt->fetch(PDO::FETCH_ASSOC);
    $hperson_data[0]['pat_curr_province'] = $decoded['province_description'];

    $sql = "SELECT region_description FROM region WHERE region_code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hperson_data[0]['pat_curr_region']]);
    $decoded = $stmt->fetch(PDO::FETCH_ASSOC);
    $hperson_data[0]['pat_curr_region'] = $decoded['region_description'];

    // decode the hospital code
    $sql = "SELECT hospital_name FROM sdn_hospital WHERE hospital_code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hperson_data[0]['hpatcode']]);
    $decoded = $stmt->fetch(PDO::FETCH_ASSOC);
    $hperson_data[0]['hpatcode'] = $decoded['hospital_name'];


    // fetch the needed information for patients referral information
    $sql = "SELECT patlast, patfirst, patmiddle, patsuffix, type, pat_class, date_time, referred_by, refer_to, approved_time, approval_details, reason, status, discharged_time, icd_diagnosis FROM incoming_referrals WHERE hpercode=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hpercode]);
    $incoming_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //asdf
    for($i = 0; $i < count($incoming_data); $i++){
        $sql = "SELECT icd10_title FROM icd_code_mapping WHERE icd10_code=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$incoming_data[$i]['icd_diagnosis']]);
        $icd_diag = $stmt->fetch(PDO::FETCH_ASSOC);
        $incoming_data[$i]['icd_diagnosis'] = $icd_diag['icd10_title'];
    }
    
    $sql = "SELECT referral_id FROM hperson WHERE hpercode=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hpercode]);
    $data_referral_id = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decode the referral_id values from JSON to arrays
    foreach ($data_referral_id as &$row) {
        $row['referral_id'] = json_decode($row['referral_id'], true);
    }

    // Extract referral_id values from $data_referral_id
    $referral_ids = array_column($data_referral_id, 'referral_id');

    
    $mergedData = array_merge($hperson_data, $incoming_data, $referral_ids);
    $jsonData = json_encode($mergedData);
    echo $jsonData;
?>