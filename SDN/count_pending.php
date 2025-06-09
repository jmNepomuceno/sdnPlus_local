<?php
include('../database/connection2.php');

try {
    $query_count = "SELECT COUNT(*) AS pendingCount FROM bghmc.bucas_referral WHERE status = 'pending'";
    $stmt_count = $pdo->prepare($query_count);
    $stmt_count->execute();
    $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
    
    $count_pending = isset($count_result['pendingCount']) ? $count_result['pendingCount'] : 0;
    $response = array(
        'count_pending'=> $count_pending
    );

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
