                                <?php 
require 'vendor/autoload.php';
require_once 'include/SecureMailer.php';
 
if ($_SERVER['REQUEST_METHOD']=="POST") {
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    try {
        $mailer = new SecureMailer();
        if($mailer->sendContactFormEmail($name, $email, $message)){
        echo "Message sent successfully";
     } else {
            echo "Message could not be sent";
     }
    } catch (Exception $e) {
        echo "Message could not be sent, Error: " . $e->getMessage();
    }
}
?>