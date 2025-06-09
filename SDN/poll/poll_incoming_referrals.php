<?php
session_start();
include("../../database/connection2.php");

// error_reporting(E_ALL);
// ini_set('display_errors', 1);



// echo json_encode($message);
// echo '<pre>'; print_r("asdf"); echo '</pre>';

try {
    $lastModified = $_POST['last_modified_arr'];
    $startTime = time();
    $timeout = 30; // Maximum time (in seconds) to wait before ending the loop

    while (true) {
        if (time() - $startTime >= $timeout) {
            echo json_encode(['timeout' => true]);
            break;
        }

        $sql = "SELECT last_modified FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND refer_to='". $_SESSION["hospital_name"] ."' ORDER BY date_time ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $message = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($message != $lastModified) {
            echo json_encode($message);
            break;
        }else{
            echo json_encode(['asdf' => 'fdsa']);
            break;
        }
       

        sleep(1);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

$sql = null;
?>
