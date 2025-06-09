<?php
    session_start();
    include("../database/connection2.php");
    date_default_timezone_set('Asia/Manila');

    $column = $_POST['column'];

    // $allowed_columns = ['icd10_code', 'icd10_title', 'icd11_code', 'icd11_title'];
    // if (!in_array($column, $allowed_columns)) {
    //     die('Invalid column');
    // }

    $search_keyword = $_POST['search_keyword'];

    $sql = "SELECT * FROM icd_code_mapping WHERE $column LIKE ? LIMIT 7";
    $stmt = $pdo->prepare($sql);
    $search_param = '%' . $search_keyword . '%';
    $stmt->execute([$search_param]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
    // echo "success";
?>