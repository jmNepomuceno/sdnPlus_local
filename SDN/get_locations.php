<?php
    include("../database/connection2.php");
    
    $val = $_GET['val'];
    if($val == 'region'){
        $region_code = $_GET['region_code'];
        echo $region_code;

        //fetch the word region because we are only getting the Region CODE
        // $stmt = $pdo->query("SELECT region_description FROM region WHERE region_code = '". $region_code ."' ");
        // $region_name = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // echo $region_name[0]['region_description'];

        $stmt = $pdo->prepare('SELECT province_code, province_description FROM provinces WHERE region_code = ?');
        $stmt->execute([$region_code]);

        echo '<option value=""> Choose a Province </option>';
        while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
            echo '<option value="' , $data['province_code'] , '">' , $data['province_description'] , '</option>';
        }  
    }

    if($val == 'province'){
        $province_code = $_GET['province_code'];
        echo $province_code;
        
        $stmt = $pdo->prepare('SELECT municipality_code, municipality_description FROM city WHERE province_code = ?');
        $stmt->execute([$province_code]);

        echo '<option value=""> Choose a Municipality </option>';
        while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
            echo '<option value="' , $data['municipality_code'] , '">' , $data['municipality_description'] , '</option>';

            // echo $data['ctyzipcode'];
        }  
    }

    if($val == 'city'){
        $city_code = $_GET['city_code'];
        $stmt = $pdo->prepare('SELECT barangay_code, barangay_description FROM barangay WHERE bgymuncod = ?');
        $stmt->execute([$city_code]);

        echo '<option value=""> Choose a Barangay </option>';
        while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
            echo '<option value="' , $data['barangay_code'] , '">' , $data['barangay_description'] , '</option>';
        }  

        //uncomment, then slice last 4 value
        $stmt = $pdo->prepare('SELECT * FROM city WHERE municipality_code = "'. $city_code .'"');
        $stmt->execute();
        while($temp = $stmt->fetch(PDO::FETCH_ASSOC)){
            echo $temp['ctyzipcode'];
        }  
    }

  
    

?>