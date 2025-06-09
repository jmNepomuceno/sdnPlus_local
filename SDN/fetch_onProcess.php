<?php 
    session_start();
    include('../database/connection2.php');
    date_default_timezone_set('Asia/Manila');

    function isAssociativeArray($array) {
        if (!is_array($array)) {
            return false; // Not an array
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    // Initialize session variables if not set
    if (!isset($_SESSION['running_timer'])) {
        $_SESSION['running_timer'] = [];
    }
    if (!isset($_SESSION['running_startTime'])) {
        $_SESSION['running_startTime'] = [];
    }
    if (!isset($_SESSION['running_hpercode'])) {
        $_SESSION['running_hpercode'] = [];
    }
    if (!isset($_SESSION['running_index'])) {
        $_SESSION['running_index'] = [];
    }

    $_SESSION['running_index'] = array_values($_SESSION['running_index']);
    $_SESSION['running_timer'] = array_values($_SESSION['running_timer']);
    $_SESSION['running_startTime'] = array_values($_SESSION['running_startTime']);
    $_SESSION['running_hpercode'] = array_values($_SESSION['running_hpercode']);

    // Extract POST data
    $index = (int)$_POST['index'];
    $timer = $_POST['timer'];
    $startTime = $_POST['startTime'];
    $hpercode = $_POST['hpercode'];
    $running_bool = $_POST['running_bool'];
    $check_ref_length = $_POST['check_ref_length'];
    $changed_inc_total = "false";
    $rowsHtml = [];

    // get mo yung lahat ng naka on process tas index


    // if($_SESSION['prev_inc_ref_total'] > $check_ref_length && $_SESSION['any_referral_locked'] == true){
    //     for($i = 0; $i < count($_SESSION['running_timer']); $i++){
    //         $_SESSION['running_index'][$i] = $_SESSION['running_index'][$i] - 1;
    //     }
    //     $_SESSION['prev_inc_ref_total'] = (int)$_SESSION['prev_inc_ref_total'] - 1;
    //     $changed_inc_total = "true";

    //     if($index >= 1){
    //         $index -= 1;
    //     }
    // }

    // if($check_ref_length == 1){
    //     $_SESSION['any_referral_locked'] = false;
    // }


    // Check if the index already exists in the session
    $existingIndex = array_search($index, $_SESSION['running_index']);
    if ($existingIndex !== false) {
        // Update existing data
        $_SESSION['running_timer'][$existingIndex] = $timer; 
        $_SESSION['running_startTime'][$existingIndex] = $startTime;
    } else {
        // Add new data
        $_SESSION['running_index'][] = $index;
        $_SESSION['running_timer'][] = $timer; 
        $_SESSION['running_startTime'][] = $startTime;
        $_SESSION['running_hpercode'][] = $hpercode;
        $_SESSION['datatable_index'] = $index;
        $_SESSION['running_bool'] = $running_bool;
    }

    
    // function isProperlySorted($array) {
    //     for ($i = 0; $i < count($array) - 1; $i++) {
    //         // If an element is greater than or equal to the next, it's not sorted properly
    //         if ($array[$i] >= $array[$i + 1]) {
    //             return false;
    //         }
    //     }
    //     return true;
    // }
    
    // // Check if the array is sorted
    // if (!isProperlySorted($_SESSION['running_index'])) {
    //     // Remove the last element
    //     array_pop($_SESSION['running_index']);
    //     array_pop($_SESSION['running_timer']);
    //     array_pop($_SESSION['running_startTime']);
    //     array_pop($_SESSION['running_hpercode']);
    // }



    // Clean up session arrays to maintain order
    $_SESSION['running_index'] = array_values($_SESSION['running_index']);
    $_SESSION['running_timer'] = array_values($_SESSION['running_timer']);
    $_SESSION['running_startTime'] = array_values($_SESSION['running_startTime']);
    $_SESSION['running_hpercode'] = array_values($_SESSION['running_hpercode']);

    // Return session data as JSON
    echo json_encode([
        "timer" => $_SESSION['running_timer'],
        "startTime" => $_SESSION['running_startTime'],
        "hpercode" => $_SESSION['running_hpercode'],
        "index" => $_SESSION['running_index'],
        "changed_inc_total" => $changed_inc_total,
        "session_prev_inc" => $_SESSION['prev_inc_ref_total'],
        "any" => $_SESSION['any_referral_locked'],
        "pass_index" => $index,
    ]);
?>