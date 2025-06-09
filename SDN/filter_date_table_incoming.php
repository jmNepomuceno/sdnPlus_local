<?php 
    session_start();
    include("../database/connection2.php");

    // populate table header by the patient classifications.
    $class_code = array();
    $sql = "SELECT class_code FROM classifications";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pat_class_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // echo '<pre>'; print_r($pat_class_data); echo '</pre>';
    for($i = 0; $i < count($pat_class_data); $i++){
        $class_code[$pat_class_data[$i]['class_code'] . "_primary"] = 0;
        $class_code[$pat_class_data[$i]['class_code'] . "_secondary"] = 0;
        $class_code[$pat_class_data[$i]['class_code'] . "_tertiary"] = 0;
    }  

    $dateTime = new DateTime();
    $formattedDate = $dateTime->format('Y-m-d') . '%';

    $start_date = $_POST['from_date'];
    $end_date = $_POST['to_date'];
    $end_date_adjusted = date('Y-m-d', strtotime($end_date . ' +1 day'));
    // echo $formattedDate;

    $sql = "SELECT pat_class, type, referred_by FROM incoming_referrals WHERE (status='Approved' OR status='Discharged') AND refer_to = '" . $_SESSION["hospital_name"] . "' AND date_time >= '$start_date' AND date_time < '$end_date_adjusted' ";
    // $sql = "SELECT pat_class, type, referred_by FROM incoming_referrals WHERE (status='Approved' OR status='Checked' OR status='Arrived') AND refer_to = '" . $_SESSION["hospital_name"] . "'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $tr_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // echo '<pre>'; print_r($tr_data); echo '</pre>';

    for($i = 0; $i < count($tr_data); $i++){
        echo '<input type="hidden" class="referred-by-class" value="' . $tr_data[$i]["referred_by"] . '">';
    }

    $in_table = [];
    
    foreach ($tr_data as $row){
        if (!in_array($row['referred_by'], $in_table)) {
            $in_table[] = $row['referred_by'];
        }   
    }

    // echo '<pre>'; print_r($class_code); echo '</pre>';
    $loop_index = 0;
    for($i = 0; $i < count($in_table); $i++){
        foreach ($tr_data as $row){
            if($in_table[$i] === $row['referred_by']){
                $referred_by = $row['referred_by'];

                // new logic for dynamic rendering of the classication of the patients case to be put on the table
                $lowercase_string = strtolower($row['pat_class']);
                $class_code[$row['type']."_".$lowercase_string] += 1;
            }        
        }


        echo '
        <tr class="tr-div text-center"> 
            <td class="border-2 border-slate-700 col-span-3">'.$referred_by.'</td>
        ';
        foreach ($class_code as $key => $value) {
            echo '
                <td class="add border-2 border-slate-700">'. $value .'</td>
            ';
        }
        echo '
            <td class="sumCell border-2 border-slate-700">'. array_sum($class_code) .'</td>
        </tr>
        ';

        $class_code = array_fill_keys(array_keys($class_code), 0);
    }   
?>