<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include_once("../database/class_dbconn.php");

    if ($_SERVER["REQUEST_METHOD"] === 'POST') {
        $requestData = json_decode(file_get_contents("php://input"), true);
    
        $sdnPatientID = $_POST['sdnPatientID'];
        $sdnCaseNo = $_POST['sdnCaseNo'];
        $sdnStatusInput = $_POST['sdnStatusInput'];
        $sdnProcessDT = $_POST['sdnProcessDT'];
        $statusDefer = $_POST['statusDefer'];
        $sdnUserLog = $_POST['sdnUserLog'];
    
        $msSqlConnection = new MsSqlConnection();
        $msPdo = $msSqlConnection->getPdo();
    
        $sdn_update = "UPDATE dbo.referral_data
                    SET status = :sdnStatusInput,
                    received_at = :sdnProcessDT,
                    received_by = :sdnUserLog,
                    defer_reason = :statusDefer
                    WHERE JSON_VALUE(sdn_data, '$.PatientID') = :sdnPatientID
                    AND JSON_VALUE(sdn_data, '$.caseNumber') = :sdnCaseNo";
    
        $stmt = $msPdo->prepare($sdn_update);
        $stmt->bindValue(':sdnStatusInput', $sdnStatusInput);
        $stmt->bindValue(':sdnProcessDT', $sdnProcessDT);
        $stmt->bindValue(':sdnUserLog', $sdnUserLog);
        $stmt->bindValue(':statusDefer', $statusDefer);
        $stmt->bindValue(':sdnPatientID', $sdnPatientID);
        $stmt->bindValue(':sdnCaseNo', $sdnCaseNo);
    
        try {
            $stmt->execute();
    
            if ($stmt) {
    
                // MySQL bucas_referral status update
                include('../database/connection2.php');
                $sdnProcessDT = date('Y-m-d H:i:s', strtotime($sdnProcessDT));
    
                $status_update = "UPDATE bghmc.bucas_referral 
                                SET status = :sdnStatusInput, 
                                    received_at = :sdnProcessDT, 
                                    received_by = :sdnUserLog 
                                WHERE JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.PatientID')) = :sdnPatientID
                                AND JSON_UNQUOTE(JSON_EXTRACT(sdn_data, '$.caseNumber')) = :sdnCaseNo";

                $stmtStatus = $pdo->prepare($status_update);
                $stmtStatus->bindValue(':sdnStatusInput', $sdnStatusInput);
                $stmtStatus->bindValue(':sdnProcessDT', $sdnProcessDT);
                $stmtStatus->bindValue(':sdnUserLog', $sdnUserLog);
                $stmtStatus->bindValue(':sdnPatientID', $sdnPatientID);
                $stmtStatus->bindValue(':sdnCaseNo', $sdnCaseNo);
                $stmtStatus->execute();
    
                if ($stmtStatus) {
                    $response = array(
                        "success" => true,
                        "message" => "Response submitted successfully."
                    );
                } else {
                    $response = array(
                        "success" => false,
                        "message" => "Failed updating status."
                    );
                }
    
            } else {
                $response = array(
                    "success" => false,
                    "message" => "Failed submitting response."
                );
            }
    
        } catch (PDOException $e) {
            error_log('PDO Exception: ' . $e->getMessage());
            $response = array(
                'success' => false,
                "message" => "PDO Exception: " . $e->getMessage()
            );
        }
    
        echo json_encode($response);
    
    } else {
        echo json_encode(array("success" => false, "message" => "Invalid request method."));
    }
    

?>