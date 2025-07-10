<?php
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Enable verbose debug output
    $mail->SMTPDebug = 2;                      // Set to 0 to disable, or 2 for detailed output
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'zuscoffeenotification@gmail.com';    
    $mail->Password   = 'kmcr dfjx jmrj lwph';         // <-- Replace with your actual password or app password
    $mail->SMTPSecure = 'tls';                   // Or 'ssl' depending on your mail server
    $mail->Port       = 587;                     // Or 465 if using SSL

    $mail->setFrom('zuscoffeenotification@gmail.com', 'Test Sender');
    $mail->addAddress('enricolouisvelez@gmail.com', 'Test Recipient'); // <-- Replace with a test recipient

    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = '<b>This is a test email sent from PHPMailer!</b>';
    $mail->AltBody = 'This is a test email sent from PHPMailer!';

    $mail->send();
    echo 'Email has been sent successfully.';
} catch (Exception $e) {
    echo "Email could not be sent. Error: {$mail->ErrorInfo}";
}