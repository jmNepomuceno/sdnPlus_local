<?php 
    include("../database/connection2.php");
    session_start();
    
    $temp = $_POST['temp'];

    $sql = "";
    if($temp === 'hospital_name_DESC'){
        $sql = "SELECT * FROM sdn_hospital ORDER BY hospital_name DESC";
    }else if($temp === 'hospital_code_DESC'){
        $sql = "SELECT * FROM sdn_hospital ORDER BY hospital_code DESC";
    }else if($temp === 'hospital_isVerified_DESC'){
        $sql = "SELECT * FROM sdn_hospital ORDER BY hospital_isVerified DESC";
    }else if($temp === 'hospital_name_ASC'){
        $sql = "SELECT * FROM sdn_hospital ORDER BY hospital_name ASC";
    }else if($temp === 'hospital_code_ASC'){
        $sql = "SELECT * FROM sdn_hospital ORDER BY hospital_code ASC";
    }else if($temp === 'hospital_isVerified_ASC'){
        $sql = "SELECT * FROM sdn_hospital ORDER BY hospital_isVerified ASC";
    }


    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_sdn_hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $table_header_arr = ['Hospital Name' , 'Hospital Code', 'Verified', 'Number of Users', ""];
    $sub_table_header_arr = ['Last Name' , 'First Name', 'Middle Name', 'Username', 'Password', 'Active', 'Action'];

    $sql = "SELECT classifications FROM classifications";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>'; print_r($data); echo '</pre>';

    $classification_arr = array();
    for($i = 0; $i < count($data); $i++){
        array_push($classification_arr, $data[$i]['classifications']);
    }
    
    $sql = "SELECT * FROM sdn_users";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_sdn_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>'; print_r($data); echo '</pre>';
    
    // retrieve all the hospital code that has 2 users
    $sql = "SELECT hospital_code FROM sdn_users WHERE user_count=2";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_sdn_users_count2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT hospital_code FROM sdn_users WHERE user_count=1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_sdn_users_count1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>'; print_r($data_sdn_users_count2); echo '</pre>';

    $users_count_2_hcode = array();
    $users_count_1_hcode = array();

    for($i = 0; $i < count($data_sdn_users_count2); $i++){
        array_push($users_count_2_hcode, $data_sdn_users_count2[$i]['hospital_code']);
    }

    for($i = 0; $i < count($data_sdn_users_count1); $i++){
        array_push($users_count_1_hcode, $data_sdn_users_count1[$i]['hospital_code']);
    }

    for($i = 0; $i < count($data_sdn_hospitals); $i++) {
        if($data_sdn_hospitals[$i]['hospital_isVerified'] === 1){
            $hospital_isVerified = 'Verified';
        }else{
            $hospital_isVerified = 'Not Verified';
        }

        $number_users = 0;
        

        if(in_array($data_sdn_hospitals[$i]['hospital_code'], $users_count_1_hcode)){
            $number_users = 1;
        }

        if(in_array($data_sdn_hospitals[$i]['hospital_code'], $users_count_2_hcode)){
            $number_users = 2;
        }

        $color_style = "#fffff";
        $sub_color_style = "#fffff";
        if($i % 2 == 0){
            $color_style = "#999999";
            $sub_color_style = "#cccccc";   
        }

        $users_curr_hospitals = "";
        $sql = "SELECT * FROM sdn_users WHERE hospital_code=:code";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':code', $data_sdn_hospitals[$i]['hospital_code'], PDO::PARAM_INT);
        $stmt->execute();
        $users_curr_hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '
            <tr class="table-tr h-[50px] w-full border border-[#b3b3b3] text-base bg-['.$color_style.'] font-medium">
                <td class="border-r border-[#b3b3b3] w-[450px] h-full"> '. $data_sdn_hospitals[$i]['hospital_name'] .'</td>
                <td class="border-r border-[#b3b3b3] w-[200px] h-full"> '. $data_sdn_hospitals[$i]['hospital_code'] .'</td>
                <td class="border-r border-[#b3b3b3] w-[130px] h-full"> '. $hospital_isVerified .'</td>

                <td class="border-r border flex flex-col justify-center items-center w-full h-full text-center"> 
                    <div class="number_users w-[90%] h-[25px] flex flex-row justify-center items-center"> '.$number_users .' </div>
                    
                    <div class="hidden breakdown-div w-[95%] h-[300px] bg-['.$sub_color_style .'] rounded flex flex-row justify-center items-center overflow-hidden">
                        <table class="w-[97%] h-[95%] text-center rounded">
                            <thead>
                                <tr>
        '; 
        
                                        for($j = 0; $j < count($sub_table_header_arr); $j++) { 
                                            echo '<th class="border border-[#b3b3b3] p-3 bg-[#333333] text-white text-xs"> '. $sub_table_header_arr[$j] .'</th>';
                                        };
        echo'                   </tr>
                            </thead>
                            <tbody>
                                    ';

                                if(count($users_curr_hospitals) === 2){
                                    for($x = 0; $x < 2; $x++) {
                                        echo 
                                        '
                                            <tr class="h-[50%] w-full border border-[#b3b3b3] text-base bg-['.$color_style.'] font-medium">
                                                <td class="border-r border-[#b3b3b3] w-[100px] text-sm"> 
                                                    <input type="text" class="edit-users-info w-[90%] outline-none h-[30px] text-center text-sm bg-transparent pointer-events-none" value= "'.$users_curr_hospitals[$x]["user_lastname"].'" />
                                                </td>
                                                <td class="border-r border-[#b3b3b3] w-[100px] text-sm"> 
                                                <input type="text" class="edit-users-info w-[90%] outline-none h-[30px] text-center text-sm bg-transparent pointer-events-none" value="'.$users_curr_hospitals[$x]['user_firstname'].'" />
                                                </td>
                                                <td class="border-r border-[#b3b3b3] w-[100px] text-sm"> 
                                                    <input type="text" class="edit-users-info w-[90%] outline-none h-[30px] text-center text-sm bg-transparent pointer-events-none" value= '. $users_curr_hospitals[$x]["user_middlename"] .' />
                                                </td>
                                                <td class="border-r border-[#b3b3b3] w-[100px] text-sm"> 
                                                    <input type="text" class="edit-users-info w-[90%] outline-none h-[30px] text-center text-sm bg-transparent pointer-events-none" value= '. $users_curr_hospitals[$x]["username"] .' />
                                                </td>
                                                <td class="border-r border-[#b3b3b3] w-[100px] text-sm"> 
                                                    <input type="text" class="edit-users-info w-[90%] outline-none h-[30px] text-center text-sm bg-transparent pointer-events-none" value= '. $users_curr_hospitals[$x]["password"] .' />
                                                </td>
                                            ';

                                            if($users_curr_hospitals[$x]['user_isActive'] === 0) {
                                                    echo '<td class="border-r border-[#b3b3b3] w-[100px] text-sm"> Inactive</td>';
                                            }else{
                                                echo '<td class="border-r border-[#b3b3b3] w-[100px] text-sm"> Active</td>';
                                            } 
                                            echo '
                                            <td class="border-r border-[#b3b3b3] w-[100px] text-sm">
                                                <button type="button" class="edit-info-btn bg-[#0d6efd] w-[90%] h-[35px] text-white rounded-md p-1">Edit</button>
                                                <button type="button" class="hidden cancel-info-btn bg-[#6c757d] w-[90%] h-[35px] text-white rounded-md p-1 mt-2">Close</button>
                                                </td>
                                                <input class="hcode-edit-info" type="hidden" name="hcode-edit-info" value=<'.$users_curr_hospitals[$x]['hospital_code'].' />
                                            </tr>
                                            ';
                                                
                                        
                                    }
                                }

                                if(count($users_curr_hospitals) === 1){
                                    echo 
                                        '
                                            <tr class="h-[50%] w-full border border-[#b3b3b3] text-base bg-['.$color_style.'] font-medium">
                                                <td class="border-r border-[#b3b3b3] w-[100px] text-sm"> 
                                                    <input type="text" class="edit-users-info w-[90%] outline-none h-[30px] text-center text-sm bg-transparent pointer-events-none" value= '.$users_curr_hospitals[0]["user_lastname"] .' />
                                                </td>
                                                <td class="border-r border-[#b3b3b3] w-[100px] text-sm"> 
                                                <input type="text" class="edit-users-info w-[90%] outline-none h-[30px] text-center text-sm bg-transparent pointer-events-none" value="'.$users_curr_hospitals[0]['user_firstname'].'" />
                                                </td>
                                                <td class="border-r border-[#b3b3b3] w-[100px] text-sm"> 
                                                    <input type="text" class="edit-users-info w-[90%] outline-none h-[30px] text-center text-sm bg-transparent pointer-events-none" value= '. $users_curr_hospitals[0]["user_middlename"] .' />
                                                </td>
                                                <td class="border-r border-[#b3b3b3] w-[100px] text-sm"> 
                                                    <input type="text" class="edit-users-info w-[90%] outline-none h-[30px] text-center text-sm bg-transparent pointer-events-none" value= '. $users_curr_hospitals[0]["username"] .' />
                                                </td>
                                                <td class="border-r border-[#b3b3b3] w-[100px] text-sm"> 
                                                    <input type="text" class="edit-users-info w-[90%] outline-none h-[30px] text-center text-sm bg-transparent pointer-events-none" value= '. $users_curr_hospitals[0]["password"] .' />
                                                </td>
                                            ';

                                            if($users_curr_hospitals[0]['user_isActive'] === 0) {
                                                    echo '<td class="border-r border-[#b3b3b3] w-[100px] text-sm"> Inactive</td>';
                                            }else{
                                                echo '<td class="border-r border-[#b3b3b3] w-[100px] text-sm"> Active</td>';
                                            } 
                                            echo '
                                            <td class="border-r border-[#b3b3b3] w-[100px] text-sm">
                                                <button type="button" class="edit-info-btn bg-[#0d6efd] w-[90%] h-[35px] text-white rounded-md p-1">Edit</button>
                                                <button type="button" class="hidden cancel-info-btn bg-[#6c757d] w-[90%] h-[35px] text-white rounded-md p-1 mt-2">Close</button>
                                                </td>
                                                <input class="hcode-edit-info" type="hidden" name="hcode-edit-info" value=<'.$users_curr_hospitals[0]['hospital_code'].' />
                                            </tr>
                                        ';
                                }
                echo  
                '
                </tbody>
                        </table>
                    </div>
                </td>
                <td class="w-[50px]"><i class="see-more-btn fa-regular fa-square-caret-down cursor-pointer"></i></td> 

            </tr>
                ';
                            
    }
?>