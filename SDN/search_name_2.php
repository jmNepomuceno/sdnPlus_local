<?php
    include("../database/connection2.php");
    session_start();
    
    $search_lname = $_POST['search_lname'];
    $search_fname = $_POST['search_fname'];
    $search_mname = $_POST['search_mname'];

    $sql = "none";
    $search_lname = filter_input(INPUT_POST, 'search_lname');
    $search_fname = filter_input(INPUT_POST, 'search_fname');
    $search_mname = filter_input(INPUT_POST, 'search_mname');

    $hpatcode = $_SESSION['hospital_code'];
    $hpatcode = (string) $hpatcode;
    $conditions = array();

    if (!empty($search_lname)) {
        $conditions[] = "patlast LIKE :search_lname";
    }

    if (!empty($search_fname)) {
        $conditions[] = "patfirst LIKE :search_fname";
    }

    if (!empty($search_mname)) {
        $conditions[] = "patmiddle LIKE :search_mname";
    }

    $sql = "SELECT patfirst, patlast, patmiddle, hpercode, patbdate, hpatcode, status FROM hperson";

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // if($hpatcode != '1437'){
    //     $sql .= " AND hpatcode=:hpatcode;";
    // }
    $stmt = $pdo->prepare($sql);

    if (!empty($search_lname)) {
        $search_lname_param = "%$search_lname%";
        $stmt->bindParam(':search_lname', $search_lname_param, PDO::PARAM_STR);
    }

    if (!empty($search_fname)) {
        $search_fname_param = "%$search_fname%";
        $stmt->bindParam(':search_fname', $search_fname_param, PDO::PARAM_STR);
    }

    if (!empty($search_mname)) {
        $search_mname_param = "%$search_mname%";
        $stmt->bindParam(':search_mname', $search_mname_param, PDO::PARAM_STR);
    }

    
    // if($hpatcode != '1437'){
    //     $stmt->bindParam(':hpatcode', $hpatcode, PDO::PARAM_STR);   
    // }
    
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $finalJsonString = json_encode($data);
    // echo $finalJsonString;
    // echo json_encode($data);
    
    $pagination_html = '';
    $results_html = '';
    if(count($data) >= 1){
        // Number of items per page
        $items_per_page = 10;
        // Current page number from POST parameter (default is 1)
        $current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        // Calculate the offset for the SQL query
        $offset = ($current_page - 1) * $items_per_page;

        // Total number of items (assuming $data is your array of items)
        $total_items = count($data);

        // Total number of pages
        $total_pages = ceil($total_items / $items_per_page);

        // Slice the data array to get only the items for the current page
        $page_data = array_slice($data, $offset, $items_per_page);

        $results_html = '';
        $i = $offset; // Start the index from the current page offset

        foreach ($page_data as $item) {
            $bg_color = ($i % 2 == 0) ? "#526c7a" : "transparent";
            $text_color = "white";
            $history_style = "none";

            if (isset($item['status'])) {
                $text_color = "#99ff99";
                $history_style = "block";
            }

            $sql = "SELECT hospital_name FROM sdn_hospital WHERE hospital_code=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$item['hpatcode']]); // Ensure you use $item here
            $hpatcode_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $results_html .= '<div id="search-sub-div" class="search-sub-div" style="background: '. $bg_color .'">';
            $results_html .= ' <div id="upper-part-sub-div">';
            $results_html .=    '<h1 id="pat-id-h1" class="search-sub-code">'. $item['hpercode'] .'</h1>';
            $results_html .=      '<div>';
            $results_html .=          '<h1>'. $item['patbdate'] .'</h1>';
            $results_html .=           '<span class="fa-solid fa-user"></span>';
            $results_html .=     ' </div>';
            $results_html .= '</div>';
            $results_html .= ' <div id="lower-part-sub-div">';
            $results_html .=     ' <h3 id="pat-name">'. $item['patlast'] . ", " . $item['patfirst'] . " " . $item['patmiddle'] .'</h3>';
            $results_html .=      '<div id="pat-history-div">';
                                    if($hpatcode_data['hospital_name'] == $_SESSION['hospital_name'] || $_SESSION['hospital_name'] == 'Bataan General Hospital and Medical Center'){
                                    $results_html .=        '<h3 class="pat-history-class" id="pat-history" style="display:'.$history_style.';"> <i class="fa-solid fa-clock-rotate-left"></i> </h3>';
                                    }
            $results_html .=        '<h3 id="pat-stat" style="color: '.$text_color.';">' . (isset($item['status']) ? "Status: Referred-" . $item['status'] : "Status: Not yet referred") . '</h3>';
            $results_html .=      '</div>';
            $results_html .=  '</div>';
            $results_html .= '<label id="reg-at-lbl">Registered at: '. $hpatcode_data['hospital_name'] .'</label>';
            $results_html .= '</div>';

            $i += 1;
        }

        $pagination_html = '';
        if ($total_pages > 1) {
            $pagination_html .= '<div class="pagination">';
            if ($current_page > 1) {
                $pagination_html .= '<a href="#" data-page="'.($current_page - 1).'">&laquo; Previous</a>';
            }

            for ($page = 1; $page <= $total_pages; $page++) {
                if ($page == $current_page) {
                    $pagination_html .= '<span class="current-page">'.$page.'</span>';
                } else {
                    $pagination_html .= '<a href="#" data-page="'.$page.'">'.$page.'</a>';
                }
            }

            if ($current_page < $total_pages) {
                $pagination_html .= '<a href="#" data-page="'.($current_page + 1).'">Next &raquo;</a>';
            }
            $pagination_html .= '</div>';
        }

        // Return the results and pagination as JSON
        echo json_encode(['results' => $results_html, 'pagination' => $pagination_html]);

        
    }else{
        echo json_encode(['results' => $results_html, 'pagination' => $pagination_html]);
    }
?>