<?php
    include("../database/connection2.php");
    session_start();
    
    // include("../Includes/PHPMailer-6.8.1/src/PHPMailer.php");
    // include("../Includes/PHPMailer-6.8.1/PHPMailer-6.8.1/src/SMTP.php"); // Optional for SMTP support
    // include("../Includes/PHPMailer-6.8.1/PHPMailer-6.8.1/src/Exception.php"); // Optional for error handling

    // use PHPMailer\PHPMailer\PHPMailer;
    // use PHPMailer\PHPMailer\SMTP;
    // use PHPMailer\PHPMailer\Exception;

    $id = (int)$_POST['id'];
    $cipher = $_POST['cipher'];

    // $mail = new PHPMailer(true);

    $sql = "SELECT hospital_email FROM sdn_hospital WHERE hospital_code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $email = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "UPDATE sdn_hospital SET hospital_autKey=? WHERE hospital_code=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cipher, $id]);
    echo "success";
    //SENDING EMAIL
    // try {                
    //     //Server settings
    //     $mail->SMTPDebug = SMTP::DEBUG_OFF; // Set the debugging level (options: DEBUG_OFF, DEBUG_CLIENT, DEBUG_SERVER)
    //     $mail->isSMTP(); // Set mailer to use SMTP
    //     $mail->AuthType = 'PLAIN'; 
    //     $mail->Host = 'smtp.gmail.com'; // Specify your SMTP server
    //     $mail->SMTPAuth = true; // Enable SMTP authentication
    //     $mail->Username = 'bataansdn123@gmail.com'; // SMTP username 
    //     $mail->Password = 'swcvfvzikdmezzak'; // SMTP password
    //     $mail->SMTPSecure = 'tls'; // Enable TLS encryption, 'ssl' also accepted
    //     $mail->Port = 587; // TCP port to connect to

    //     //Recipients
        
    //     $mail->addAddress($email['hospital_email']); // Add a recipient
    //     //Content
    //     $mail->isHTML(true); // Set email format to HTML
    //     $mail->Subject = 'Cipher Key for HCPN (SDN) Registration';
    //     $mail->Body =  $cipher . " is your cipher key code. For your protection, do not share this code with anyone." . '<br>' . 
    //                     'After verifying your account, you can now proceed on creating and authorization of the user account(s) '; // OTP value from sdn_reg
    //     $mail->AltBody = 'This is the plain text message body';

    //     $mail->send();
    //     echo 'Message has been sent';
    // } catch (Exception $e) {
    //     echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    // }

  
?>