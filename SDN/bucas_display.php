<?php session_start(); ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/bucas_referral.css">

<body>
    <div class="bucas-container">
        <form id="bucas-history-form" method="POST">     
            <!-- <h2 class="page-title">BUCAS MEDICAL RECORD SUMMARY</h2> -->
            <table id="tbl-referral" class="table table-bordered custom-search-modal" style="width: 100%; border-spacing: -1px;">
                <?php foreach ($data as $result) { ?>
                <tr>                
                    <th class="summary-th-width">Patient ID</th> 
                    <td class="summary-td-width">                        
                        <input type="text" name="sdnBucasID" id="sdnBucasID" class="form-control" 
                        value="<?php echo $result['bucasID'] ?>" style="background-color: #A5D6A7;">
                    </td>
                    <th class="summary-th-width">Case Number</th>
                    <td class="summary-td-width">                        
                        <input type="text" name="sdnCaseNo" id="sdnCaseNo" class="form-control" 
                        value="<?php echo $result['caseNo'] ?>" style="background-color: #A5D6A7;">                        
                    </td>
                    <th rowspan="2">
                        ICD-10 Diagnosis:<br><br>
                        <span class="summary-data">
                            <?php echo $result['icd10Code'] . ' - ' . trim($result['icd10Title']); ?>
                        </span>                    
                    </th>    
                </tr>
                <tr>
                <th class="summary-th-width">Service Type</th>
                <td><?php echo $result['ServiceType'] ?></td>
                <th class="summary-th-width">Age</th>
                <td><?php echo $result['age'] ?></td>                  
            </tr>
            <tr>
                <th class="summary-th-width">Last Name</th>
                <td><?php echo $result['pxLast'] ?></td>   
                <th rowspan="3" colspan="3">                    
                    SUBJECTIVE:<br><br>   
                    <span class="summary-medical">
                        <?php echo $result['subjectiveInfo']; ?>
                    </span>           
                </th>
            </tr>
            <tr>
                <th class="summary-th-width">First Name</th>
                <td><?php echo $result['pxFirst'] ?></td>
            </tr>
            <tr>
                <th class="summary-th-width">Middle Name</th>
                <td><?php echo $result['pxMiddle'] ?></td>                
            </tr>
            <tr>
                <th class="summary-th-width">Extension Name</th>
                <td><?php echo $result['pxExtension'] ?></td>
                <th rowspan="3" colspan="3">                    
                    OBJECTIVE:<br><br>   
                    <span class="summary-medical">
                        <?php echo $result['objectiveInfo']; ?>
                    </span>           
                </th>
            </tr>
            <tr>
                <th class="summary-th-width">Gender</th>
                <td><?php echo $result['gender'] ?></td>
            </tr>
            <tr>
                <th class="summary-th-width">Civil Status</th>
                <td><?php echo $result['civilStatus'] ?></td>
            </tr>
            <tr>
                <th class="summary-th-width">Religion</th>
                <td><?php echo $result['religion'] ?></td>  
                <th rowspan="3" colspan="3">                    
                    ASSESSMENT:<br><br>   
                    <span class="summary-medical">
                        <?php echo $result['assessmentInfo']; ?>
                    </span>           
                </th>
            </tr>
            <tr>                
            <th class="summary-th-width">Contact No</th>
                <td><?php echo $result['contactInfo'] ?></td>
            </tr>
            <th class="summary-th-width">Blodd Pressure</th>
                <td><?php echo $result['bp'] ?></td>
            </tr>
            <th class="summary-th-width">Heart Rate (HR)</th>
                <td><?php echo $result['hr'] ?></td>
                <th rowspan="3" colspan="3">                    
                    PLAN:<br><br>   
                    <span class="summary-medical">
                        <?php echo $result['planInfo']; ?>
                    </span>           
                </th>
            </tr>
            <th class="summary-th-width">Respiratory Rate (RR)</th>
                <td><?php echo $result['rr'] ?></td>
            </tr>
            <th class="summary-th-width">Body Temperature</th>
                <td><?php echo $result['temp'] ?></td>
            </tr>
                <th class="summary-th-width">Weight</th>
                <td><?php echo $result['weight'] ?></td>
                <th class="summary-th-response">Select Response Status</th>
                <td>                    
                    <div class="select-wrapper">
                        <select name="sdnStatus" id="sdnStatus" class="form-control" onchange="updateTextarea()" required autocomplete="off">
                            <option value="">Select</option>
                            <option value="accepted">Accepted</option>
                            <option value="deferred">Deferred</option>
                        </select>
                    </div>
                </td>
                <th class="summary-th-response">Deffered Reason</th>
            </tr>
            <tr>
                <?php 
                    $timezone = new DateTimeZone('Asia/Manila');
                    $currentDateTime = new DateTime('now', $timezone); 
                    $formattedDateTime = $currentDateTime->format('m/d/Y h:i A');           
                ?>
                <th class="summary-th-width">Remarks</th>
                <td><?php echo $result['remarks'] ?></td>
                <th class="summary-th-response">Process Date/Time</th>
                <td>
                    <input type="text" name="sdnProcessDT" id="sdnProcessDT" class="form-control" 
                    value="<?php echo $formattedDateTime ?>"
                    autocomplete="off">
                </td>
                <td rowspan="2">
                    <textarea name="statusDefer" id="statusDefer" rows="3" style="resize: none; width:250px;" disabled></textarea>
                </td>
            </tr>
            <tr>
                <?php $userlogged = $_SESSION['user_name']; ?>
                <th class="summary-th-width">Referred By</th>
                <td><?php echo $result['referred_by'] ?></td>
                <th class="summary-th-response">Processed By</th>   
                <td>
                <input type="text" name="sdnUserID" id="sdnUserID" class="form-control" 
                    value="<?php echo $userlogged ?>"
                    autocomplete="off">
                </td>  
            </tr>
                <?php } ?>
            </table>

            
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateTextarea() {
            const statusReasonInput = document.getElementById('sdnStatus');
            const statusDefer = document.getElementById('statusDefer');

            if (statusReasonInput.value === 'deferred') {
                statusDefer.disabled = false;
            } else {
                statusDefer.disabled = true;
            }
        }
    </script>
</body>