<?php

    include("../database/connection2.php");
    include("../csrf/session.php");

    echo $_SESSION['_csrf_token'];


    $personal_information = $_POST['personal_information'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $name_extension = $_POST['name_extension'];
    $birthday = $_POST['birthday'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $civil_status = $_POST['civil_status'];


    $region = $_POST['region'];
    $province = $_POST['province'];
    $municipality = $_POST['municipality'];
    $barangay = $_POST['barangay'];
    $street = $_POST['street'];


    $occupation = $_POST['occupation'];
    $nationality = $_POST['nationality'];
    $house_no = $_POST['house_no'];
    $passport_no = $_POST['passport_no'];
    $hospital_no = $_POST['hospital_no'];
    $phic_no = $_POST['phic_no'];
    $permanent_address = $_POST['permanent_address'];
    $home_phone_no = $_POST['home_phone_no'];
    $mobile_no = $_POST['mobile_no'];


    $email_address = $_POST['email_address'];
    $current_address = $_POST['current_address'];
    $current_workplace_address = $_POST['current_workplace_address'];
    $workplace_house_no = $_POST['workplace_house_no'];
    $workplace_street = $_POST['workplace_street'];
    $workplace_region = $_POST['workplace_region'];
    $workplace_province = $_POST['workplace_province'];
    $workplace_municipality = $_POST['workplace_municipality'];
    $workplace_barangay = $_POST['workplace_barangay'];
    $name_of_workplace = $_POST['name_of_workplace'];
    $landline_mobile_no = $_POST['landlanie_mobile_no'];
    $address_outside_philippines = $_POST['address_outside_philippines'];
    $employers_name = $_POST['employers_name'];
    $place_of_work = $_POST['place_of_work'];
    $country = $_POST['country'];
    $official_phone_no = $_POST['official_phone_no'];
   


        $sql = "INSERT INTO hperson(personal_information,first_name,last_name,middle_name,name_extension,birthday,age,sex,civil_status,
                            region,province,municipality,barangay,street,occupation,nationality,house_no,passport_no,hospital_no,phic_no,
                            permanent_address,home_phone_no,mobile_no,email_address,current_address,current_workplace_address,workplace_house_no,
                            workplace_street,workplace_region,workplace_province,workplace_municipality,workplace_barangay,name_of_workplace,
                            landline_mobile_no,address_outside_philippines,employers_name,place_of_work,country,official_phone_no)
                            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";


        $stmt = $pdo ->prepare($sql);



        $stmt->bindParam(1, $personal_information, PDO::PARAM_INT);
        $stmt->bindParam(2, $first_name, PDO::PARAM_STR);
        $stmt->bindParam(3, $middle_name, PDO::PARAM_STR);
        $stmt->bindParam(4, $name_extension, PDO::PARAM_STR);
        $stmt->bindParam(5, $birthday, PDO::PARAM_STR);
        $stmt->bindParam(6, $age, PDO::PARAM_STR);
        $stmt->bindParam(7, $sex, PDO::PARAM_STR);
        $stmt->bindParam(8, $civil_status, PDO::PARAM_INT);


        $stmt->bindParam(9, $region, PDO::PARAM_STR);
        $stmt->bindParam(10, $province, PDO::PARAM_STR);
        $stmt->bindParam(11, $municipality, PDO::PARAM_STR);
        $stmt->bindParam(12, $barangay, PDO::PARAM_STR);
        $stmt->bindParam(13, $street, PDO::PARAM_STR);

        $stmt->bindParam(14, $occupation, PDO::PARAM_STR);
        $stmt->bindParam(15, $nationality, PDO::PARAM_STR);
        $stmt->bindParam(16, $house_no, PDO::PARAM_STR);
        $stmt->bindParam(17, $passport_no, PDO::PARAM_STR);
        $stmt->bindParam(18, $hospital_no, PDO::PARAM_STR);
        $stmt->bindParam(19, $phic_no, PDO::PARAM_STR);
        $stmt->bindParam(20, $permanent_address, PDO::PARAM_STR);
        $stmt->bindParam(21, $home_phone_no, PDO::PARAM_STR);
        $stmt->bindParam(22, $mobile_no, PDO::PARAM_STR);

        $stmt->bindParam(23, $email_address, PDO::PARAM_STR);
        $stmt->bindParam(24, $current_address, PDO::PARAM_STR);
        $stmt->bindParam(25, $current_workplace_address, PDO::PARAM_STR);
        $stmt->bindParam(26, $workplace_house_no, PDO::PARAM_STR);
        $stmt->bindParam(27, $workplace_street, PDO::PARAM_STR);
        $stmt->bindParam(28, $workplace_region, PDO::PARAM_STR);
        $stmt->bindParam(29, $workplace_province, PDO::PARAM_STR);
        $stmt->bindParam(30, $workplace_municipality, PDO::PARAM_STR);
        $stmt->bindParam(31, $home_phone_no, PDO::PARAM_STR);
        $stmt->bindParam(32, $workplace_barangay, PDO::PARAM_STR);
        $stmt->bindParam(33, $name_of_workplace, PDO::PARAM_STR);
        $stmt->bindParam(34, $landline_mobile_no, PDO::PARAM_STR);
        $stmt->bindParam(35, $address_outside_philippines, PDO::PARAM_STR);
        $stmt->bindParam(36, $employers_name, PDO::PARAM_STR);
        $stmt->bindParam(37, $place_of_work, PDO::PARAM_STR);
        $stmt->bindParam(38, $country, PDO::PARAM_STR);
        $stmt->bindParam(39, $official_phone_no, PDO::PARAM_STR);

        $stmt->execute();


?>