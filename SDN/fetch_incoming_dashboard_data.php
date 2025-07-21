<?php
session_start();
header('Content-Type: application/json');

// Database connection
require_once '../database/connection2.php';

try {
    $hospital = $_SESSION['hospital_name'] ?? null;

    // Receive filter dates from POST
    $fromDate = $_POST['fromDate'] ?? null;
    $toDate   = $_POST['toDate'] ?? null;

    // Columns to select
    $columns = "
        incoming.id,
        incoming.referral_id,
        incoming.reference_num,
        incoming.patlast,
        incoming.patfirst,
        incoming.patmiddle,
        incoming.type,
        incoming.date_time,
        incoming.reception_time,
        incoming.status,
        incoming.approved_time,
        incoming.final_progressed_timer,
        incoming.referred_by,
        incoming.refer_to,
        incoming.pat_class,
        incoming.icd_diagnosis,
        icd_mapping.icd10_title AS icd_diagnosis_title
    ";

    $params = [];
    $where = [];

    if ($hospital && $hospital !== 'Bataan General Hospital and Medical Center') {
        $where[] = 'incoming.refer_to = ?';
        $params[] = $hospital;
    }

    if ($fromDate) {
        $where[] = 'DATE(incoming.date_time) >= ?';
        $params[] = $fromDate;
    }

    if ($toDate) {
        $where[] = 'DATE(incoming.date_time) <= ?';
        $params[] = $toDate;
    }

    // If no from/to given, default to today
    if (!$fromDate && !$toDate) {
        $today = date('Y-m-d');
        $where[] = 'DATE(incoming.date_time) = ?';
        $params[] = $today;
    }

    $sql = "
        SELECT $columns
        FROM incoming_referrals AS incoming
        LEFT JOIN icd_code_mapping AS icd_mapping
            ON incoming.icd_diagnosis = icd_mapping.icd10_code
    ";

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY incoming.date_time DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($referrals);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
    exit;
}
?>
