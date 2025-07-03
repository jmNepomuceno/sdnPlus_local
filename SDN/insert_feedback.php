<?php
    session_start();
    include("../database/connection2.php");
    
    $sql = "INSERT INTO concerns (concern_txt, concern_requestDate, concern_requestor) VALUES (?,?,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['feedback'], date('Y-m-d H:i:s'), $_SESSION['first_name'] . " " . $_SESSION['last_name']]);

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

    foreach ($concerns as $concern) {
        echo '<li>
                <strong>[' . date('Y-m-d', strtotime($concern['concern_requestDate'])) . ']</strong> '
                . htmlspecialchars($concern['concern_txt']) .
                ' (<span style="color:#007bff;font-weight:600;">Requested by:</span> ' . htmlspecialchars($concern['concern_requestor']) . ')
            </li>';
    }
?>