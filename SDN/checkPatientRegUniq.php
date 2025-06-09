<?php
    session_start();
    include('../database/connection2.php');

    $patlast = $_POST['patlast'];
    $patfirst = $_POST['patfirst'];
    $patmiddle = $_POST['patmiddle'];
    $patsuffix = $_POST['patsuffix'];
    $patbdate = $_POST['patbdate'];

    try {
        $sql = "SELECT * FROM hperson WHERE patlast LIKE :patlast AND patfirst LIKE :patfirst AND patmiddle LIKE :patmiddle AND patsuffix LIKE :patsuffix AND patbdate LIKE :patbdate ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':patlast' => '%' . $patlast . '%',
            ':patfirst' => '%' . $patfirst . '%',
            ':patmiddle' => '%' . $patmiddle . '%',
            ':patsuffix' => '%' . $patsuffix . '%',
            ':patbdate' => '%' . $patbdate . '%'
        ]);
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