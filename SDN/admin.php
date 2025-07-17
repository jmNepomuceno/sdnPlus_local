<?php 
session_start();
include('../database/connection2.php');
date_default_timezone_set('Asia/Manila');

// Check if the database connection is established
if (!isset($pdo)) {
    die("Database connection failed.");
}

if ($_SESSION['user_name'] === 'admin'){
    $user_name = 'Bataan General Hospital and Medical Center';
} else {
    $user_name = htmlspecialchars($_SESSION['hospital_name'], ENT_QUOTES, 'UTF-8');
}

$webpage_date_traverse = date('Y-m-d H:i:s');
$_SESSION['webpage_date_traverse'] = $webpage_date_traverse;

$table_header_arr = ['Hospital Name', 'Hospital Code', 'Verified', 'Number of Users', 'Contact Information', 'Hospital Director', 'Mobile No.', 'Point Person', 'Mobile No.', 'Action'];
$sub_table_header_arr = ['Last Name', 'First Name', 'Middle Name', 'Username', 'Password', 'Active', 'Action'];
$add_user_table_header_arr = ['First Name', 'Last Name', 'Username', 'Password', 'Role', 'Access', 'Action'];

try {
    $sql = "SELECT classifications FROM classifications";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$classification_arr = array();
foreach ($data as $row) {
    array_push($classification_arr, htmlspecialchars($row['classifications'], ENT_QUOTES, 'UTF-8'));
}

// Fetch sdn users
try {
    $sql = "SELECT * FROM sdn_users";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_sdn_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Retrieve all the hospital codes
try {
    $sql = "SELECT hospital_code FROM sdn_users WHERE user_count=2";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_sdn_users_count2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT hospital_code FROM sdn_users WHERE user_count=1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_sdn_users_count1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$users_count_2_hcode = array();
$users_count_1_hcode = array();

foreach ($data_sdn_users_count2 as $row) {
    array_push($users_count_2_hcode, htmlspecialchars($row['hospital_code'], ENT_QUOTES, 'UTF-8'));
}

foreach ($data_sdn_users_count1 as $row) {
    array_push($users_count_1_hcode, htmlspecialchars($row['hospital_code'], ENT_QUOTES, 'UTF-8'));
}

try {
    $sql = "SELECT * FROM sdn_users WHERE hospital_code=9312";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users_curr_hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

try {
    $sql = "SELECT COUNT(*) FROM incoming_referrals WHERE status='Pending' AND refer_to=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['hospital_name']]);
    $incoming_num = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <?php require "../header_link.php" ?>
    
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .custom-modal-width {
            max-width: 95vw; /* Adjust the width as per your requirements */
            width: 100%;
        }

        @media only screen and (max-height: 800px){
            #myModal-hospitalAndUsers #modal-body-main{
                height: 500px;
            }

            .custom-modal-width {
                max-width: 90vw;
                width: 100%;
            }
        }

    </style>
</head>
<body class="h-screen">
    <header class="header-div">
        <div class="side-bar-title">
            <h1 id="sdn-title-h1"> Service Delivery Network</h1>
            <div class="side-bar-mobile-btn">
                <i id="navbar-icon" class="fa-solid fa-angles-left"></i>
            </div>
        </div>
        <div class="account-header-div">
            <div class="notif-main-div">
               
                    <div id="notif-div">
                        <?php 
                            if($incoming_num['COUNT(*)'] > 0){
                                echo '<h1 id="notif-circle" style="display:block;"><span id="notif-span"></span></h1>';
                            }else{
                                echo '<h1 id="notif-circle" style="display:none;"><span id="notif-span"></span></h1>';
                            }
                        ?>
                        <i class="fa-solid fa-bell"></i> 
                        <audio id="notif-sound" preload='auto' muted loop>
                            <source src="../assets/sound/incoming_message.mp3" type="audio/mpeg">
                        </audio>

                        <div id="notif-sub-div">
                            <!-- <div class="h-[30px] w-full border border-black flex flex-row justify-evenly items-center">
                                <h4 class="font-bold text-lg">3</h4>
                                <h4 class="font-bold text-lg">OB</h4>
                            </div> -->
                        </div>
                    </div>
            </div>

            <div id="nav-account-div" class="header-username-div">
                <div class="user-icon-div">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="user-name-div">
                    <!-- <h1 class="text-white text-lg hidden sm:block">John Marvin Nepomuceno</h1> -->
                    <?php 
                        if($_SESSION['last_name'] === 'Administrator'){
                            echo '<h1 id="user_name-id">' . $user_name . ' | ' . $_SESSION["last_name"] . '</h1>';
                        }else{
                            echo '<h1 id="user_name-id">' . $user_name . ' | ' . $_SESSION["last_name"] . ', ' . $_SESSION['first_name'] . ' ' . $_SESSION['middle_name'] . '</h1>';;

                        }
                    ?>
                    
                </div>
                <div class="username-caret-div">
                    <i class="fa-solid fa-caret-down"></i>
                </div>
            </div>
        </div>
    </header>

    <div id="nav-drop-account-div">
        <div id="nav-drop-acc-sub-div">
            
            <?php if($_SESSION["user_name"] == "admin") {?>
                <div id="admin-module-btn" class="nav-drop-btns">
                    <h2 id="admin-module-id" class="nav-drop-btns-txt">Admin</h2>
                </div>
            <?php } ?>
            <div id="dashboard-incoming-btn" class="nav-drop-btns">
                <h2 class="nav-drop-btns-txt">Dashboard (Incoming)</h2>
            </div>

            <div id="dashboard-outgoing-btn" class="nav-drop-btns">
                <h2 class="nav-drop-btns-txt">Dashboard (Outgoing)</h2>
            </div>

            <div id="history-log-btn" class="nav-drop-btns">
                <h2 class="nav-drop-btns-txt">History Log</h2>
            </div>

            <div class="nav-drop-btns" id="setting-btn">
                <h2 class="nav-drop-btns-txt">Settings</h2>
            </div>

            <div class="nav-drop-btns">
                <h2 class="nav-drop-btns-txt">Help</h2>
            </div>

            <div class="nav-drop-btns">
                <h2 id='logout-btn' class="nav-drop-btns-txt" data-bs-toggle="modal" data-bs-target="#myModal-prompt">Logout</h2>
            </div>
        </div>
    </div>

    <div class="main-div">

    <?php if($_SESSION['user_name'] === 'admin042801'){?>
            <div class="user-access-div">
                <i class="fa-solid fa-users"></i>
                <label id="user-access-lbl" class="tile-title-div" data-bs-toggle="modal" data-bs-target="#myModal-user-access">
                    User Accessibility
                </label>
            </div>
    <?php } ?>
        

        <div class="add-classification-div">
            <i class="fa-solid fa-file-import"></i>
            <label id="add-classification-lbl" class="tile-title-div" data-bs-toggle="modal" data-bs-target="#myModal-add-classification">
                Add Patient's Classification
            </label>
        </div>

        <div class="hospital-users-div">
            <i class="fa-solid fa-users"></i>
            <label id="hospital-users-lbl" class="tile-title-div" data-bs-toggle="modal" data-bs-target="#myModal-hospitalAndUsers">
                Hospitals and Users
            </label>
        </div>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="myModal-add-classification" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 id="modal-title-main" class="modal-title-main" id="exampleModalLabel">Add New Patient's Classification</h5>
                    <!-- <i class="fa-solid fa-circle-check"></i> -->
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="modal-body-main" class="modal-body-main">
                <div class="add-classification-div">
                    <!-- <input  type="text" id="add-classification-txt" class="border-2 border-black" placeholder="New Classification" /> -->
                    <!-- <button id="add-classification-btn" class="border-2 border-black">Add</button> -->

                    <h2>Current Patient's Classifications</h2>
                    <div>
                        <div id="populate-patclass-div">
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="delete-classification-btn" type="button" data-bs-dismiss="modal">Delete</button>
                <button id="add-classification-btn" type="button" data-bs-dismiss="modal">Add</button>
            </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="myModal-user-access" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered custom-modal-width" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 id="modal-title-main" class="modal-title-main" id="exampleModalLabel">User Accessibility</h5>
                    <!-- <i class="fa-solid fa-circle-check"></i> -->
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div id="modal-body-main" class="modal-body-main">
                <div class="user-access-div">
                    <div class="search-bar">
                        <input class="inputs-class form-control" id="search-input" type="text" placeholder="Search User">
                    
                        <div class="add-user-div">
                            <input type="text" id="user-fname" class="inputs-class form-control" placeholder="First Name" />
                            <input type="text" id="user-mname" class="inputs-class form-control" placeholder="Middle Name" />
                            <input type="text" id="user-lname" class="inputs-class form-control" placeholder="Last Name" />
                            <input type="text" id="user-name" class="inputs-class form-control" placeholder="Username" />
                            <input type="password" id="user-pw" class="inputs-class form-control" placeholder="Password" />
                            <div id="access-role-div">
                                <label>
                                    <input type="checkbox" id="admin" name="role" value="admin" class="role-checkbox">
                                    Admin
                                </label>
                                <label>
                                    <input type="checkbox" id="doctor-admin" name="role" value="doctor_admin" class="role-checkbox">
                                    Doctor
                                </label>
                            </div>
                            <button id="add-user-btn">Add</button>
                        </div>
                    </div>

                    
                    <div id="scroll-div-2">

                        <table id="user-access-table">
                            <thead class="">
                                <tr>
                                    <?php for($i = 0; $i < count($add_user_table_header_arr); $i++) { ?>
                                        <th> 
                                            <div>
                                                <?php echo $add_user_table_header_arr[$i] ?>
                                            </div>
                                        </th>
                                    <?php } ?>
                                </tr>
                            </thead>
                                <!-- <tr>
                                    <td id="access-fullname-td">Nepomuceno, John Marvin</td>
                                    <td id="access-username-td">nepnep</td>
                                    <td id="access-password-td">open</td>
                                    <td id="access-access-td">all</td>
                                    <td id="access-action-td">Save</td>
                                </tr> -->
                            
                            <tbody>
                            <?php 
                                $sql = "SELECT * FROM sdn_users WHERE role='doctor_admin' ";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();
                                $data_user_access = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                // Decode permissions once
                                foreach ($data_user_access as &$user) {
                                    if (isset($user['permission']) && !empty($user['permission'])) {
                                        $user['permission'] = json_decode($user['permission'], true);
                                    }
                                }

                                $style = "";

                                for ($i = 0; $i < count($data_user_access); $i++) {
                                    $style = ($i % 2 == 0) ? "background: #e2e7e9;" : "background: none;";
                                
                                    echo '
                                    <tr style="' . $style .'">
                                        <td class="access-details" id="access-firstname-td"> 
                                            <input type="text" class="access-details-inputs form-control" value="' . htmlspecialchars($data_user_access[$i]['user_firstname']) . '" />
                                        </td>
                                        <td class="access-details" id="access-lastname-td">
                                            <input type="text" class="access-details-inputs form-control" value="' . htmlspecialchars($data_user_access[$i]['user_lastname']) . '" />
                                        </td>
                                        <td class="access-details" id="access-username-td">
                                            <input type="text" class="access-details-inputs form-control" value="' . htmlspecialchars($data_user_access[$i]['username']) . '" />
                                        </td>
                                        <td class="access-details password-mask" id="access-password-td">
                                            <input type="password" class="access-details-inputs form-control" value="' . htmlspecialchars($data_user_access[$i]['password']) . '" />
                                        </td>
                                
                                        <td class="access-details access-role-td" id="access-role-div">
                                            <label>
                                                <input type="checkbox" id="admin" name="role" value="admin" class="role-checkbox-add" ' . 
                                                ($data_user_access[$i]['role'] === 'admin' ? 'checked' : '') . '>
                                                Admin
                                            </label>
                                            <label>
                                                <input type="checkbox" id="doctor-admin" name="role" value="doctor_admin" class="role-checkbox-add" ' . 
                                                ($data_user_access[$i]['role'] === 'doctor_admin' ? 'checked' : '') . '>
                                                Doctor Admin
                                            </label>
                                        </td>
                                
                                        <td class="access-details access-access-td">
                                            <div class="access-permision-div">
                                                <div id="left-div">';
                                                
                                                // Use pre-decoded permissions
                                                $permissions = $data_user_access[$i]['permission'];
                                                $count = 0;
                                                if ($permissions) {
                                                    foreach ($permissions as $key => $value) {
                                                        if ($count < 3) { // First 3 permissions for left div
                                                            $label = ucwords(str_replace('_', ' ', $key));
                                                            $checked = $value ? 'checked' : '';
                                                            echo '
                                                            <label>
                                                                <input type="checkbox" id="' . $key . '-permission" name="permission" value="' . $key . '" class="permission-checkbox" ' . $checked . '>
                                                                ' . $label . '
                                                            </label>';
                                                        }
                                                        $count++;
                                                    }
                                                }
                                
                                                echo '
                                                </div>
                                
                                                <div id="middle-div">';
                                                
                                                // Next 2 permissions for the middle div
                                                $count = 0;
                                                if ($permissions) {
                                                    foreach ($permissions as $key => $value) {
                                                        if ($count >= 3 && $count < 5) { 
                                                            $label = ucwords(str_replace('_', ' ', $key));
                                                            $checked = $value ? 'checked' : '';
                                                            echo '
                                                            <label>
                                                                <input type="checkbox" id="' . $key . '-permission" name="permission" value="' . $key . '" class="permission-checkbox" ' . $checked . '>
                                                                ' . $label . '
                                                            </label>';
                                                        }
                                                        $count++;
                                                    }
                                                }
                                
                                                echo '
                                                </div>
                                
                                                <div id="right-div">';
                                                
                                                // Remaining permissions for right div
                                                $count = 0;
                                                if ($permissions) {
                                                    foreach ($permissions as $key => $value) {
                                                        if ($count >= 5) {
                                                            $label = ucwords(str_replace('_', ' ', $key));
                                                            $checked = $value ? 'checked' : '';
                                                            echo '
                                                            <label>
                                                                <input type="checkbox" id="' . $key . '-permission" name="permission" value="' . $key . '" class="permission-checkbox" ' . $checked . '>
                                                                ' . $label . '
                                                            </label>';
                                                        }
                                                        $count++;
                                                    }
                                                }
                                
                                                echo '
                                                </div>
                                                
                                            </div>
                                        </td>
                                
                                        <td class="access-action-td">
                                            <button class="access-action-btn">EDIT</button>
                                            <i class="fa-solid fa-trash" id="delete-user-btn"></i>
                                        </td>
                                    </tr>';
                                }
                                
                            ?>



                                
                            </tbody>
                        </table>
                    </div>

                </div>
                
                
            </div>
            <!-- <div class="modal-footer">
                <button id="delete-classification-btn" type="button" data-bs-dismiss="modal">Delete</button>
                <button id="add-classification-btn" type="button" data-bs-dismiss="modal">Add</button>
            </div> -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="myModal-hospitalAndUsers" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered custom-modal-width" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <div>
                    <button id="modal-title-main" class="modal-title-main btn btn-success" id="exampleModalLabel">Hospitals/BHS/RHU</button>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div id="modal-body-main" class="modal-body-main">
                <div id="scroll-div" class="add-classification-div">
                    <table id="main-table">
                        <thead class="">
                            <!-- 727 1536 -->
                            <tr>
                                <?php for($i = 0; $i < count($table_header_arr); $i++) { ?>
                                    <!-- <th class="border border-[#b3b3b3] p-3 bg-[#333333] text-white text-lg">  -->
                                    <th> 
                                        <div>
                                            <?php echo $table_header_arr[$i] ?>
                                            <?php if($i < 3){?>
                                                <div> 
                                                    <i id="sort-up-btn-id-<?php echo $i; ?>" class="sort-up-btn fa-solid fa-caret-up"></i>
                                                    <i id="sort-down-btn-id-<?php echo $i; ?>" class="sort-down-btn fa-solid fa-caret-down"></i>
                                                </div>
                                            <?php }?>
                                        </div>
                                    </th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <!-- Table body -->
                        <tbody>
                            
                            <?php 
                                $sql = "SELECT * FROM sdn_hospital";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute();
                                $data_sdn_hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php for($i = 0; $i < count($data_sdn_hospitals); $i++) { ?>
                                <?php
                                    // $hospital_isVerified    
                                    
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
                                        $color_style = "#d3dbde";
                                        $sub_color_style = "#cccccc";   
                                    }

                                    $hospital_mobile_number = $data_sdn_hospitals[$i]['hospital_mobile'];

                                    // echo $data_sdn_hospitals[$i]['hospital_code'];
                                    // echo '</br>';
                                    $users_curr_hospitals = "";
                                    $sql = "SELECT * FROM sdn_users WHERE hospital_code=:code";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->bindParam(':code', $data_sdn_hospitals[$i]['hospital_code'], PDO::PARAM_INT);
                                    $stmt->execute();
                                    $users_curr_hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    // print_r($users_curr_hospitals);
                                    // echo count($users_curr_hospitals);
                                    // echo $users_curr_hospitals[0]['user_firstname'];

                                    $style_action = "";
                                    if($hospital_isVerified === 'Not Verified' || $data_sdn_hospitals[$i]['hospital_autKey'] != ''){
                                        $style_action = "opacity: 0.1; pointer-events: none;";
                                    }else{
                                        $style_action = "opacity: 1; pointer-events: auto;";
                                    }
                                ?>
                                <tr class="table-tr" style="background: <?php echo $color_style ?>">
                                    <td id="hospital-name-td"> <?php echo $data_sdn_hospitals[$i]['hospital_name'] ?></td>
                                    <td id="hospital-code-td"> <?php echo $data_sdn_hospitals[$i]['hospital_code'] ?></td>
                                    <td id="hospital-ver-td"> <?php echo $hospital_isVerified ?></td>
                                    <!-- <td class="w-[300px] border-r border"> <?php echo $hospital_mobile_number ?> </td>  -->
                                    <td id="hospital-user-td"> 
                                        <div class="number_users"> <?php echo $number_users ?> </div>
                                        
                                        <div class="breakdown-div" style="background: <?php echo $sub_color_style ?>">
                                            <table>
                                                <thead>
                                                    <tr>
                                                    <?php for($j = 0; $j < count($sub_table_header_arr); $j++) { ?>
                                                        <th> <?php echo $sub_table_header_arr[$j] ?></th>
                                                    <?php } ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(count($users_curr_hospitals) === 2){?>
                                                        <?php for($x = 0; $x < 2; $x++) { ?>
                                                            <?php $user_firstName_var =$users_curr_hospitals[$x]['user_firstname']; ?>
                                                            <tr style="background: <?php echo $sub_color_style ?>">
                                                                <td> 
                                                                    <input type="text" class="edit-users-info" value="<?php echo $users_curr_hospitals[$x]['user_lastname'] ?>" />
                                                                </td>
                                                                <td> 
                                                                    <input type="text" class="edit-users-info" style="text-wrap:pretty" value="<?php echo $users_curr_hospitals[$x]['user_firstname']; ?>" />
                                                                </td>
                                                                <td> 
                                                                    <input type="text" class="edit-users-info" value= "<?php echo $users_curr_hospitals[$x]['user_middlename'] ?>" />
                                                                </td>
                                                                <td> 
                                                                    <input type="text" class="edit-users-info" value="<?php echo $users_curr_hospitals[$x]['username'] ?>"/>
                                                                </td>
                                                                <td> 
                                                                    <input type="text" class="edit-users-info" value= "<?php echo $users_curr_hospitals[$x]['password'] ?>" />
                                                                </td>
                                                                <?php if($users_curr_hospitals[$x]['user_isActive'] === 0) {?>
                                                                    <td> Inactive</td>
                                                                <?php }else{ ?>
                                                                    <td> Active</td>
                                                                <?php } ?>
                                                                <td>
                                                                    <button type="button" class="edit-info-btn">Edit</button>
                                                                    <button type="button" class="hidden cancel-info-btn">Close</button>
                                                                </td>
                                                                <input class="hcode-edit-info" type="hidden" name="hcode-edit-info" value=<?php echo $users_curr_hospitals[$x]['hospital_code'] ?> />
                                                            </tr>
                                                        <?php }?>
                                                    <?php } else if(count($users_curr_hospitals) === 1){ ?>
                                                            <tr style="background: <?php echo $color_style ?>">
                                                                <td> 
                                                                    <input type="text" class="edit-users-info" value= "<?php echo $users_curr_hospitals[0]['user_lastname'] ?>" />
                                                                </td>
                                                                <td> 
                                                                    <input type="text" class="edit-users-info text-pretty" value= "<?php echo $users_curr_hospitals[0]['user_firstname'] ?>" />
                                                                </td>
                                                                <td> 
                                                                    <input type="text" class="edit-users-info" value= "<?php echo $users_curr_hospitals[0]['user_middlename'] ?>" />
                                                                </td>
                                                                <td> 
                                                                    <input type="text" class="edit-users-info" value= "<?php echo $users_curr_hospitals[0]['username'] ?>"/>
                                                                </td>
                                                                <td> 
                                                                    <input type="text" class="edit-users-info" value= "<?php echo $users_curr_hospitals[0]['password'] ?>" />
                                                                </td>
                                                            <?php if($users_curr_hospitals[0]['user_isActive'] === 0) {?>
                                                                <td> Inactive</td>
                                                            <?php }else{ ?>
                                                                <td> Active</td>
                                                            <?php } ?>
                                                            <td>
                                                                <button type="button" class="edit-info-btn">Edit</button>
                                                                <button type="button" class="cancel-info-btn">Close</button>
                                                            </td>
                                                            <input class="hcode-edit-info" type="hidden" name="hcode-edit-info" value=<?php echo $users_curr_hospitals[0]['hospital_code'] ?> />
                                                        </tr>
                                                    <?php } ?>
                                                        
                                                </tbody>
                                            </table>
                                        </div>

                                        <i class="see-more-btn fa-regular fa-square-caret-down"></i>
                                    </td>
                                    <!-- <td class="w-[50px]"></td>  -->

                                    <td id="hospital-num-td">
                                        <div class="w-full h-full flex flex-col justify-center items-center">
                                            <label>Landline: <?php echo $data_sdn_hospitals[$i]['hospital_landline'] ?></label>
                                            <label>Mobile: <?php echo $data_sdn_hospitals[$i]['hospital_mobile'] ?></label>
                                            <label>Email Address: <?php echo $data_sdn_hospitals[$i]['hospital_email'] ?></label>
                                        </div>
                                    </td>
                                    <td id="hospital-dir-td"> <?php echo $data_sdn_hospitals[$i]['hospital_director'] ?></td>
                                    <td id="hospital-dir-mo-td"> <?php echo $data_sdn_hospitals[$i]['hospital_director_mobile'] ?></td>
                                    <td id="hospital-pp-td"> <?php echo $data_sdn_hospitals[$i]['hospital_point_person'] ?></td>
                                    <td id="hospital-pp-mo-td"> <?php echo $data_sdn_hospitals[$i]['hospital_point_person_mobile'] ?></td>
                                    <td id="hospital-action-td"> 
                                        <div class="cipher-div" style="<?php echo $style_action ?>">
                                            <input type="text" name="" class="cipher-input" placeholder="Cipher Key" />
                                            <button class="send-cipher btn btn-success" id="<?php echo $data_sdn_hospitals[$i]['hospital_code'] ?>">Send</button>
                                        </div>
                                    </td>
                                    
                                    <!-- end rendering - sakto hahaha gl gl  -->
                                </tr>
                            <?php } ?>
                            <!-- Add more rows as needed  -->
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- <div class="modal-footer">
                <button id="add-classification-btn" type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2 opacity-50 pointer-events-none" data-bs-dismiss="modal">Add</button>
            </div> -->
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal-prompt" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-div">
                        <i id="modal-icon" class="fa-solid fa-circle-check ml-2"></i>
                        <h5 id="modal-title-incoming" class="modal-title-incoming" id="exampleModalLabel">Successed</h5>
                    </div>
                </div>
                <div id="modal-body-incoming" class="modal-body-incoming ml-2">
                    Edit Successfully
                </div>
                <div class="modal-footer">
                    <button id="ok-modal-btn-incoming" type="button">OK</button>
                    <button id="yes-modal-btn-incoming" type="button">YES</button>
                </div>
            </div>
        </div>
    </div>

     <!-- Modal -->
     <div class="modal fade" id="myModal-success" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-div">
                    <i id="modal-icon" class="fa-solid fa-circle-check"></i>
                    <h5 id="modal-title-incoming" class="modal-title-incoming" id="exampleModalLabel">Successed</h5>
                </div>
            </div>
            <div id="modal-body-incoming-success" class="modal-body-incoming">
            </div>
            <div class="modal-footer">
                <button id="ok-modal-btn-incoming" type="button" data-bs-toggle="modal" data-bs-target="#myModal-success">OK</button>
            </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="../js/admin.js?v=<?php echo time(); ?>"></script>
</body>
</html>