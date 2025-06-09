<?php 
    session_start();
    include('../database/connection2.php');
    
    $_SESSION['current_content'] = "default";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/default_view.css">
</head>
<body>
        <div id="default-div" class="container">
            <img id="sdn-main-bg" src="../assets/login_imgs/main_bg.png" alt="sdn-main-bg" />
            <h1>Service Delivery Network Plus (SDN+)</h1>
            <h3>Bataan General Hospital and Medical Center</h3>

            <div id="license-div">
                <p id="copy-right">Philippine Copyright © 2018 Dr. Glory V. Baltazar</p>
                <p>This software program is protected by the Republic of the Philippines copyright laws. Reproduction and distribution of the software without prior written permission of the author is prohibited.</p>
                <p>If you wish to use the software for commercial or other purposes, please contact us at bgh_bataan2005@yahoo.com.ph.</p>
            </div>
        </div>

        <div id="stopwatch-sub-div" style="display:none">
            Processing: <span class="stopwatch"></span>
        </div>

    <!-- <script src="../js/main_style.js"></script> -->

    <script>
    </script>
</body>
</html>