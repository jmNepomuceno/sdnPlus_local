<?php 
    include("../database/connection2.php");
    session_start();

    $hospital_loc = $_SESSION['hospital_name'];
    $role = $_SESSION['user_role'];

    if ($hospital_loc === 'Bataan General Hospital and Medical Center') {
        // Fetch all locations (no WHERE clause)
        $sql = "SELECT concern_txt, concern_requestDate, concern_requestor, concern_loc, concernID, concern_response, concern_response_date
                FROM concerns
                ORDER BY concern_requestDate DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(); // no parameter
    } else {
        // Fetch only for this hospital
        $sql = "SELECT concern_txt, concern_requestDate, concern_requestor, concern_loc, concernID, concern_response, concern_response_date
                FROM concerns
                WHERE concern_loc=?
                ORDER BY concern_requestDate DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$hospital_loc]);
    }

    $userConcerns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'concerns' => $userConcerns,
        'role' => $role
    ]);
?>