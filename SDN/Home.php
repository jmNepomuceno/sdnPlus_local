<?php
    session_start();
    include('../database/connection2.php');
    
    //if cache is cleared redirect to index page
    if (!isset($_SESSION['user_name']) || empty($_SESSION['user_name'])) {
        header("Location: ../index.php");
        exit();
    } else {
        if ($_SESSION['user_name'] === 'admin042801'){
            $user_name = 'Bataan General Hospital and Medical Center';
            $count_pending = isset($_SESSION['count_pending']) ? $_SESSION['count_pending'] : 0;
        }else{
            $user_name = $_SESSION['hospital_name'];
        }
    }

    $sql = "SELECT COUNT(*) FROM incoming_referrals WHERE status='Pending' AND refer_to=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['hospital_name']]);
    $incoming_num = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT COUNT(*) FROM incoming_referrals WHERE progress_timer!=null AND refer_to=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['hospital_name']]);
    $progress_timer_num = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // echo '<pre>'; print_r($progress_timer_num); echo '</pre>'; 
    // echo $_SESSION['running_bool'], gettype($_SESSION['running_bool']);

    $sql = "SELECT permission FROM sdn_users WHERE username=? AND password=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_name'] , $_SESSION['user_password']]);
    $permission_account = $stmt->fetch(PDO::FETCH_ASSOC);

    $permissions = json_decode($permission_account['permission'], true);

    // $sql = "SELECT patlast, patfirst, patmiddle, patbdate FROM hperson WHERE patlast = ? AND patfirst = ? AND patmiddle = ? AND patbdate = ?";
    // $stmt = $pdo->prepare($sql);
    // $stmt->execute(["TryDuplicate", "asdf", "TryDuplicate", "1991-01-01"]);
    // $duplicate = $stmt->fetch(PDO::FETCH_ASSOC);
    // echo '<pre>'; print_r($duplicate); echo '</pre>';
    // echo "asdf"; 
    // if($duplicate == null){
    //     echo "here";
    // }

    // echo $permissions['census'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDN</title>
    
    <?php require "../header_link.php" ?>

    <link rel="stylesheet" href="../css/main_style.css" />
    <link rel="stylesheet" href="../css/main_style_mediaq.css" />
    <style>
        .scrollbar-hidden {        
            scrollbar-width: none;            
            -webkit-scrollbar {
            display: none;
            }
        }

        .custom-modal-width {
            max-width: 80vw; /* Adjust the width as per your requirements */
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
<body>
    <input id="current-page-input" type="hidden" name="current-page-input" value="" />
    <input id="clicked-logout-input" type="hidden" name="clicked-logout-input" value="" />    

    <div id="main-div">
        <header class="header-div">
            <div class="side-bar-title">
                <h1 id="sdn-title-h1">Service Delivery Network Plus (SDN+)</h1>
                <div class="side-bar-mobile-btn">
                    <i id="navbar-icon" class="fa-solid fa-bars"></i>
                </div>
            </div>
            <div class="account-header-div">
                <i class="fas fa-rotate" id="update-div" title="Updates"></i>
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
                                <!-- b3b3b3 -->
                            </div>
                        </div>

                        <!-- <div class="w-[20px] h-full flex flex-col justify-center items-center">
                            <i class="fa-solid fa-caret-down text-white text-xs mt-2"></i>
                        </div> -->
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
                                echo '<h1 id="user_name-id">' . $user_name . ' | ' . $_SESSION["last_name"] . ', ' . $_SESSION['first_name'] . ' ' . $_SESSION['middle_name'] . '</h1>';

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
                <?php if($permissions['admin_function'] == 1){?>
                    <div id="admin-module-btn" class="nav-drop-btns">
                        <h2 id="admin-module-id" class="nav-drop-btns-txt">Admin</h2>
                    </div>
                <?php } ?>

                <?php if($_SESSION['user_role'] != 'rhu_account'){?>
                    <div id="dashboard-incoming-btn" class="nav-drop-btns">
                        <h2 class="nav-drop-btns-txt">Dashboard (Incoming)</h2>
                    </div>
                <?php } ?>

                <?php if($_SESSION['user_role'] != 'rhu_account'){?>
                    <div id="dashboard-outgoing-btn" class="nav-drop-btns">
                        <h2 class="nav-drop-btns-txt">Dashboard (Outgoing)</h2>
                    </div>
                <?php } ?>

                <?php if($permissions['history_log'] == 1){?>
                    <div id="history-log-btn" class="nav-drop-btns">
                        <h2 class="nav-drop-btns-txt">History Log</h2>
                    </div>
                <?php } ?>
                
                <?php if($permissions['admin_function'] == 1 || $permissions['setting'] == 1){?>
                    <div class="nav-drop-btns" id="setting-btn">
                        <h2 class="nav-drop-btns-txt">Settings</h2>
                    </div>
                <?php } ?>

                <div class="nav-drop-btns" id="help-btn">
                    <h2 class="nav-drop-btns-txt">Help</h2>
                </div>

                <?php if($_SESSION['user_name'] == 'mss' && $_SESSION['user_password'] == 'mss'){?>
                    <div class="nav-drop-btns" id="setting-mss-btn">
                        <h2 class="nav-drop-btns-txt">MSS Settings</h2>
                    </div>
                <?php } ?>

                <div class="nav-drop-btns" id="credit-btn">
                    <h2 class="nav-drop-btns-txt">Acknowledgments</h2>
                </div>


                <div class="nav-drop-btns">
                    <h2 id='logout-btn' class="nav-drop-btns-txt" data-bs-toggle="modal" data-bs-target="#myModal-main">Logout</h2>
                </div>
            </div>
        </div>

        <div class="aside-main-div"> 

            <aside id="side-bar-div">
                <div id="side-bar-title-bgh">
                    <img src="../assets/login_imgs/logo-hi-res.png" alt="logo-img">
                    <p id="bgh-name">Bataan General Hospital and Medical Center</p>
                </div>

                <div id="main-side-bar-1">
                    <div id="main-side-bar-1-subdiv">
                        <i class="fa-solid fa-hospital-user"></i>
                        <h3>Patient Registration</h3>
                    </div>

                    <div id="sub-side-bar-1">
                        <?php 
                            if($permissions['patient_registration'] == 1){
                                echo '<div id="patient-reg-form-sub-side-bar" class="side-bar-navs-class">
                                        <i class="fa-solid fa-hospital-user"></i>  
                                        <h3 id="pat-reg-form-h3">Patient Registration Form</h3>
                                    </div>';
                            }
                        ?>
                    </div>
                </div>

                <div id="main-side-bar-2">
                    <div id="main-side-bar-2-subdiv">
                        <i class="fa-solid fa-retweet"></i>
                        <h3>Online Referral </h3>
                    </div>

                    <div id="sub-side-bar-2">
                        <?php 
                            if($permissions['outgoing_referral'] == 1){
                                echo '<div id="outgoing-sub-div-id" class="side-bar-navs-class">
                                        <i class="fa-solid fa-inbox"></i>
                                        <h3 id="outgoing-h3">Outgoing</h3>
                                    </div>';
                            }

                            if($permissions['incoming_referral'] == 1){
                                echo '<div id="incoming-sub-div-id" class="side-bar-navs-class">
                                        <!-- <h3 class="m-16 text-white">Incoming</h3> -->
                                        <i class="fa-solid fa-inbox"></i>
                                        <h3 id="incoming-h3">Incoming</h3>
                                    </div>
                                ';
                            }

                            if(isset($permissions['census'])){
                                if($permissions['census'] == 1){
                                    echo '<div id="census-sub-side-bar" class="side-bar-navs-class">
                                            <i class="fa-solid fa-hospital-user"></i>  
                                            <h3 id="pat-reg-form-h3">Census Table</h3>
                                        </div>';
                                }
                            }

                            
                        ?>

                        <!-- bucas referral -->
                        <!-- <div id="bucasPending-sub-div-id">
                            <i class="fa-solid fa-inbox"></i>
                            <h3>BUCAS (Incoming)</h3>
                        </div> -->

                        <?php if($permissions['bucas_referral'] == 1){?>
                        <!-- bucas referral with badge -->
                        <div id="bucasPending-sub-div-id" class="side-bar-navs-class" >
                            <i class="fa-solid fa-inbox"></i>
                            <h3>BUCAS (Incoming)</h3>
                            <span id="badge" class="position-absolute top-80 start-80 translate-middle badge rounded-pill bg-danger" style="left:30px;">
                            <span style="font-size: 10px !important;"><?php echo $count_pending; ?></span>
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </div>
                        
                        <!-- bucas referral -->
                        <div id="bucasHistory-sub-div-id" class="side-bar-navs-class" >
                            <i class="fa-solid fa-inbox"></i>
                            <h3>BUCAS (History)</h3>
                        </div>
                        <?php } ?>

                       
                    </div>
                </div>
            </aside>


            <div id="container">
            
            </div>
            <!-- ADMIN MODULE -->
        
        </div>
        
    </div>

    <!-- Include the count pending script for bucas referral-->
    <!-- <?php include('../SDN/count_pending.php'); ?> -->

    <!-- Modal -->
    <div class="modal fade" id="myModal-main" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <!-- <div class="modal-dialog" role="document"> -->
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-div">
                        <i id="modal-icon" class="fa-solid fa-triangle-exclamation"></i>
                        <h5 id="modal-title-main" class="modal-title-main" id="exampleModalLabel">Warning</h5>
                    </div>
                </div>
                <!-- <div id="modal-body-main" class="modal-body-main"> -->
                <div id="modal-body" class="logout-modal">
                        Are you sure you want to logout?
                </div>
                <div class="modal-footer">
                    <button id="ok-modal-btn-main" type="button" data-bs-dismiss="modal">OK</button>
                    <button id="yes-modal-btn-main" type="button" data-bs-dismiss="modal">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="myModal-traverse" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <!-- <div class="modal-dialog" role="document"> -->
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-div">
                    <i id="modal-icon" class="fa-solid fa-triangle-exclamation"></i>
                    <h5 id="modal-title-main" class="modal-title-main" id="exampleModalLabel">Warning</h5>
                </div>
            </div>
            <!-- <div id="modal-body-main" class="modal-body-main"> -->
            <div id="modal-body" class="logout-modal">
                A referral has arrived. Please remain on the page.
            </div>
            <div class="modal-footer">
                <button id="ok-modal-btn-main" type="button" data-bs-dismiss="modal">OK</button>
            </div>
            </div>
        </div>
    </div>

    <!-- bucas referral modal --> 
    <div class="modal fade" id="bucasBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="bucasBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="bucasBackdropLabel">BUCAS MEDICAL RECORD SUMMARY</h1>
                </div>
                <div class="modal-body-bucas" style="max-height: 700px; font-size: 14px !important; overflow-y: auto;">

                </div>
                <div class="modal-footer">
                    <button type="button" id="submit-referral-btn" class="btn btn-danger" onclick="">SUBMIT</button>
                    <button type="button" id="searchBtn" class="btn btn-secondary searchBtn" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php 
        require "../php/modals_php/credit_modal.php";
    ?>

    <?php 
        require "../php/modals_php/update_modal.php";
        require "../php/modals_php/mss_setting.php";
    ?>

    <div id="overlay"></div>

    <div class="modal fade" id="tutorialModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered custom-modal-width" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-div">
                        <h5 id="modal-title" class="modal-title" id="exampleModalLabel">Tutorial</h5>

                        <button type="button" data-bs-dismiss="modal" aria-label="Close" style="color:white">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div id="modal-body" class="modal-body">
                    <div class="tutorial-mods-div">
                        <button id="pat-mod">Patient Registration</button>
                        <button id="ref-mod">Incoming Referrals</button>
                    </div>
                    <div id="tutorial-carousel" class="carousel slide">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#tutorial-carousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#tutorial-carousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#tutorial-carousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                        </div>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="../assets/tutorial_images/pat_reg_imgs/pat_reg.jpg" class="d-block w-100" alt="image">
                            </div>
                            <div class="carousel-item">
                                <img src="../assets/tutorial_images/pat_reg_imgs/search_pat.jpg" class="d-block w-100" alt="image">
                            </div>
                            <div class="carousel-item">
                                <img src="../assets/tutorial_images/referral_imgs/referral.jpg" class="d-block w-100" alt="image">
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

    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->
    <!-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script> -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>    
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>


    <script src="../js/main_style.js?v=<?php echo time(); ?>"></script>
    <script src="../js/location.js?v=<?php echo time(); ?>"></script>

    <script>
        var running_bool = <?php echo ($_SESSION['running_bool']) === "true" ? "true" : "false"; ?>;
        var user_role = "<?php echo $_SESSION['user_role']; ?>";
        // bucas referral badge count pending
        $(document).ready(function() {
            // 
            function updateCountPending() {
                $.ajax({
                    url: 'count_pending.php',
                    method: 'GET',
                    success: function(response) {
                        var _json = JSON.parse(response);
                        $('#badge').text(_json.count_pending);
                    },
                    error: function(xhr, status, error) {
                        console.log('Error fetching count pending:', error);
                    }
                });
            }

            updateCountPending();

            setInterval(function() {
                updateCountPending();
            }, 60000);

        });

    </script>



    <!-- <script src="../js/patient_register_form2.js?v=<?php echo time(); ?>"></script>
    
    <script src="./js/incoming_form_2.js?v=<?php echo time(); ?>"></script> -->
    <!-- <script src="./js/fetch_interval.js?v=<?php echo time(); ?>"></script> -->

    <script>
        var running_val = <?php echo json_encode(floatval($_SESSION['running_timer'])); ?>;
        
    </script>
</body>
</html>