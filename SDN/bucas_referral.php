<?php
    include('../database/connection2.php');

    $param_bucasID = isset($_POST['bucasID_parameter']) ? $_POST['bucasID_parameter'] : null;

    if ($param_bucasID) {
        $query_bucasSDN = "SELECT 
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.PatientID')) AS bucasID,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.caseNumber')) AS caseNo,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.LastName')) AS pxLast,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.FirstName')) AS pxFirst,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.MiddleName')) AS pxMiddle,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.ExtensionName')) AS pxExtension,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.Gender')) AS gender,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.CivilStatus')) AS civilStatus,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.Age')) AS age,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.description')) AS religion,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.ContactNo')) AS contactInfo,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.ServiceType')) AS ServiceType,    
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.icd10_code')) AS icd10Code,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.icd10_title')) AS icd10Title,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.SubjectiveInfo')) AS subjectiveInfo,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.ObjectiveInfo')) AS objectiveInfo,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.AssessmentInfo')) AS assessmentInfo,
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.PlanInfo')) AS planInfo,
                            bp, hr, rr, temp, weight, remarks, referred_by    
                        FROM 
                            bghmc.bucas_referral
                        WHERE 
                            JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.PatientID')) = :param_bucasID";

        $stmt = $pdo->prepare($query_bucasSDN);
        $stmt->bindParam(':param_bucasID', $param_bucasID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); 

        include_once("bucas_display.php");
    } else {
        echo "Error: bucasID is missing or empty.";
    }
?>
