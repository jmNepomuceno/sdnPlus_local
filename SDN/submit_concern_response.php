<?php 
    include("../database/connection2.php");
    session_start();

    $concernID = $_POST['concernID'];
    $responseText = $_POST['responseText'];

    $stmt = $pdo->prepare("UPDATE concerns SET concern_response = ?, concern_response_date = NOW() WHERE concernID = ?");
    if ($stmt->execute([$responseText, $concernID])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
?>