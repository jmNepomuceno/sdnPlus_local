<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    
    $sql = "INSERT INTO concerns (concern_txt, concern_requestDate, concern_requestor, concern_loc) VALUES (?,?,?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['feedback'], date('Y-m-d H:i:s'), $_SESSION['first_name'] . " " . $_SESSION['last_name'], $_SESSION['hospital_name']]);

    // if ($stmt->rowCount() > 0) {
    //     $response = [
    //         'status' => 'success',
    //         'message' => 'Feedback submitted successfully.'
    //     ];
    // } else {
    //     $response = [
    //         'status' => 'error',
    //         'message' => 'Failed to submit feedback.'
    //     ];
    // }

    $stmt = $pdo->query("SELECT concern_txt, concern_requestDate, concern_requestor FROM concerns ORDER BY concern_requestDate DESC");
    $concerns = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>