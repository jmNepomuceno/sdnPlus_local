<?php
    // ini_set('session.save_path', 'C:/Web/eSDN/session_path');
    // echo __DIR__ . "/session_path"; 

    
    ini_set('session.gc_probability', 0);
    session_start();
    include('../database/connection2.php');
    date_default_timezone_set('Asia/Manila');

    $currentDateTime = date('Y-m-d H:i:s');

   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <!-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> -->

     <?php require "../header_link.php" ?>
    <link rel="stylesheet" href="../css/incoming_form.css">
</head>
<body>

    <div class="incoming-container">
        <h1 id="content-title">Census Referral Patients</h1>
        <div class="search-main-div">
            <div class="refer-no-div">
                <label for="incoming-referral-no-search">Referral No.</label>
                <input id="incoming-referral-no-search" type="textbox">
            </div>
        
            <div class="lname-search-div">
                <label for="incoming-last-name-search">Last Name</label>
                <input id="incoming-last-name-search" type="textbox">
            </div>

            <div class="fname-search-div">
                <label  for="incoming-first-name-search">First Name</label>
                <input id="incoming-first-name-search" type="textbox">
            </div>

            <div class="mname-search-div">
                <label  for="incoming-middle-name-search">Middle Name</label>
                <input id="incoming-middle-name-search" type="textbox">
            </div>

            <div class="caseType-search-div">
                <label for="incoming-type-select">Case Type</label>
                <select id='incoming-type-select'>
                    <?php 
                        $stmt = $pdo->prepare('SELECT classifications FROM classifications');
                        $stmt->execute();

                        echo '<option value=""> None </option>';
                        while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
                            echo '<option value="' , $data['classifications'] , '">' , $data['classifications'] , '</option>';
                        } 
                    ?>
                </select>
            </div>


            <div class="agency-search-div">
                <label id="agency-search" for="incoming-agency-select">Agency</label>
                <select id='incoming-agency-select'>
                   <?php 
                    $stmt = $pdo->prepare('SELECT hospital_name FROM sdn_hospital');
                    $stmt->execute();
            
                    echo '<option value=""> None </option>';
                    while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
                        echo '<option value="' , $data['hospital_name'] , '">' , $data['hospital_name'] , '</option>';
                    } 
                   ?>
                </select>
            </div>

            <div class="startDate-search-div">
                <label for="incoming-startDate-search">Start Date</label>
                <input id="incoming-startDate-search" type="date" value="">
            </div>

            <div class="endDate-search-div">
                <label for="incoming-endDate-search">End Date</label>
                <input id="incoming-endDate-search" type="date" value="">
            </div>

            <div class="tat-search-div">
                <label for="incoming-tat-search">Turnaround Time Filter</label>
                <select id="incoming-tat-select">
                    <option value="">Select</option>

                    <option value="tat-green">≥ 15 minutes (greater than or equal to 15)</option>
                    <option value="tat-red">&lt;  15 minutes (less than 15)</option>
                    
                </select>
            </div>

             <div class="sensitive-search-div">
                <label for="incoming-sensitive-select">Sensitive Case</label>
                <select id="incoming-sensitive-select">
                    <option value="">Select</option>
                    <!-- <option value="sensitive-all">All</option> -->
                    <option value="true">True</option>
                    <option value="false">False</option>
                    
                </select>
            </div>


            <div class="status-search-div">
                <label for="incoming-status-select">Status</label>
                <select id='incoming-status-select'>
                    <option value="default">Select</option>
                    <option value="Pending">Pending</option>
                    <option value="All"> All</option>
                    <option value="On-Process"> On-Process</option>
                    <option value="Deferred"> Deferred</option>
                    <option value="Approved"> Approved</option>
                    <option value="Cancelled"> Cancelled</option>
                    <option value="Arrived"> Arrived</option>
                    <!-- <option value="Checked"> Checked</option>
                    <option value="Admitted"> Admitted</option>
                    <option value="Discharged"> Discharged</option>
                    <option value="For follow"> For follow up</option>
                    <option value="Referred"> Referred Back</option> -->
                </select>
            </div>

            <div class="search-clear-btns-div">
                <button id='incoming-clear-search-btn'>Clear</button>
                <button id='incoming-search-btn'>Search</button>
            </div>
        </div>
        <section class="incoming-table">

            <table id="myDataTable" class="display table table-bordered custom-search-modal" style="width: 100%; border-spacing: -1px;">
                <thead>
                    <tr class="text-center">
                        <th id="refer-no">Reference No. </th>
                        <th>Patient's Name</th>
                        <th>Type</th>
                        <th>Agency</th>
                        <th>Date/Time</th>
                        <th>Response Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="incoming-tbody">
                <div class="loader" style="display: none;"></div>
                
                </tbody>
            </table>

            <?php include("../php/footer_php/footer.php") ?>
        </section>
    </div>

    <!-- MODAL -->
    
    <div class="modal fade" id="pendingModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl pendingModalSize modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- <button>Print</button>
                    <button id="close-pending-modal" data-bs-dismiss="modal">Close</button> -->
                    PATIENT REFERRAL INFORMATION

                    <button id="proceed-ref-res">Proceed to Referral Response</button>
                </div>
                
                <div class="modal-body-incoming">
                    <div class="container">
                        <div class="left-div"> 
                        </div>
                        <div class="right-div">
                        </div>  
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" id="print-modal-btn-incoming" class="btn btn-primary" >Print</button>
                    <button type="button" id="close-modal-btn-incoming" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal-incoming" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-div">
                    <i id="modal-icon" class="fa-solid fa-triangle-exclamation"></i>
                    <h5 id="modal-title-incoming" class="modal-title-incoming" id="exampleModalLabel">Warning</h5>
                    <!-- <i class="fa-solid fa-circle-check"></i> -->
                </div>
               
            </div>
            <div id="modal-body-incoming" class="modal-body-incoming ml-2">
                Please input at least one value in any field.
            </div>
            <div class="modal-footer">
                <button id="ok-modal-btn-incoming" type="button" data-bs-dismiss="modal" data-target=".sensitive-case-btn">OK</button>
                <button id="yes-modal-btn-incoming" type="button" data-bs-dismiss="modal">Yes</button>
            </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay">
        <span>Updating the table...</span>
        <div id="myProgress">
            <div id="myBar"></div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script type="text/javascript"  charset="utf8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>

    <script src="../js/census.js?v= <?php echo time(); ?>"></script>

    <script>                        
    
       
    </script>
    
</body>
</html>


<?php $_SESSION['current_content'] = 'incoming_ref'; ?>
