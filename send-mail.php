<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader (created by composer, not included with PHPMailer)
require 'vendor/autoload.php';

 
if ($_SERVER['REQUEST_METHOD']=="POST") {
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

   $mail = new PHPMailer(true);
    
   
try {
    //Server settings
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'israelbabs59@gmail.com';                     //SMTP username
    $mail->Password   = 'uenb rrvr lyrl rzje';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`



     //Recipients
    $mail->setFrom('prodrivers@gmail.com', 'PRODRIVERS');
    $mail->addAddress('israelbabs59@gmail.com', 'Emmanuel');     //Add a recipient
    // $mail->addAddress('ellen@example.com');               //Name is optional
    // $mail->addReplyTo('info@example.com', 'Information');
    // $mail->addCC('cc@example.com');
    // $mail->addBCC('bcc@example.com');



     //Content
    //  $mail->isHTML(true);                                  //Set email format to HTML
     $mail->Subject = "New Contact Form Submission";
     $mail->Body    = "Name: $name\n".
                      "Email: $email\n".
                      "Message: $message";

     if($mail -> send()){
        echo "Message sent successfully";
     } else {
        echo "Message could not be sent, Error: " . $mail->ErrorInfo;
     }
    
    }  catch (Exception $e) {
        echo "Message could not be sent, Error: $mail->ErrorInfo";
    }

}
?>