<?php
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('Access-Control-Allow-Origin: *');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    $counter = 0;

    while (true) {
        echo "data: " . json_encode(["message" => "Update #$counter"]) . "\n\n";
        ob_flush();
        flush();

        $counter++;

        if ($counter >= 10) { // Stop after 10 updates
            break;
        }

        sleep(1);
    }

    
?>