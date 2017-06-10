<?php

$host = "localhost"; // SMTP host
$username = ""; //SMTP username
$password = ""; // SMTP password

require("includes/mail/class.phpmailer.php");

$key = 0;

$to = $_GET['to'];
$name = $_GET['who'];
$email_subject = $_GET['subj'];
$Email_msg = $_GET['bdy'];
$email_from = $_POST['frm'];

$mail = new PHPMailer();

$mail->IsSMTP();                                   // send via SMTP
$mail->Host     = $host; // SMTP servers
$mail->SMTPAuth = true;     // turn on SMTP authentication
$mail->Username = $username;  // SMTP username
$mail->Password = $password; // SMTP password

$mail->From     = $email_from;
$mail->FromName = $name;
$mail->AddAddress($to);  
$mail->AddReplyTo($email_from,$who);

$mail->WordWrap = 50;                              // set word wrap
$mail->IsHTML(true);                               // send as HTML

$mail->Subject  =  $email_subject;
$mail->Body     =  $Email_msg;
$mail->AltBody  =  $Email_msg;

if(!$mail->Send())
{
   echo "Message was not sent. Error: " . $mail->ErrorInfo;
}
else
{
	echo "Message to $Email_to has been sent";
}
?>
 
