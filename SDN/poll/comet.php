<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('../../database/connection2.php');

header('Content-Type: application/json');
set_time_limit(0); // Prevent the script from timing out

$lastModifiedArr = $_POST['last_modified_arr']; // The array of last_modified times sent via AJAX
$startTime = time();
function compareArrays($arr1, $arr2) {
    foreach ($arr1 as $index => $item) {
        if ($item['last_modified'] !== $arr2[$index]['last_modified']) {
            return false; // Found a difference
        }
    }
    return true; // Arrays are identical
}

while (true) {
    // Fetch the latest data from the database
    $sql = "SELECT last_modified FROM incoming_referrals WHERE (status='Pending' OR status='On-Process') AND refer_to=:referTo ORDER BY date_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':referTo', $_SESSION["hospital_name"], PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!compareArrays($lastModifiedArr, $result)) {
        echo json_encode($result); // Return the new data
        break;
    }

    if (time() - $startTime > 5) { // Exit the loop after 30 seconds
        echo json_encode(['status_comet' => 'timeout']);
        break;
    }

    // Sleep for a while before checking again to avoid hammering the server
    sleep(1);
}

?>
