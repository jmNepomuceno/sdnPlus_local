<?php
    include("../database/connection2.php");
    session_start();
    
    include("../Includes/PHPMailer-6.8.1/src/PHPMailer.php");
    include("../Includes/PHPMailer-6.8.1/PHPMailer-6.8.1/src/SMTP.php"); // Optional for SMTP support
    include("../Includes/PHPMailer-6.8.1/PHPMailer-6.8.1/src/Exception.php"); // Optional for error handling

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    if($_POST['validity'] === 'false'){
        // check for duplicate hospital registration.
        $sql = "SELECT hospital_code FROM sdn_hospital WHERE hospital_code= '". $_POST['hospital_code'] ."' ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // echo count($data);

        if(count($data) >= 1){
            echo "invalid";
        }else{
            echo "valid";
        }
        
    }else{
        $mail = new PHPMailer(true);

        function generateOTP($secretKey, $counter) {
            $hash = hash_hmac('sha1', pack('N*', 0) . pack('N*', $counter), hex2bin($secretKey));
            $offset = hexdec(substr($hash, -1)) & 0xF;
            $otp = (hexdec(substr($hash, $offset * 2, 8)) & 0x7FFFFFFF) % 900000 + 100000;
            return str_pad($otp, 6, '0', STR_PAD_LEFT);
        }
        
        // Function to generate a random secret key
        function generateRandomKey($length) {
            $randomBytes = random_bytes($length);
            return bin2hex($randomBytes);
        }
        
        $secretKeyLength = 32;
        $secretKey = generateRandomKey($secretKeyLength);
        
        // Generate OTP using the randomly generated secret key and a counter
        $counter = 123456; // Example counter value
        $OTP = generateOTP($secretKey, $counter);
    
        // Initialize a counter for letters
        $letterCount = 0;
    
        // Iterate through each character in the string
        for ($i = 0; $i < strlen($OTP); $i++) {
            // Check if the character is a letter
            if (ctype_alpha($OTP[$i])) {
                $letterCount++;
            }
        }
        
        if(strlen($OTP) <= 5){
            while (strlen($OTP) <= 5){
                $OTP = generateOTP($secretKey, $counter);
            }
        }
    
        $hospital_name = $_POST['hospital_name'];
        $hospital_code = $_POST['hospital_code'];
        $_SESSION['hospital_code'] = $_POST['hospital_code'];
        
        $region = $_POST['region'];
        $province = $_POST['province'];
        $municipality = $_POST['municipality'];
        $barangay = $_POST['barangay'];
        $zip_code = $_POST['zip_code'];
    
        $email = $_POST['email'];
        $landline_no = $_POST['landline_no'];
        $hospital_mobile_no = $_POST['hospital_mobile_no'];
    
        $hospital_director = $_POST['hospital_director'];
        $hospital_director_mobile_no = $_POST['hospital_director_mobile_no'];
     
        $point_person = $_POST['point_person'];
        $point_person_mobile_no = $_POST['point_person_mobile_no'];
    
        $sql = "INSERT INTO sdn_hospital (hospital_code, hospital_name, hospital_region_code, hospital_province_code, hospital_municipality_code, hospital_barangay_code, hospital_zip_code, hospital_email, hospital_landline, hospital_mobile, hospital_director, hospital_director_mobile, hospital_point_person, hospital_point_person_mobile, hospital_OTP)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    
        $stmt = $pdo->prepare($sql);
    
        $stmt->bindParam(1, $hospital_code, PDO::PARAM_INT);
        $stmt->bindParam(2, $hospital_name, PDO::PARAM_STR);
        $stmt->bindParam(3, $region, PDO::PARAM_STR);
        $stmt->bindParam(4, $province, PDO::PARAM_STR);
        $stmt->bindParam(5, $municipality, PDO::PARAM_STR);
        $stmt->bindParam(6, $barangay, PDO::PARAM_STR);
        $stmt->bindParam(7, $zip_code, PDO::PARAM_STR);
        $stmt->bindParam(8, $email, PDO::PARAM_STR);
        $stmt->bindParam(9, $landline_no, PDO::PARAM_STR);
        $stmt->bindParam(10, $hospital_mobile_no, PDO::PARAM_STR);
        $stmt->bindParam(11, $hospital_director, PDO::PARAM_STR);
        $stmt->bindParam(12, $hospital_director_mobile_no, PDO::PARAM_STR);
        $stmt->bindParam(13, $point_person, PDO::PARAM_STR);
        $stmt->bindParam(14, $point_person_mobile_no, PDO::PARAM_STR);
        $stmt->bindParam(15, $OTP, PDO::PARAM_INT);
    
        $stmt->execute([$hospital_code, $hospital_name, $region, $province, $municipality, $barangay, $zip_code, $email, $landline_no, $hospital_mobile_no, $hospital_director, $hospital_director_mobile_no, $point_person, $point_person_mobile_no, $OTP]);
    
        //SENDING EMAIL
        try {                
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF; // Set the debugging level (options: DEBUG_OFF, DEBUG_CLIENT, DEBUG_SERVER)
            $mail->isSMTP(); // Set mailer to use SMTP
            $mail->AuthType = 'PLAIN'; 
            $mail->Host = 'smtp.gmail.com'; // Specify your SMTP server
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'sdnplus.bghmc@gmail.com'; // SMTP username 
            $mail->Password = 'apiknomansslprnq'; // SMTP password
            $mail->SMTPSecure = 'tls'; // Enable TLS encryption, 'ssl' also accepted
            $mail->Port = 587; // TCP port to connect to
    
            //Recipients
            
            $mail->addAddress($email);
            //Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'OTP Verification BGHMC-SDN';
            $mail->Body =  $OTP . " is your verification code. For your protection, do not share this code with anyone." . '<br>' . 
                            'After verifying your account, you can now proceed on creating and authorization of the user account(s) '; // OTP value from sdn_reg
            $mail->AltBody = 'This is the plain text message body';
    
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }
  
?>