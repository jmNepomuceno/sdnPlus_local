<?php
    session_start();
    include '../database/connection2.php';

    $user = $_SESSION['user_name'] ?? 'anonymous';
    $response = $_POST['response'] ?? '';

    if ($response) {
        $sql = "INSERT INTO survey_responses (survey_user, survey_response, survey_date, survey_location) VALUES (?, ?, NOW(),?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user, $response, $_SESSION['hospital_name']]);

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid response']);
    }
?>