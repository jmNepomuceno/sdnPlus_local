<?php 
    session_start();
    include('../database/connection2.php');
    date_default_timezone_set('Asia/Manila');
    
    if ($_SESSION['user_name'] === 'admin'){
        $user_name = 'Bataan General Hospital and Medical Center';
    }else{
        $user_name = $_SESSION['hospital_name'];
    }

    $webpage_date_traverse = date('Y-m-d H:i:s');
    $_SESSION['webpage_date_traverse'] = $webpage_date_traverse;

    $sql = "SELECT COUNT(*) FROM incoming_referrals WHERE status='Pending' AND refer_to=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['hospital_name']]);
    $incoming_num = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <?php require "../header_link.php" ?>
    <link rel="stylesheet" href="../css/history_log.css">
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

            <?php if($_SESSION["user_name"] == "admin") {?>
            <div id="history-log-btn" class="nav-drop-btns">
                <h2 class="nav-drop-btns-txt">History Log</h2>
            </div>
            <?php }?>

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
        <div class="main-sub-div">
            <div id="main-title-div">
                <h1>Account History Log</h1>
            </div>     

            
            <div class="search-div">
                <div class="">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Search by User">   
                </div>

                <div class="">
                    <h1>Activity Type</h1>
                    <select id="history-select" type="text" >
                        <option value="">All Logs</option>
                        <option value="login">Login</option>
                        <option value="register">Registered Patient</option>
                        <option value="incoming">Referral</option>
                        <option value="cancel">Cancelled Referral</option>
                    </select>
                </div>
            </div>

            <div class="icon-div">
                <h1>Date <i class="fa-solid fa-arrow-down-short-wide"></i></h1>
                <h1>Action <i class="fa-solid fa-arrow-down-short-wide"></i></h1>
                <h1>User name <i class="fa-solid fa-arrow-down-short-wide"></i></h1>
            </div> 

            <div class="history-container">
                <?php 
                    $sql = "SELECT * FROM sdn_users JOIN history_log ON sdn_users.username = history_log.username WHERE (sdn_users.role='admin' OR sdn_users.role='doctor_admin') ORDER BY history_log.date DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();   
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    // echo '<pre>'; print_r($data); echo '</pre>';

                    $temp_1 = "";
                    $temp_2 = "";   
                    $temp_3 = "";

                    for($i = 0; $i < count($data); $i++){

                        if($data[$i]['activity_type'] === 'user_login'){
                            $name = $data[$i]['user_lastname'] . ', ' . $data[$i]['user_firstname'] . ' ' . $data[$i]['user_middlename'] . '. ';
                            $originalDate = $data[$i]['user_lastLoggedIn'];
                            $currentDate = date('Y-m-d H:i:s');
                            $formattedDate = "";

                            $dateTime = new DateTime($data[$i]['date']);
                            $formattedDate = $dateTime->format('F j, Y g:ia');

                            $temp_1 = $formattedDate;
                            $temp_2 = "Online Status: " . $data[$i]['action'];
                            $temp_3 = $name;
                        }
                        else {
                            $name = $data[$i]['user_lastname'] . ', ' . $data[$i]['user_firstname'] . ' ' . $data[$i]['user_middlename'] . '. ';
                            $originalDate = $data[$i]['date'];
                            $currentDate = date('Y-m-d H:i:s');
                            $formattedDate = "";

                            $dateTime = new DateTime($originalDate);
                            $formattedDate = $dateTime->format('F j, Y g:ia');

                            $temp_1 = $formattedDate;
                            $temp_2 = $data[$i]['action'] . ' ' . $data[$i]['pat_name'];
                            $temp_3 = $name;
                        }

                        $style_color = "#ffffff";
                        $text_color = "#1f292e";
                        if($i % 2 == 1){
                            $style_color = "#d3dbde"; 
                            $text_color = "#ffffff";
                        }

                        echo '
                            <div class="history-div" style="background: '. $style_color .'">
                                <div>
                                    <i class="fa-regular fa-calendar-days"></i>
                                    <h3>'. $temp_1 .'</h3>
                                </div>
                
                                <div>
                                    <!-- <i class="fa-regular fa-calendar-days text-2xl "></i> -->
                                    <h3 class="text-base"> <span id="status-login">'. $temp_2 .'</span></h3>
                                </div>

                                <div>
                                    <h3> '. $temp_3 .' </h3>
                                    <i class="fa-solid fa-user text-2xl "></i>
                
                                </div>
                            </div>
                        ';
                    }
                ?>
            </div>
        </div>  
    </div>

    <div class="modal fade" id="myModal-prompt" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-div">
                    <i id="modal-icon" class="fa-solid fa-circle-check ml-2"></i>
                    <h5 id="modal-title-incoming" class="modal-title-incoming" id="exampleModalLabel">Successed</h5>
                </div>
                <!-- <button type="button" class="close text-3xl" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button> -->
            </div>
            <div id="modal-body">
                Edit Successfully
            </div>
            <div class="modal-footer">
                <button id="ok-modal-btn-incoming" type="button" data-bs-toggle="modal" data-bs-target="#myModal-prompt">OK</button>
                <button id="yes-modal-btn-incoming" type="button" data-bs-toggle="modal" data-bs-target="#myModal-prompt">OK</button>
            </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="../js/history_log.js?v=<?php echo time(); ?>"></script>
</body>
</html>