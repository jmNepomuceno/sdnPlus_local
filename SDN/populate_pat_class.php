<?php 
    session_start();
    include("../database/connection2.php");

    $sql = "SELECT classifications FROM classifications";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>'; print_r($data); echo '</pre>';

    $classification_arr = array();
    for($i = 0; $i < count($data); $i++){
        array_push($classification_arr, $data[$i]['classifications']);
    }

    // $finalJsonString = json_encode($data);
    // echo $finalJsonString;
    for($i = 0; $i < count($classification_arr) + 1; $i++){
        if($i < count($classification_arr)){
            echo '<div class="classification-sub-div form-control">'.$classification_arr[$i].'</div>';
        }else{
            echo 
            '
                <div id="dynamic-width-div">
                    <input type="text" id="add-classification-input" class="form-control" placeholder="Input New Classification" autocomplete="off" />
                    <i id="add-classification-icon" class="fa-solid fa-circle-plus"></i> 
                </div>
            ';
        }
    }
?>