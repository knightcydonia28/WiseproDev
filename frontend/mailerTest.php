<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer.php';
?>


<?php

if ( isset($_POST['buttonName']) ) {
        $myFile = $_POST['myfile'];
        $mail = new PHPMailer(); 
    try {
        //Recipients
        $mail->setFrom('administration@wisepro.com', 'Wisetek Providers');
        $mail->addReplyTo('administration@wisepro.com', 'Wisetek Providers');

        $mail->addAddress('tombaham@yahoo.com');     //Add a recipient

        //Attachments
        $mail->addAttachment($myFile);         //Add attachments

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Here is the subject';
        $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();

        echo "<h2>Mail succeed</h2>";         

    } catch (Exception $e) {
        echo "<h2>Mail fail</h2>";  
    }
}
else 
{
    echo 
    "
    <form id=\"test_form\" action=\"mailerTest.php\" method=\"POST\">
        <label for=\"myfile\"><b>Select a file:</b></label>
        <input type=\"file\" id=\"myfile\" name=\"myfile\"> 
        <button class=\"btn\" type=\"submit\" name=\"buttonName\">Email To Me</button>
    </form>
    ";
}

?>





