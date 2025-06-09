<?php
    session_start();
    include('../database/connection2.php');

    $hpercode = $_POST['hpercode'];

    // Assume $pdo is your PDO object
    try {
        $sql = "SELECT approval_details, pat_class FROM incoming_referrals WHERE hpercode=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$hpercode]);
        $incoming_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if there are any results
        if ($incoming_data) {
            // Encode the data to JSON
            $response = json_encode($incoming_data);
            echo $response;
        } else {
            // Handle case where no data is found
            echo json_encode(["error" => "No data found for the provided hpercode"]);
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
?>