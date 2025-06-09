<?php 
    session_start();
    include("../database/connection2.php");

    $start_date = $_POST['from_date'];
    $end_date = $_POST['to_date'];
    $end_date_adjusted = date('Y-m-d', strtotime($end_date . ' +1 day'));

    $averageDuration_reception = "00:00:00";
    $averageDuration_approval  = "00:00:00";
    $averageDuration_total  = "00:00:00";
    $fastest_response_final  = "00:00:00";
    $slowest_response_final  = "00:00:00";

    $sql = "SELECT hpercode, reception_time, date_time, final_progressed_timer 
            FROM incoming_referrals 
            WHERE refer_to = 'Bataan General Hospital and Medical Center' 
              AND date_time >= :start_date 
              AND date_time <= :end_date_adjusted 
              AND (status='Approved' OR status='Discharged')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['start_date' => $start_date , 'end_date_adjusted' => $end_date_adjusted]);
    $dataRecep = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Optional: echo raw data
    // echo json_encode($dataRecep);

    $recep_arr = array(); 
    foreach ($dataRecep as $row) {
        $date1 = new DateTime($row['reception_time']);
        $date2 = new DateTime($row['date_time']);
        $interval = $date1->diff($date2);
        $formattedDifference = sprintf('%02d:%02d:%02d', $interval->h, $interval->i, $interval->s);
        $recep_arr[] = $formattedDifference;
    }

    function durationToSeconds($duration) {
        list($hours, $minutes, $seconds) = explode(':', $duration);
        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    function secondsToDuration($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    $averageSeconds_reception = 0;
    $averageSeconds_approval = 0;
    $averageSeconds_total = 0;
    $fastest_recep_secs = array();

    for ($i = 0; $i < count($dataRecep); $i++) {
        $recep_sec = durationToSeconds($recep_arr[$i]);
        $appr_sec = durationToSeconds($dataRecep[$i]['final_progressed_timer']);

        $averageSeconds_reception += $recep_sec;
        $averageSeconds_approval  += $appr_sec;
        $averageSeconds_total     += ($recep_sec + $appr_sec);
        $fastest_recep_secs[] = $appr_sec;
    }

    $count = count($dataRecep);
    if ($count > 0) {
        $averageSeconds_reception = round($averageSeconds_reception / $count);
        $averageSeconds_approval  = round($averageSeconds_approval / $count);
        $averageSeconds_total     = round($averageSeconds_total / $count);

        $averageDuration_reception = secondsToDuration($averageSeconds_reception);  
        $averageDuration_approval  = secondsToDuration($averageSeconds_approval);
        $averageDuration_total     = secondsToDuration($averageSeconds_total);

        $fastest_response_final = secondsToDuration(min($fastest_recep_secs));
        $slowest_response_final = secondsToDuration(max($fastest_recep_secs));
    }

    $associativeArray = array(
        'totalReferrals' => $count,
        'averageSeconds_reception' => $averageSeconds_reception,
        'averageDuration_reception' => $averageDuration_reception,
        'averageSeconds_approval' => $averageSeconds_approval,
        'averageDuration_approval' => $averageDuration_approval,
        'averageSeconds_total' => $averageSeconds_total,
        'averageDuration_total' => $averageDuration_total,
        'fastest_response_final' => $fastest_response_final,
        'slowest_response_final' => $slowest_response_final,
    );

    echo json_encode($associativeArray);
?>
