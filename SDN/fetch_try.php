<?php 
    session_start();
    include('../database/connection2.php');
    date_default_timezone_set('Asia/Manila');

    $dr_full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

    $sql = "SELECT * FROM incoming_referrals WHERE status='On-Process' AND isLocked=1 AND refer_to=? ORDER BY date_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["hospital_name"]]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $indexes = [];
    for($i = 0; $i < count($data); $i++) {
        if($data[$i]['whoLocked'] == $dr_full_name){
            array_push($indexes, $i);
        }
    }

    echo json_encode([
        "running_timer" => $_SESSION['running_timer'],
        "running_startTime" => $_SESSION['running_startTime'],
        "running_hpercode" => $_SESSION['running_hpercode'],
        "running_index" => $_SESSION['running_index'],
        "data" => $indexes,
        "prev_inc" => $_SESSION['prev_inc_ref_total'],
        "curr_inc" => ($indexes != $_SESSION['running_index']) ? $_SESSION['prev_inc_ref_total'] - 1 : $_SESSION['prev_inc_ref_total']
    ]);

    if($indexes != $_SESSION['running_index']){
        $_SESSION['running_index'] = [];
        $_SESSION['running_timer'] = [];
        $_SESSION['running_hpercode'] = [];
        $_SESSION['running_startTime'] = [];

        $_SESSION['prev_inc_ref_total'] = (int)$_SESSION['prev_inc_ref_total'] - 1;
    }
?>