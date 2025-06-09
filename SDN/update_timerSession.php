<?php 
    session_start();
    include("../database/connection2.php");

    $_SESSION['running_timer'];
    $_SESSION['running_bool'];
    $_SESSION['running_startTime'];
    $_SESSION['running_hpercode'];
    $_SESSION['running_index'];

    $indexToDelete = $_POST['index'];
    $hpercode = $_POST['hpercode'];

    $dr_full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

    $sql = "SELECT isLocked FROM incoming_referrals WHERE status='On-Process' AND isLocked=1 AND hpercode!=? AND whoLocked!=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hpercode , $dr_full_name]);
    $isLocked = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($isLocked) > 0){
        if($indexToDelete > 0){
            $indexToDelete = $indexToDelete - 1;
        }
    }

    if (max($_SESSION['running_index']) - min($_SESSION['running_index']) > count($_SESSION['running_index']) - 1) {
        array_shift($_SESSION['running_index']); // Remove the first element
    
        $_SESSION['running_index'] = array_map(function($item) {
            return $item - 1; // Subtract 1 from the remaining elements
        }, $_SESSION['running_index']);
    } else {
        array_pop($_SESSION['running_index']); // Default behavior
    }

    // $is_sorted = true; // Flag to check if array is in increasing order
    // for ($i = 0; $i < count($_SESSION['running_index']) - 1; $i++) {
    //     if ($_SESSION['running_index'][$i] >= $_SESSION['running_index'][$i + 1]) {
    //         $is_sorted = false; // If any element is greater than or equal to the next, it's not sorted
    //         break;
    //     }
    // }

    // if (!$is_sorted) {
    //     // Handle arrays that are not sorted in increasing order
    //     array_shift($_SESSION['running_index']); // Remove the first element
    //     $_SESSION['running_index'] = array_map(function($item) {
    //         return $item - 1; // Subtract 1 from the remaining elements
    //     }, $_SESSION['running_index']);
    // } else {
    //     // Handle arrays that are sorted
    //     array_pop($_SESSION['running_index']); // Remove the last element
    // }
    
    unset($_SESSION['running_timer'][$indexToDelete]);
    unset($_SESSION['running_startTime'][$indexToDelete]);
    unset($_SESSION['running_hpercode'][$indexToDelete]);


    echo json_encode([
        "running_timer_var" => array_values($_SESSION['running_timer']),
        "running_startTime_var" => array_values($_SESSION['running_startTime']),
        "running_hpercode_var" => array_values($_SESSION['running_hpercode']),
        "running_index_var" => array_values($_SESSION['running_index']),
        "key" => count($isLocked)
    ]);

    // noel.laxamana@sdn.com
    // ej.bautista@sdn.com
?>