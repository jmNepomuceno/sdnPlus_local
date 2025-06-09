<?php
    include('../database/connection2.php');
    $count_pending = 0;

    //Query to fetch the count of pending referrals
    $query_count = "SELECT COUNT(*) AS pendingCount FROM bghmc.bucas_referral WHERE status = 'pending'";
    $stmt_count = $pdo->prepare($query_count);
    $stmt_count->execute();
    $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $count_pending = $count_result['pendingCount'];

    $query_bucas = "SELECT 
                        JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.PatientID')) AS bucasID,
                        JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.caseNumber')) AS caseNo,
                        JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.LastName')) AS pxLast,
                        JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.FirstName')) AS pxFirst,
                        JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.MiddleName')) AS pxMiddle,
                        JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.ExtensionName')) AS pxExtension,
                        JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.Gender')) AS gender,
                        JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.CivilStatus')) AS civilStatus,
                        JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.Age')) AS age, 
                        JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.ServiceType')) AS ServiceType,
                        status AS statusReferral
                    FROM 
                        bghmc.bucas_referral
                    WHERE 
                        status = 'pending'";

    $stmt = $pdo->prepare($query_bucas);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>'; print_r($data); echo '</pre>';
    // Array
    // (
    //     [0] => Array
    //         (
    //             [bucasID] => BUCAS-20240307-00034
    //             [caseNo] => 2024-000002
    //             [pxLast] => DE JESUS
    //             [pxFirst] => JUANITO
    //             [pxMiddle] => MACARAIG
    //             [pxExtension] => 
    //             [gender] => Male
    //             [civilStatus] => Married   
    //             [age] => 58
    //             [ServiceType] => Medical
    //             [statusReferral] => pending
    //         )
    // )

    session_start();
    $_SESSION['count_pending'] = $count_pending;

    $currentDate = date('m/d/Y');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="../css/bucas_referral.css">

<div class="bucas-container">
    <form id="bucas-list-form" method="POST">
        <div>
            <h1 class="page-title">BUCAS INCOMING REFERRAL</h1>
        </div>
        <div>
            <h5 class="page-title">As of <?php echo $currentDate ?></h5>
        </div>    
        <div class="row">
            <table id="tbl-bucas" class="table table-bordered custom-search-modal" style="width: 100%; border-spacing: -1px;">
                <thead>
                    <tr>
                        <th class="th-bg" style="width: 140px; text-align: center;">Patient ID</th>
                        <th class="th-bg" style="width: 90px; text-align: center;">Case Number</th>
                        <th class="th-bg" style="width: 100px; text-align: center;">Last Name</th>
                        <th class="th-bg" style="width: 100px; text-align: center;">First Name</th>
                        <th class="th-bg" style="width: 100px; text-align: center;">Middle Name</th>
                        <th class="th-bg" style="width: 80px; text-align: center;">Ext. Name</th>
                        <th class="th-bg" style="width: 100px; text-align: center;">Service Type</th>
                        <th class="th-bg" style="width: 220px; text-align: center;">Agency</th>
                        <th class="th-bg" style="width: 80px; text-align: center;">Status</th>
                        <th class="th-bg" style="width: 80px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $agency = 'Bagac Community Medicare Hospital';
                        foreach ($data as $row) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['bucasID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['caseNo']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['pxLast']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['pxFirst']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['pxMiddle']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['pxExtension']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ServiceType']) . "</td>";         
                            echo "<td>" . htmlspecialchars($agency) . "</td>";
                            echo "<td>" . htmlspecialchars($row['statusReferral']) . "</td>";
                            echo '<td style="text-align: center;">';
                            echo '<a href="javascript:void(0);" class="view-link" data-bs-toggle="modal" data-bs-target="#bucasBackdrop" data-bucas-id="' . htmlspecialchars($row['bucasID']) . '">VIEW</a>';
                            echo '</td>';
                            echo "</tr>";
                        }
                    ?>    
                </tbody>
            </table>
        </div>     
    </form>
</div>

<!-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/bucas.js?v=<?php echo time(); ?>"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>


<script>
    $(document).ready(function() {
        $('#tbl-bucas').DataTable({
            paging: true,
            ordering: false,
            pageLength: 10,
            lengthChange: false,
        });
    });
</script>
