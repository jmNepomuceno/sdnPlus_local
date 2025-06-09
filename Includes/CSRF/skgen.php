<?php

function generateOTP($secretKey, $counter) {
    $hash = hash_hmac('sha1', pack('N*', 0) . pack('N*', $counter), hex2bin($secretKey));
    $offset = hexdec(substr($hash, -1)) & 0xF;
    $otp = (hexdec(substr($hash, $offset * 2, 8)) & 0x7FFFFFFF) % 1000000;
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
$otp = generateOTP($secretKey, $counter);

// echo "Generated Secret Key: " . $secretKey . PHP_EOL."</br>";
// echo "Generated OTP: " . $otp . PHP_EOL;

?>
