<?php 
    session_start();
    include('../database/connection2.php');
    date_default_timezone_set('Asia/Manila');

    // get the current date and time
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $monthYear = $now->format('F Y');
    $fullDate = $now->format('F j, Y - h:ia');

    $sql = "SELECT hospital_name FROM sdn_hospital ORDER BY hospital_name ASC;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $hospital_names = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($_SESSION['user_name'] === 'admin'){
        $user_name = 'Bataan General Hospital and Medical Center';
    }else{
        $user_name = $_SESSION['hospital_name'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <?php require "../header_link.php" ?>
    <link rel="stylesheet" href="../css/dashboard_incoming.css">
    <link rel="stylesheet" href="../css/dashboard_incoming_mediaq.css">
</head>

<body class="h-screen">
    <!-- header -->
    <header class="header-div">
        <div class="side-bar-title">
            <h1 id="sdn-title-h1" style="text-align: center;">Service Delivery Network Plus (SDN+)</h1>
            <div class="side-bar-mobile-btn">
                <i id="navbar-icon" class="fa-solid fa-angles-left"></i>
            </div>
        </div>
        <div class="account-header-div">
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
            <div id="setting-btn" class="nav-drop-btns">
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
        <div class="main-title-div">
            <label>Dashboard For Incoming Referrals</label>
            <div> 
                <label id="curr-month-lbl"><?= htmlspecialchars($monthYear) ?></label>
                <label id="curr-date-lbl">as of <?= htmlspecialchars($fullDate) ?></label>
            </div>
        </div>

        <div class="main-div-container container-1">
            <div class="main-filter-div">
                <div class="filter-date-div">
                    <button id="filter-date-btn">Filter</button>
                    <div>
                        <label>from: <input type="date" id='from-date-inp'> to: <input type="date" id='to-date-inp'></label>
                    </div>
                </div>

                <div class="filter-type-div">
                    <button id="filter-type-button">Filter Case Type: </button>
                    <button class="filter-type-btn" id="er-filter-type-btn" data-type="ER">ER</button>
                    <button class="filter-type-btn" id="ob-filter-type-btn" data-type="OB">OB</button>
                    <button class="filter-type-btn" id="opd-filter-type-btn" data-type="OPD">OPD</button>
                    <button class="filter-type-btn" id="toxicology-filter-type-btn" data-type="Toxicology">TOX</button>
                    <button class="filter-type-btn" id="pcr-filter-type-btn" data-type="PCR">PCR</button>
                    <button class="filter-type-btn" id="cancer-filter-type-btn" data-type="CANCER">CANCER</button>
                    <button class="filter-type-btn" id="nbscc-filter-type-btn" data-type="NBSCC">NBSCC</button>
                </div>

                <div class="filter-rhu-div">
                    <button id="filter-rhu-button">Filter RHU: </button>
                    <select class="form-control"  id="refer-to-select">
                        <option value="" selected> -- Select the RHU / Local Hospitals --</option>
                        <?php for($i = 0; $i < count($hospital_names); $i++) { ?>
                            <?php echo "<option value='" . $hospital_names[$i]['hospital_name'] . "'" . ">" . $hospital_names[$i]['hospital_name'] . "</option>" ?>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="main-turnaround-div">
                <div>
                    <label id="total-processed-refer">18</label>
                    <label>Total Processed Referrals</label>
                </div>
                <div>
                    <label id="average-reception-id" class="average-reception-lbl"></label>
                    <label>Average Reception Time</label>
                </div>

                <div>
                    <label id="average-approve-id"></label>
                    <label>Average Approval Time</label>
                </div>

                <div>
                    <label id="fastest-id"></label>
                    <label>Fastest Response Time</label>
                </div>

                <div>
                    <label id="slowest-id"></label>
                    <label>Slowest Response Time</label>
                </div>
            </div>
                            
            <div class="main-graph-div">
                <div class="main-graph-sub-div">
                    <label class="font-semibold text-xl ">Case Category</label>
                    <div class="canva-class" id="myChart-1"></div>
                </div>

                <div class="main-graph-sub-div">
                    <label class="font-semibold text-xl">Case Type</label>
                    <div class="canva-class" id="myChart-2"></div>
                </div>

                <div class="main-graph-sub-div">
                    <label class="font-semibold text-xl">Referring Health Facility</label>
                    <div class="canva-class" id="myChart-3"></div>
                </div>
            </div>
        </div>


        
        <div class="main-div-container container-2">
            <h3> ICD Diagnosis Report</h3>
            <div class="icd-dashboard">
                <div class="icd-chart-container">
                    <div id="icd-bar-chart" style="width: 100%; height: 100%;"></div>
                </div>

                <div class="icd-table-container mt-4">
                    <table id="icd-dataTable" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>ICD Code & Title</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="main-div-container container-3">
            <div class="main-data-div">
                <table id="referrals-summary-table" class="display nowrap">
                    <thead>
                        <tr class="thead-tr-main">
                            <th rowspan="2">Referring Health Facility</th>
                            <th colspan="3">ER</th>
                            <th colspan="3">OB</th>
                            <th colspan="3">PCR</th>
                            <th colspan="3">Toxicology</th>
                            <th colspan="3">Cancer</th>
                            <th colspan="3">OPD</th>
                            <th colspan="3">NBSCC</th>
                            <th rowspan="2">Total</th>
                        </tr>
                        <tr>
                            <th>Primary</th><th>Secondary</th><th>Tertiary</th>
                            <th>Primary</th><th>Secondary</th><th>Tertiary</th>
                            <th>Primary</th><th>Secondary</th><th>Tertiary</th>
                            <th>Primary</th><th>Secondary</th><th>Tertiary</th>
                            <th>Primary</th><th>Secondary</th><th>Tertiary</th>
                            <th>Primary</th><th>Secondary</th><th>Tertiary</th>
                            <th>Primary</th><th>Secondary</th><th>Tertiary</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>          
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal-dashboardIncoming" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <!-- <div class="modal-dialog" role="document"> -->
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-div">
                        <i id="modal-icon" class="fa-solid fa-circle-exclamation"></i>
                        <h5 id="modal-title-main" class="modal-title-main" id="exampleModalLabel">Notification</h5>
                        <!-- <i class="fa-solid fa-circle-check"></i> -->
                    </div>
                    <!-- <button id="x-btn" type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> -->
                </div>
                <!-- <div id="modal-body-main" class="modal-body-main"> -->
                <div id="modal-body" class="logout-modal">
                    No incoming referrals for today yet.
                </div>
                <div class="modal-footer">
                    <button id="ok-modal-btn-main" type="button" data-bs-dismiss="modal">OK</button>
                    <button id="yes-modal-btn-main" type="button" data-bs-dismiss="modal" style="display:none">Yes</button>
                </div>
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

    <!-- Load Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-3d.js"></script>

    <script type="text/javascript" src="../js/dashboard_incoming.js?v=<?php echo time(); ?>"></script>
</body>
</html>