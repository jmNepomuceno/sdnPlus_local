<?php
    header('Content-type: text/event-stream');
    header('Cache-Control: no-cache');
    ob_end_flush();

    while(true){
        echo 'event: message';
        echo 'data: asdfasfdsdfa';
        flush();

        if(connection_aborted()) break;

        sleep(2);
    }


    // function sendUpdate($data) {
    //     echo "asdf: $data\n\n";
    //     ob_flush();
    //     flush();
    // }

    // // Replace with your database connection details
    // $dsn = 'mysql:host=localhost;dbname=bghmc';
    // $username = 'root';
    // $password = 'S3rv3r';

    // try {
    //     $pdo = new PDO($dsn, $username, $password);
    // } catch (PDOException $e) {
    //     die('Connection failed: ' . $e->getMessage());
    // }

    // while (true) {
    //     $stmt = $pdo->prepare("SELECT COUNT(*) AS incoming_referrals FROM incoming_referrals WHERE status = 'Pending'");
    //     $stmt->execute();
    //     $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //     sendUpdate('Casdfount: ' . $result['count']);

    //     // Adjust the interval as needed (e.g., sleep for a few seconds)
    //     sleep(2);
    // }

?>