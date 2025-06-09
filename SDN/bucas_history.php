<?php
    include('../database/connection2.php');

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
                        status, received_by, received_at
                    FROM 
                        bghmc.bucas_referral
                    WHERE status = 'accepted' OR status = 'deferred';";

    $stmt = $pdo->prepare($query_bucas);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentDate = date('m/d/Y');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="../css/bucas_referral.css">

<div class="bucas-container">
    <form id="bucas-list-form" method="POST">
        <div>
            <h1 class="page-title">BUCAS REFERRAL HISTORY</h1>
        </div>
        <div>
            <h5 class="page-title">As of <?php echo $currentDate ?></h5>
        </div>    
        <div class="row">
            <table id="tbl-history" class="table table-bordered custom-search-modal" style="width: 100%; border-spacing: -1px;">
                <thead>
                    <tr>
                        <th class="th-bg" style="width: 140px; text-align: center;">Patient ID</th>
                        <th class="th-bg" style="width: 80px; text-align: center;">Case Number</th>
                        <th class="th-bg" style="width: 200px; text-align: center;">Patient Name</th>
                        <th class="th-bg" style="width: 100px; text-align: center;">Service Type</th>
                        <th class="th-bg" style="width: 180px; text-align: center;">Agency</th>
                        <th class="th-bg" style="width: 70px; text-align: center;">Status</th>
                        <th class="th-bg" style="width: 130px; text-align: center;">Date Processed</th>
                        <th class="th-bg" style="width: 80px; text-align: center;">Process By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $agency = 'Bagac Community Medicare Hospital';
                        foreach ($data as $row) {
                            echo "<tr>";
                            echo "<td>" . $row['bucasID'] . "</td>";
                            echo "<td>" . $row['caseNo'] . "</td>";
                            echo "<td>" . $row['pxFirst'] ." ".$row['pxMiddle']." ".$row['pxLast'] ." ".$row['pxExtension']."</td>";
                            echo "<td>" . $row['ServiceType'] . "</td>";         
                            echo "<td>" . $agency . "</td>";
                            echo "<td>" . $row['status'] . "</td>";
                            echo "<td>" . date('m/d/Y h:i A', strtotime($row['received_at'])) . "</td>";
                            echo "<td>" . $row['received_by'] . "</td>";
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js">
// <script src="../js/bucas.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>


<script>
    $(document).ready(function() {
        $('#tbl-history').DataTable({
            paging: true,
            ordering: false,
            pageLength: 10,
            lengthChange: false,
        });
    });
</script>
