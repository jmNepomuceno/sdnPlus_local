<?php
    include '../database/connection2.php';

    $sql = "
        SELECT 
            SUM(survey_response = 'approve') AS approve_count,
            SUM(survey_response = 'disapprove') AS disapprove_count
        FROM survey_responses
    ";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'approve' => $result['approve_count'] ?? 0,
        'disapprove' => $result['disapprove_count'] ?? 0
    ]);
?>
