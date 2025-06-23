<?php
    include('database/connection2.php');
    include('./SDN/csrf/session.php');

    if ($_POST) {
        // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['_csrf_token']) {
        //     die("CSRF token verification failed");
        // }

        $_SESSION["process_timer"] = [];
        $sdn_username = $_POST['sdn_username'];
        $sdn_password = $_POST['sdn_password'];
        $timezone = new DateTimeZone('Asia/Manila');
        $currentDateTime = new DateTime("", $timezone);

        $final_date = $currentDateTime->format('Y/m/d H:i:s');
        $normal_date = $currentDateTime->format('Y-m-d H:i:s');

        try {
            // Check user role
            $stmt = $pdo->prepare('SELECT role FROM sdn_users WHERE username = ? AND password = ?');
            $stmt->execute([$sdn_username, $sdn_password]);
            $role_account = $stmt->fetch(PDO::FETCH_ASSOC);

            // Common session variables
            function setCommonSessions($role, $user, $hospital, $final_date) {
                $_SESSION['hospital_code'] = $hospital['hospital_code'];
                $_SESSION['hospital_name'] = $hospital['hospital_name'];
                $_SESSION['hospital_email'] = $hospital['hospital_email'] ?? '';
                $_SESSION['hospital_landline'] = $hospital['hospital_landline'] ?? '';
                $_SESSION['hospital_mobile'] = $hospital['hospital_mobile'] ?? '';

                $_SESSION['user_name'] = $user['username'];
                $_SESSION['user_password'] = $user['password'];
                $_SESSION['first_name'] = $user['user_firstname'];
                $_SESSION['last_name'] = $user['user_lastname'];
                $_SESSION['middle_name'] = $user['user_middlename'];
                $_SESSION['user_type'] = $role;

                $_SESSION['post_value_reload'] = 'false';
                $_SESSION["sub_what"] = "";
                $_SESSION['datatable_index'] = 0;
                $_SESSION['running_bool'] = false;
                $_SESSION['running_startTime'] = [];
                $_SESSION['running_timer'] = [];
                $_SESSION['running_hpercode'] = [];
                $_SESSION['running_index'] = [];
                $_SESSION['fifo_hpercode'] = "asdf";
                $_SESSION['login_time'] = $final_date;
                $_SESSION['current_content'] = "";
                $_SESSION['session_navigation'] = "";
                $_SESSION['webpage_date_traverse'] = "";
                $_SESSION['user_role'] = $role;

                if($role === 'admin' || $role === 'doctor_admin') { 
                    $_SESSION['mcc_passwords'] = [
                        "Lacsamana" => "123",
                        "Baltazar" => "1"
                    ];
                }

                if($role === 'admin') {
                    $_SESSION['user_name'] = 'admin042801';
                    $_SESSION['user_password'] = 'admin042801';
                    $_SESSION['last_name'] = 'Administrator';
                    $_SESSION['first_name'] = '';
                    $_SESSION['middle_name'] = '';
                }
            }
            
            if (!$role_account) {
                // echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                //     <script type="text/javascript">
                //         $(document).ready(function() {
                //             $("#modal-title").text("Warning")
                //             $("#modal-icon").addClass("fa-triangle-exclamation")
                //             $("#modal-icon").removeClass("fa-circle-check")
                //             $("#modal-body").text("Invalid username and password!")
                //             $("#ok-modal-btn").text("OK")
                //             $("#yes-modal-btn").css("display" , "none")
                //             $("#ok-modal-btn").css("margin-right" , "0")
                //             $("#myModal").modal("show");

                //             $(document).off("click", "#ok-modal-btn").on("click", "#ok-modal-btn", function(event) {
                //                 event.preventDefault();
                //                 $("#myModal").modal("hide");
                //             })
                //         });
                //     </script>';
                echo "<script>alert('Invalid username and password!');</script>";

            } else if ($role_account['role'] == 'rhu_account') {
                $stmt = $pdo->prepare('SELECT * FROM sdn_users WHERE username = ? AND password = ?');
                $stmt->execute([$sdn_username, $sdn_password]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare('SELECT * FROM sdn_hospital WHERE hospital_code = ?');
                $stmt->execute([$user_data['hospital_code']]);
                $hospital_data = $stmt->fetch(PDO::FETCH_ASSOC);

                setCommonSessions('rhu_account', $user_data, $hospital_data, $final_date);

                $stmt = $pdo->prepare("UPDATE sdn_users SET user_lastLoggedIn='online', user_isActive=1 WHERE username = :username AND password = :password");
                $stmt->execute(['username' => $user_data['username'], 'password' => $user_data['password']]);

                addHistoryLog($pdo, $user_data['username'], $hospital_data['hospital_code'], $normal_date, 'user_login', 'online');
                header('Location: ./SDN/Home.php');
            } else if ($role_account['role'] == 'admin') {
                $hospital = [
                    'hospital_code' => '1111',
                    'hospital_name' => 'Bataan General Hospital and Medical Center',
                    'hospital_landline' => '333-3333',
                    'hospital_mobile' => '3333-3333-333'
                ];

                setCommonSessions('admin', [
                    'username' => 'admin', 
                    'password' => 'admin', 
                    'user_firstname' => 'admin' ,
                    'user_lastname' => 'admin',
                    'user_middlename' => 'admin'
                ], $hospital, $final_date);
                addHistoryLog($pdo, 'admin', $hospital['hospital_code'], $normal_date, 'user_login', 'online');
                header('Location: ./SDN/Home.php');
            } elseif ($role_account['role'] == 'doctor_admin') {
                $stmt = $pdo->prepare('SELECT * FROM sdn_users WHERE username = ? AND password = ?');
                $stmt->execute([$sdn_username, $sdn_password]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

                $hospital = [
                    'hospital_code' => '1111',
                    'hospital_name' => 'Bataan General Hospital and Medical Center',
                    'hospital_landline' => '333-3333',
                    'hospital_mobile' => '3333-3333-333'
                ];

                setCommonSessions('doctor_admin', $user_data, $hospital, $final_date);

                $stmt = $pdo->prepare("UPDATE sdn_users SET user_lastLoggedIn='online', user_isActive=1 WHERE username = :username AND password = :password");
                $stmt->execute(['username' => $user_data['username'], 'password' => $user_data['password']]);

                addHistoryLog($pdo, $user_data['username'], $hospital['hospital_code'], $normal_date, 'user_login', 'online');
                header('Location: ./SDN/Home.php');
            }

        } catch (PDOException $e) {
            die("Database query failed: " . $e->getMessage());
        }
    }

    // Helper Functions
    function renderModal($title, $iconClass, $bodyText, $buttonText) {
        return '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script type="text/javascript">
                        jQuery(document).ready(function() {
                            jQuery("#modal-title").text("Warning")
                            jQuery("#modal-icon").addClass("fa-triangle-exclamation")
                            jQuery("#modal-icon").removeClass("fa-circle-check")
                            jQuery("#modal-body").text("Invalid username and password!")
                            jQuery("#ok-modal-btn").text("OK")
                            jQuery("#yes-modal-btn").css("display" , "none")
                            jQuery("#ok-modal-btn").css("margin-right" , "0")
                            jQuery("#myModal").modal("show");
        
                        });
                    </script>';
    }

    function addHistoryLog($pdo, $username, $hospital_code, $date, $activity_type, $action) {
        $sql = "INSERT INTO history_log (hpercode, hospital_code, date, activity_type, action, pat_name, username) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['', $hospital_code, $date, $activity_type, $action, '', $username]);
    }

    // $sql = "DELETE FROM hperson";
    // $stmt = $pdo->prepare($sql);
    // $stmt->execute();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Delivery Network</title>

    <?php require "./header_link.php" ?>
    <link rel="stylesheet" href="index.css" />
    <link rel="stylesheet" href="index_mediaq.css" />

    <style>
        .custom-box-shadow {
            box-shadow: rgb(38, 57, 77) 0px 20px 30px -10px;
            
        }

        canvas{ 
            display: block; vertical-align: bottom; 
        } /* ---- particles.js container ---- */ 

        #particles-js{ 
            position:absolute; width: 100%; height: 100%; background-color: #86A789; background-repeat: no-repeat; background-size: cover; background-position: 50% 50%; 
        } 
        /* ---- stats.js ---- */ 

        .js-count-particles{ 
            font-size: 1.1em; 
        } 

        #stats, .count-particles{ 
            -webkit-user-select: none; margin-top: 5px; margin-left: 5px; 
        } 

        #stats{ border-radius: 3px 3px 0 0; overflow: hidden; 
        } 

        .count-particles{ 
            border-radius: 0 0 3px 3px; 
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
        <!-- aesthetic hospital website background -->
        <div id="particles-js"></div> 
        <div class="count-particles"> <span class="js-count-particles">--</span> particles </div> <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script> 
        
    <div class="container">
    
        <div class="main-content">
            <!-- <h1 class="letter-border">BataanGHMC-HCPN Online Referral System</h1>
            <h2 class="letter-border-h2">(Service Delivery Network)</h2> -->
            <h1 class="letter-border">Service Delivery Network Plus (SDN+)</h1>

            <div class="glass-div">
                <h1 id="login-txt">Login</h1>
                <form action="index.php" method="POST">
                    <!-- here csrf -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['_csrf_token']; ?>">

                    <div id="username-div">
                        <i class="username-icon fa-solid fa-user"></i>
                        <input type="text" name="sdn_username" id="username-inp" placeholder="Username" required autocomplete="off">
                    </div>

                    <div id="password-div">
                        <i class="username-icon fa-solid fa-user"></i>
                        <input type="password" name="sdn_password" id="password-inp" placeholder="Password" required autocomplete="off">
                    </div>

                    <button id="login-btn">Sign In</button>
                </form>
                
                <div class="query-signin-div">
                    <span id="query-signin-txt">No account yet? <span id="sign-up-txt">Sign up</span></span>
                </div>
            </div>

            <?php include("./php/footer_php/footer.php") ?>
        </div>

        <div class="sub-content">
            <div class="sub-content-header-div">
                <i class="return fa-solid fa-arrow-left"></i>
                <div class="sub-content-header">BataanGHMC-HCPN Online Referral System</div>
            </div>

            <div class="sub-nav-btns">
                <button type="button" id="registration-btn" class="btn btn-primary">Registration</button>
                <button type="button" id="authorization-btn" class="btn btn-dark">Authorization</button>
            </div>

            <div class="sub-content-note">
                This is one-time registration ONLY. If you already have an account, no need to register again.
                <span style="color:red; margin-left:6%;">A one-time password and authorization key will be send to your registered email address.</span>
            </div>

            <form class="sub-content-registration-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['_csrf_token']; ?>">

                <div class="reg-form-divs" id="reg-form-divs-1">
                    <label for="sdn-hospital-name" class="reg-labels">Hospital Name<span>*</span></label>
                    <input id="sdn-hospital-name"  type="text" class="reg-inputs form-control" required autocomplete="off">
                </div>

                <div class="reg-form-divs" id="auth-form-divs-2">
                    <label for="sdn-hospital-code" class="reg-labels">Hospital Code<span>*</span></label>

                    <div style="width: 55%; display: flex; flex-direction: column; align-items: flex-start; margin-right: 5%;">
                        <input id="sdn-hospital-code" type="number" class="reg-inputs form-control" required autocomplete="off" style="width: 100%;">
                        <small style="color: #6c757d; margin-top: 4px;">
                            Don’t know your code? Click <a href="https://nhfr.doh.gov.ph/VActivefacilitiesList" target="_blank">here</a>.
                        </small>

                    </div>
                </div>



                
                <div class="reg-form-divs">
                    <label for="sdn-region-select" class="reg-labels">Address: Region<span>*</span></label>
                    <select id="sdn-region-select" class="reg-inputs form-control" name="region" required autocomplete="off" style="cursor:pointer;" onchange="getLocations('region' , 'sdn-region')">
                        <option value="" class="">Select</option>
                        <?php 
                            $stmt = $pdo->query('SELECT region_code, region_description from region');
                            while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
                                echo '<option value="' , $data['region_code'] , '" >' , $data['region_description'] , '</option>';
                            }                                        
                        ?>
                    </select>
                </div>

                <div class="reg-form-divs">
                    <label for="sdn-province-select" class="reg-labels">Address: Province<span>*</span></label>
                    <select id="sdn-province-select" class="reg-inputs form-control" name="province" required autocomplete="off" onchange="getLocations('province' , 'sdn-province')">
                        <option value="" class="">Select</option>
                    </select>
                </div>

                <div class="reg-form-divs">
                    <label for="sdn-city-select" class="reg-labels">Address: Municipality<span>*</span></label>
                    <select id="sdn-city-select" class="reg-inputs form-control" name="city" required autocomplete="off" onchange="getLocations('city', 'sdn-city')">
                        <option value="" class="">Select</option>
                    </select>
                </div>

                <div class="reg-form-divs">
                    <label for="sdn-brgy-select" class="reg-labels">Address: Barangay<span>*</span></label>
                    <select id="sdn-brgy-select" class="reg-inputs form-control" name="brgy" required autocomplete="off">
                        <option value="" class="">Select</option>
                    </select>
                </div>

                <div class="reg-form-divs">
                    <label for="sdn-zip-code" class="reg-labels">Zip Code<span>*</span></label>
                    <!-- <input id="sdn-zip-code" type="number" class="reg-inputs form-control" required autocomplete="off"> -->
                    <span id="sdn-zip-code" class="reg-inputs form-control"></span>
                </div>

                <div class="reg-form-divs">
                    <label for="sdn-email-address" class="reg-labels">Email Address<span>*</span></label>
                    <input id="sdn-email-address" type="email" class="reg-inputs form-control"  required autocomplete="off">
                </div> 

                <div class="reg-form-divs">
                    <label for="sdn-landline-no" class="reg-labels">Hospital Landline No.<span>*</span></label>
                    <input id="sdn-landline-no" type="text" class="reg-inputs form-control " required autocomplete="off" placeholder="999-9999">
                </div>

                <div class="reg-form-divs">
                    <label for="sdn-hospital-mobile-no" class="reg-labels">Hospital Mobile No.<span>*</span></label>
                    <input id="sdn-hospital-mobile-no" type="text" class="reg-inputs form-control" required autocomplete="off" placeholder="9999-999-9999">
                </div>

                <div class="reg-form-divs">
                    <label for="sdn-hospital-director" class="reg-labels">Hospital Director<span>*</span></label>
                    <input id="sdn-hospital-director" type="text" class="reg-inputs form-control" required autocomplete="off" onkeydown="return /[a-zA-Z\s.,-]/i.test(event.key)">
                </div>

                <div class="reg-form-divs">
                    <label for="sdn-hospital-director-mobile-no" class="reg-labels">Hospital Director Mobile No.<span>*</span></label>
                    <input id="sdn-hospital-director-mobile-no" type="text" class="reg-inputs form-control" required autocomplete="off" placeholder="9999-999-9999">
                </div>

                <div class="reg-form-divs">
                    <label for="sdn-point-person" class="reg-labels">Point Person<span>*</span></label>
                    <input id="sdn-point-person" type="text" class="reg-inputs form-control" required autocomplete="off" onkeydown="return /[a-zA-Z\s.,-]/i.test(event.key)">
                </div>

                <div class="reg-form-divs">
                    <label for="sdn-point-person-mobile-no" class="reg-labels">Point Person Mobile No.<span>*</span></label>
                    <input id="sdn-point-person-mobile-no" type="text" class="reg-inputs form-control" required autocomplete="off" placeholder="9999-999-9999">
                </div>

                <!-- <button id="register-confirm-btn" type="button" class="btn btn-success">Success</button> -->
                <div class="register-confirm-div">
                    <button id="register-confirm-btn" type="button" class="btn btn-success">Register</button>
                </div>
            </form>

            <form class="sub-content-authorization-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['_csrf_token']; ?>">
                            
                <div class="autho-form-divs">
                    <label for="sdn-autho-hospital-code-id" class="reg-labels">Hospital Code<span>*</span></label>
                    <input id="sdn-autho-hospital-code-id" type="number" class="reg-inputs form-control" autocomplete="off">
                </div>

                <div class="autho-form-divs">
                    <label for="sdn-autho-cipher-key-id" class="reg-labels">Cipher Key<span>*</span></label>
                    <input id="sdn-autho-cipher-key-id" type="text" class="reg-inputs form-control" autocomplete="off">
                </div>

                <div class="autho-form-divs">
                    <label for="sdn-autho-last-name-id" class="reg-labels">Last Name<span>*</span></label>
                    <input id="sdn-autho-last-name-id" type="text" class="reg-inputs form-control" autocomplete="off">
                </div>

                <div class="autho-form-divs">
                    <label for="sdn-autho-first-name-id" class="reg-labels">First Name<span>*</span></label>
                    <input id="sdn-autho-first-name-id" type="text" class="reg-inputs form-control" autocomplete="off">
                </div>

                <div class="autho-form-divs">
                    <label for="sdn-autho-middle-name-id" class="reg-labels">Middle Name<span>*</span></label>
                    <input id="sdn-autho-middle-name-id" type="text" class="reg-inputs form-control" autocomplete="off">
                </div>

                <div class="autho-form-divs">
                    <label for="sdn-autho-ext-name-id" class="reg-labels">Extension Name</label>
                    <input id="sdn-autho-ext-name-id" type="text" class="reg-inputs form-control" autocomplete="off">
                </div>

                <div class="autho-form-divs">
                    <label for="sdn-autho-username" class="reg-labels">Username<span>*</span></label>
                    <input id="sdn-autho-username" type="text" class="reg-inputs form-control" autocomplete="off">
                </div>

                <div class="autho-form-divs">
                    <label for="sdn-autho-password" class="reg-labels">Password<span>*</span></label>
                    <input id="sdn-autho-password" type="password" class="reg-inputs form-control" autocomplete="off">
                </div>

                <div class="autho-form-divs">
                    <label for="sdn-autho-confirm-password" class="reg-labels">Confirm Password<span>*</span></label>
                    <input id="sdn-autho-confirm-password" type="password" class="reg-inputs form-control" autocomplete="off">
                </div>

                <!-- <button id="register-confirm-btn" type="button" class="btn btn-success">Success</button> -->
                <div class="authorization-confirm-div">
                    <button id="authorization-confirm-btn" type="button" class="btn btn-success">Verify</button>
                </div>
            </form>
        </div>

        
        <div class="sdn-loading-div">
            <div id="sdn-loading-div-2">
                <h3></h3>
            </div>
            
            <h3>SENDING OTP TO YOUR EMAIL...</h3>
            <div class="loader"></div>
        </div>

        <div class="otp-modal-div">
            <div id="email-sent-div">
                <h3>OTP <span>Email sent</span> <span id="new-otp-sent-txt"> - New OTP Email sent</span></h3>
                <button id="sdn-otp-modal-btn-close" class="sdn-otp-modal-btn-close">X</button>
            </div>
            
            <div id="input-otp-div">
                <h3>INPUT THE OTP</h3>
                <h5>Note: If the OTP does not appear in your inbox, please check your Spam folder</h5>
            </div>

            <div id="otp-inputs-div">
                <div class="otp-inputs">
                    <input type="text" id="otp-input-1" placeholder="-" maxlength="1">
                </div>
                <div class="otp-inputs">
                    <input type="text" id="otp-input-2" placeholder="-" maxlength="1">
                </div>
                <div class="otp-inputs">
                    <input type="text" id="otp-input-3" placeholder="-" maxlength="1">
                </div>
                <div class="otp-inputs">
                    <input type="text" id="otp-input-4" placeholder="-" maxlength="1">
                </div>
                <div class="otp-inputs">
                    <input type="text" id="otp-input-5" placeholder="-" maxlength="1">
                </div>
                <div class="otp-inputs">
                    <input type="text" id="otp-input-6" placeholder="-" maxlength="1">
                </div>
            </div>

            <div id="resend-otp-div">
                <button id="resend-otp-btn">Resend OTP</button>
                <span id="resend-otp-timer">00:00</span>
            </div>

            <div id="otp-verify-div">
                <button id="otp-verify-btn" class="otp-verify-btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-4 h-full rounded">Verify</button>
            </div>
            
        </div>

        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title-div">
                            <i id="modal-icon" class="fa-solid fa-circle-check"></i>
                            <h5 id="modal-title" class="modal-title" id="exampleModalLabel">Verification</h5>

                        </div>
                    </div>
                    <div id="modal-body" class="modal-body">
                        Verified OTP 
                    </div>
                    <div class="modal-footer">
                        <button id="ok-modal-btn" type="button" data-bs-dismiss="modal">OK</button>
                        <button id="yes-modal-btn" type="button" data-bs-dismiss="modal">Yes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="overlay"></div>
    <i id="tutorial-btn" class="fa-regular fa-circle-question"></i>

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
                        <div id="tutorial-carousel" class="carousel slide">
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#tutorial-carousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                <button type="button" data-bs-target="#tutorial-carousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                <button type="button" data-bs-target="#tutorial-carousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                                <button type="button" data-bs-target="#tutorial-carousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
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
    </div>

    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>   
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script> 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.1/js/bootstrap.bundle.min.js"></script>

    <script src="./index.js?v=<?php echo time(); ?>"></script>
    <script src="./js/location.js?v=<?php echo time(); ?>"></script>
    <script src="./js/sdn_reg.js?v=<?php echo time(); ?>"></script>
    <script src="./js/verify_otp.js?v=<?php echo time(); ?>"></script>
    <script src="./js/sdn_autho.js?v=<?php echo time(); ?>"></script>
    <script src="./js/resend_otp.js?v=<?php echo time(); ?>"></script>
    <script src="./js/closed_otp.js?v=<?php echo time(); ?>"></script>

    <script src="https://rawgit.com/mrdoob/stats.js/r16/build/stats.min.js"></script>
    <script type="text/javascript">
        particlesJS("particles-js", {
            "particles": {
                "number": {"value": 6, "density": {"enable": true, "value_area": 800}},
                "color": {"value": "#4F6F52"},
                "shape": {
                    "type": "polygon",
                    "stroke": {"width": 0, "color": "#000"},
                    "polygon": {"nb_sides": 5},
                    "image": {"src": "img/github.svg", "width": 100, "height": 100}
                },
                "opacity": {
                    "value": 0.3,
                    "random": true,
                    "anim": {"enable": false, "speed": 1, "opacity_min": 0.1, "sync": false}
                },
                "size": {
                    "value": 160,
                    "random": false,
                    "anim": {"enable": true, "speed": 10, "size_min": 40, "sync": false}
                },
                "line_linked": {"enable": false, "distance": 200, "color": "#ffffff", "opacity": 1, "width": 2},
                "move": {
                    "enable": true,
                    "speed": 8,
                    "direction": "none",
                    "random": false,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false,
                    "attract": {"enable": false, "rotateX": 600, "rotateY": 1200}
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {"onhover": {"enable": false, "mode": "grab"}, "onclick": {"enable": false, "mode": "push"}, "resize": true},
                "modes": {
                    "grab": {"distance": 400, "line_linked": {"opacity": 1}},
                    "bubble": {"distance": 400, "size": 40, "duration": 2, "opacity": 8, "speed": 3},
                    "repulse": {"distance": 200, "duration": 0.4},
                    "push": {"particles_nb": 4},
                    "remove": {"particles_nb": 2}
                }
            },
            "retina_detect": true
        });

        var count_particles, stats, update;
        
        // Correct initialization of Stats object
        stats = new Stats(); 
        stats.setMode(0);
        stats.domElement.style.position = 'absolute';
        stats.domElement.style.left = '0px';
        stats.domElement.style.top = '0px';
        document.body.appendChild(stats.domElement);
        document.body.removeChild(stats.domElement);
        count_particles = document.querySelector('.js-count-particles');

        update = function() {
            stats.begin();
            stats.end();
            if (window.pJSDom[0].pJS.particles && window.pJSDom[0].pJS.particles.array) {
                count_particles.innerText = window.pJSDom[0].pJS.particles.array.length;
            }
            requestAnimationFrame(update);
        };

        requestAnimationFrame(update);
    </script>

</body>
</html>