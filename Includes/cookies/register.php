 <?php
    include("cookies.php");
      
?>

<body class="registration-form">
    <nav class="navbar navbar-expand-lg navbar-dark bg-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="asset/img/bghmc-logo.png" alt="BataanGHMC" height="40">
                BataanGHMC
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" id="home" href="index.php">HOME</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="content">
        <h1 class="title-content">Applicant Registration Form</h1>
    </div>

    <div class="container my-2">
        <div class="personal-info-section">
            <div class="row" style="font-size: 14px; font-weight:500 !important;">
                <label>NOTE: Input fields with asterisk (<span>*</span>) are mandatory.</label>
            </div>
            <form id="personal-info-form" method="POST" novalidate>
                <input type="hidden" name="applicationType" value="entrylevel">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['_csrf_token']; ?>">
                <?php                          
                    $muncity_arr = fetch_municipality();
                    function write_muncity($arr)
                    {
                        foreach ($arr as $muncity)
                        {
                            echo "<option value=\"".$muncity["code"]."\">".$muncity["name"]."</option>";
                        }
                    }                    
                ?>

                <div class="row">
                    <div class="col">
                        <label for="applicationType" class="col-sm-12 col-form-label col-form-label-sm" 
                            style="font-size: 16px; font-weight: 600 !important;">
                            Select Application Type:<span>*</span>
                        </label>
                        <div class="col">
                            <div class="form-check" style="margin-top: 10px; margin-bottom: 10px;">
                                <input class="form-check-input" type="radio" id="entrylevel" name="applicationType" value="entrylevel" checked>
                                <label class="form-check-label" for="entrylevel">Entry Level (New in Government Service)</label>
                            </div>
                            <div class="form-check" style="margin-bottom: 10px;">
                                <input class="form-check-input" type="radio" id="promotion" name="applicationType" value="promotion">
                                <label class="form-check-label" for="promotion">Promotion (For Existing Government Employees Applying for Higher Positions)</label>
                            </div>
                            <div class="form-check" style="margin-bottom: 10px;">
                                <input class="form-check-input" type="radio" id="transfer" name="applicationType" value="transfer">
                                <label class="form-check-label" for="transfer">Transfer (From other Government Agencies)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="fileUploadContainer">                    
                </div>

                <hr>

                <div class="row">
                    <div class="col">
                        <h2 style="font-size: 1rem; font-weight: 600;">NOTE: Documents to be uploaded must be Certified True Copy.</h2>
                    </div>
                </div>


                <div class="row">
                    <h5 style="font-size: 14px; font-weight: bold">File Upload Instructions:</h5>
                    <p class="upload-paragraph">1: Only .pdf, .jpeg, .png file types are acceptable.</p>
                    <p class="upload-paragraph">2: Name your file according to the content of the documents (ex. resume.pdf,  nbi.jpg, eligibility.png).</p>             
                    <p class="upload-paragraph">3: Each document file size limit is 2MB only.</p>
                </div>

                <hr>

                <div class="row">
                    <div class="col">
                        <h2 style="font-size: 1rem; font-weight: 600;">Personal Detail and Permanent Address:</h2>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label for="lastname" class="col-sm-12 col-form-label col-form-label-sm">Last Name:<span>*</span></label>
                        <input type="text" name="lastname" id="lastname" class="form-control" oninput="formatToUppercase(this)" 
                            required autocomplete="off">            
                        <span class="validation-message"></span>                      
                    </div>

                    <div class="col">
                        <label for="firstname" class="col-sm-12 col-form-label col-form-label-sm">First Name:<span>*</span></label>
                        <input type="text" name="firstname" id="firstname" class="form-control" oninput="formatToUppercase(this)" 
                            required autocomplete="off">
                        <span class="validation-message"></span>
                    </div>
                    <div class="col">
                        <label for="middlename" class="col-sm-12 col-form-label col-form-label-sm">Middle Name:<span>*</span></label>
                        <input type="text" name="middlename" id="middlename" class="form-control" oninput="formatToUppercase(this)" 
                            required autocomplete="off">
                        <span class="validation-message"></span>
                    </div>
                    <div class="col">
                        <label for="extension" class="col-sm-12 col-form-label col-form-label-sm">Extension (Jr., Sr., III, etc.):</label>
                        <input type="text" name="extension" id="extension" class="form-control" autocomplete="off">
                    </div>
                </div> 

                <div class="row">
                    <div class="col">
                        <label for="street" class="col-sm-12 col-form-label col-form-label-sm">House #/ Street:</label>
                        <input type="text" name="street" id="street" class="form-control" autocomplete="off">
                        <span class="validation-message"></span>
                    </div>

                    <div class="col">
                        <label for="city" class="col-sm-12 col-form-label col-form-label-sm">City / Municipality:<span>*</span></label>
                        <select name="city" id="city" onchange="load_current_address(this);" class="form-control" required autocomplete="off">
                            <option value="">Select City/Municipality</option>
                            <?php

                            write_muncity($muncity_arr);
                            
                            ?>
                        </select>
                        <span class="validation-message"></span>
                    </div>

                    <div class="col">
                        <label for="currentBarangay" class="col-sm-12 col-form-label col-form-label-sm">Barangay:<span>*</span></label>
                        <select type="text" name="currentBarangay" id="currentBarangay"
                            class="form-control" autocomplete="off">
                            <option value="" data-emr-province="" data-emr-city="" data-emr-region="">Select Barangay</option>
                        </select>
                        <input type="hidden" id="currentCityValue" name="currentCityValue" />
                        <input type="hidden" id="currentProvinceValue" name="currentProvinceValue" />
                        <input type="hidden" id="currentRegionValue" name="currentRegionValue" />
                        <span class="validation-message"></span>
                    </div>
                </div>                        

                <div class="row">
                    <div class="col">
                        <label for="region" class="col-sm-12 col-form-label col-form-label-sm">Region:</label>
                        <input type="text" name="currentRegion" id="currentRegion" onchange="trigger_same_address()" 
                            class="readmode" placeholder="Region" readonly autocomplete="off"/>
                        <span class="validation-message"></span>
                    </div>

                    <div class="col">
                        <label for="province" class="col-sm-12 col-form-label col-form-label-sm">Province:</label>
                        <input type="text" name="currentProvince" id="currentProvince" onchange="trigger_same_address()"
                            class="readmode" placeholder="Province" readonly autocomplete="off">
                        <span class="validation-message"></span>    
                    </div>
                    <div class="col">
                        <label for="zipcode" class="col-sm-12 col-form-label col-form-label-sm">Zip Code:</label>
                        <input type="text" name="currentZipcode" id="currentZipcode" onchange="trigger_same_address()" 
                            class="readmode" placeholder="Zip code" readonly autocomplete="off">
                        <span class="validation-message"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label for="pwd" class="col-sm-12 col-form-label col-form-label-sm">Person with Diverse Abilities:</label>
                        <div class="select-wrapper">
                            <select name="pwd" id="pwd" class="form-control" autocomplete="off">
                                <option value="">Select Disability Type</option>
                                <option value="mobility disabilities">Mobility Disabilities</option>
                                <option value="vision impairments">Vision Impairments</option>
                                <option value="hearing impairments">Hearing Impairments</option>
                                <option value="cognitive disabilities">Cognitive/Intellectual Disabilities</option>
                                <option value="n/a">N/A</option>
                            </select>
                            <span class="chevron"></span>
                        </div>
                        <span class="validation-message"></span>
                    </div>

                    <div class="col">
                        <label for="tribe" class="col-sm-12 col-form-label col-form-label-sm">If Indigenous:</label>
                        <div class="select-wrapper">
                            <select name="tribe" id="tribe" class="form-control" autocomplete="off">
                                <option value="" selected>Select Indigenous Group</option>
                                <option value="aetas">Aetas</option>
                                <option value="igorot">Igorot</option>
                                <option value="illongots">Illongots</option>
                                <option value="isnag">Isnag</option>
                                <option value="isneg">Isneg</option>
                                <option value="kalinga">Ibaloi</option>
                                <option value="kalinga">Kalinga</option>
                                <option value="kalinga">Kankanaey</option>
                                <option value="n/a">N/A</option>
                            </select>
                            <span class="chevron"></span>
                        </div>
                        <span class="validation-message"></span>
                    </div>

                </div>

                <div class="row">
                    <div class="col">
                        <label for="birthday" class="col-sm-12 col-form-label col-form-label-sm">Birthday:<span>*</span></label>
                        <input type="date" name="birthday" id="birthday" class="form-control" required autocomplete="off">
                        <span class="validation-message"></span>
                    </div>  

                    <div class="col">
                        <label for="sex" class="col-sm-12 col-form-label col-form-label-sm">Gender Identity:<span>*</span></label>
                        <div class="select-wrapper">
                            <select name="sex" id="sex" class="form-control" required autocomplete="off">
                                <option value="">Select sex</option>
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                                <option value="Transgender">Transgender</option>
                                <option value="Non-binary">Non-binary</option>
                            </select>
                            <span class="chevron"></span>
                        </div>
                        <span class="validation-message"></span>
                    </div>

                    <div class="col">
                        <label for="status" class="col-sm-12 col-form-label col-form-label-sm">Civil Status:<span>*</span></label>
                        <div class="select-wrapper">
                            <select name="status" id="status" class="form-control" required autocomplete="off">
                                <option value="">Select Status</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                                <option value="separated">Separated</option>
                                <option value="widow">Widow/Widower</option>
                                <option value="n/a">N/A</option>
                            </select>
                            <span class="chevron"></span>
                        </div>
                        <span class="validation-message"></span>
                    </div>
                    
                    <div class="col">
                        <label for="nationality" class="col-sm-12 col-form-label col-form-label-sm">Nationality:<span>*</span></label>
                        <div class="select-wrapper">
                            <select name="nationality" id="nationality" class="form-control" required autocomplete="off">
                                <option value="">Select Nationality</option>
                                <option value="608">Philippine, Filipino</option>
                                    <?php

                                        $sql_nat = "SELECT * FROM dbo.nationality ORDER BY nationality";

                                        $stmt_nat = execute_query($sql_nat, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

                                        while ($row = $stmt_nat->fetch(PDO::FETCH_ASSOC))
                                        {
                                            echo "<option value=\"".$row["nationality_code"]."\">".$row["nationality"]."</option>";
                                        }
                                    ?>
                            </select>
                            <span class="chevron"></span>
                        </div>
                        <span class="validation-message"></span>
                    </div>
                    
                </div>

                <div class="row">
                    <div class="col">
                        <label for="bloodtype" class="col-sm-12 col-form-label col-form-label-sm">Blood Type:<span>*</span></label>
                        <div class="select-wrapper">
                            <select name="bloodtype" id="bloodtype" class="form-control" required autocomplete="off">
                                <option value="">Select Type</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="AB">AB</option>
                                <option value="0">O</option>
                            </select>
                            <span class="chevron"></span>
                        </div>
                        <span class="validation-message"></span>
                    </div>

                    <div class="col">
                        <label for="primary-no" class="col-sm-12 col-form-label col-form-label-sm">Primary Contact No.:<span>*</span></label>
                        <input type="text" name="primary-no" id="primary-no" pattern="[0-9]{4}-[0-9]{3}-[0-9]{4}" maxlength="13" 
                            class="form-control" placeholder="9999-999-9999" required autocomplete="off">
                        <span class="validation-message"></span>
                    </div>
                    <div class="col">
                        <label for="secondary-no" class="col-sm-12 col-form-label col-form-label-sm">Secondary Contact No.(if available):</label>
                        <input type="text" name="secondary-no" id="secondary-no" pattern="[0-9]{4}-[0-9]{3}-[0-9]{4}" maxlength="13" 
                            class="form-control" placeholder="9999-999-9999" autocomplete="off"> 
                        <span class="validation-message"></span>
                    </div>
                    <div class="col">
                        <label for="email" class="col-sm-12 col-form-label col-form-label-sm">Email:<span>*</span></label>
                        <input type="email" name="email" id="email" class="form-control" required autocomplete="off">
                        <span class="validation-message"></span>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col">
                        <h2 style="font-size: 1rem; font-weight: 600;">Education, Eligibility, Work Experience and Skills Training:</h2>
                    </div>
                </div> 

                <div class="row">
                    <div class="col">
                        <label for="education" class="col-sm-12 col-form-label col-form-label-sm">Education:<span>*</span></label>
                        <div class="select-wrapper">
                            <select name="education" id="education" class="form-control" required autocomplete="off">
                                <option value="">Select Education</option>
                                <option value="elementary">Elementary Graduate</option>
                                <option value="highschool">High School Graduate</option>
                                <option value="vocational">Vocational Graduate</option>
                                <option value="college-level">College Level</option>
                                <option value="college-graduate">College Graduate</option>
                                <option value="post-graduate">Post Graduate</option>
                            </select>
                            <span class="chevron"></span>
                        </div>
                        <span class="validation-message"></span>
                    </div>

                    <div class="col">
                        <label for="eligible" class="col-sm-12 col-form-label col-form-label-sm">Eligibility:<span>*</span></label>
                        <div class="select-wrapper">
                            <select name="eligible" id="eligible" class="form-control" required autocomplete="off">
                                    <option value="">Select Eligibility</option>
                                    <option value="CSC Professional">Career Service Professional</option>
                                    <option value="CSC Sub-Professional">Career Service Sub-Professional</option>
                                    <option value="NC COC">National Certificate (NC)/COC</option>
                                    <option value="PRC">PRC</option>
                                </select>
                            <span class="chevron"></span>
                        </div>
                        <span class="validation-message"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label for="withWorkExperience" class="col-sm-12 col-form-label col-form-label-sm" style="font-weight: 500 !important;">
                            Add Relevant Work Experience:
                        </label>                        
                    </div>  
                </div>

                <div class="row"> 
                    <div class="col">
                        <label for="fromDateExp" class="col-sm-12 col-form-label col-form-label-sm">From:</label>
                        <input type="date" name="fromDateExp" id="fromDateExp" class="form-control" autocomplete="off">                        
                    </div>     
                    <div class="col">
                        <label for="toDateExp" class="col-sm-12 col-form-label col-form-label-sm">To:</label>
                        <input type="date" name="toDateExp" id="toDateExp" class="form-control" autocomplete="off">                        
                    </div>
                    <div class="col">
                        <label for="positionExp" class="col-sm-12 col-form-label col-form-label-sm">Position:</label>
                        <input type="text" name="positionExp" id="positionExp" class="form-control" autocomplete="off">                        
                    </div>       
                    <div class="col">
                        <label for="companyExp" class="col-sm-12 col-form-label col-form-label-sm">Company Name:</label>
                        <input type="text" name="companyExp" id="companyExp" class="form-control" autocomplete="off">                        
                    </div>     
                    <div class="col">
                        <label for="statusExp" class="col-sm-12 col-form-label col-form-label-sm">Appointment Status:</label>
                        <div class="select-wrapper">
                            <select name="statusExp" id="statusExp" class="form-control" autocomplete="off">
                                <option value="">Select Status</option>
                                <option value="permanent">Permanent</option>
                                <option value="job order">Job Order</option>
                                <option value="COS">Contract of Service</option>
                                <option value="temporary">Temporary</option>
                            </select>
                            <span class="chevron"></span>
                        </div>                        
                    </div>   
                    <div class="col">
                        <label for="govtExp" class="col-sm-12 col-form-label col-form-label-sm">Government Service:</label>
                        <div class="select-wrapper">
                            <select name="govtExp" id="govtExp" class="form-control" autocomplete="off">
                                <option value="">Select</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                            <span class="chevron"></span>
                        </div>                        
                    </div>                                                                                                                                      
                </div>

                <div class="row">
                    <div class="col">
                        <label for="withSkillsTraining" class="col-sm-12 col-form-label col-form-label-sm" style="font-weight: 500 !important;">
                            Add Relevant Skills Training:
                        </label>                        
                    </div>  
                </div>

                <div class="row">
                    <div class="col">
                        <label for="trainingName" class="col-sm-12 col-form-label col-form-label-sm">Name of Training:</label>
                        <input type="text" name="trainingName" id="trainingName" class="form-control" autocomplete="off">
                    </div> 
                    <div class="col">
                        <label for="fromDateTrain" class="col-sm-12 col-form-label col-form-label-sm">From:</label>
                        <input type="date" name="fromDateTrain" id="fromDateTrain" class="form-control" autocomplete="off">
                    </div>     
                    <div class="col">
                        <label for="toDateTrain" class="col-sm-12 col-form-label col-form-label-sm">To:</label>
                        <input type="date" name="toDateTrain" id="toDateTrain" class="form-control" autocomplete="off">
                    </div>
                    <div class="col">
                        <label for="trainingHours" class="col-sm-12 col-form-label col-form-label-sm">Training Hours:</label>
                        <input type="number" name="trainingHours" id="trainingHours" min="1" max="999" class="form-control" autocomplete="off">
                    </div> 
                    <div class="col">
                        <label for="conductedBy" class="col-sm-12 col-form-label col-form-label-sm">Conducted By:</label>
                        <input type="text" name="conductedBy" id="conductedBy" class="form-control" autocomplete="off">
                    </div> 
                </div>

                <div class="row">
                    <h5 style="font-size: 14px; font-weight: bold; padding-top: 20px;">NOTE:</h5>
                    <p class="upload-paragraph">
                        In case the OTP Form Validation was closed or interrupted due to internet connection or
                        any technical issues, click this link <a href="" id="registerLink">Re-verify OTP</a>
                    </p>
                </div>     

                <button type="button" name="submit-btn" id="submit-btn" class="btn btn-success btn-form-success">Submit</button>

                <div class="row"></div>
            </form>
        </div>

        <div class="center-loader" style="display: none;">
            <div class="ring">
                <span class="spin-label">Submitting</span>
            </div>
        </div>


        <!-- OTP Form Modal-->
        <form action="" id="email-verification" method="POST" class="form">
            <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['_csrf_token']; ?>">
            <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="otpModalLabel">OTP Email Verification</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body text-center">
                            <p style="text-align: justify;">A One-Time-Password verification has been sent to your registered email.</p>
                            <p style="text-align: justify;">Please check your email, copy and paste the OTP in the box provided below.</p>
                            <p style="text-align: justify;">In case you did not receive it yet, click Resend OTP.</p>
                            <div class="col">
                                <label for="otp" class="col-sm-12 col-form-label col-form-label-sm">One-Time Password<span>*</span></label>
                                <input type="text" name="otp" id="otp" pattern="[0-9]{6}" maxlength="6" class="form-control" required autocomplete="off">                                
                                <span class="validation-message"></span>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button id="resendOTP" name="resendOTP" type="button" class="btn btn-secondary btn-custom">Resend OTP</button>
                            <button id="verifyOTP" name="verifyOTP" type="submit" class="btn btn-primary btn-custom">Verify OTP</button>
                        </div>

                    </div>
                </div>
            </div>
        </form>

        <!-- Success Modal -->
        <div id="successModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabel">OTP Verification Success</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img src="asset/img/tick-check.png">
                        <p>Your email has been successfully verified.</p>
                        <p>Please check your email for your Registration Code.</p>   
                        <p>Thank you.</p>                       
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="asset/js/register.js"></script>
    <script src="asset/js/file-upload.js"></script>
    <script src="asset/js/otp-verification.js"></script>
    <script src="asset/js/form-validator.js"></script>   
    <script src="asset/js/form-application.js"></script>
    <script src="asset/js/app.js"></script>


    <script>
        $("#city").selectize();

        document.addEventListener("DOMContentLoaded", function() {
            var registerLink = document.getElementById("registerLink");
            var otpModal = new bootstrap.Modal(document.getElementById("otpModal"));

            registerLink.addEventListener("click", function(e) {
                e.preventDefault(); 
                otpModal.show(); 
            });
        });
    </script>
</body>