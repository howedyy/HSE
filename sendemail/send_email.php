<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars($_POST['name']);
    $email   = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    $mail = new PHPMailer(true);

    try {
        //$mail->isSMTP();
        //$mail->SMTPDebug = 0;
        //$mail->Host       = 'mail.edaraproperty.net';
        //$mail->SMTPAuth   = true;
        //$mail->Username   = '@edaraproperty.net';
        //$mail->Password   = '-';
        //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        //$mail->Port       = 465;
		
		$mail->isSMTP();
		$mail->SMTPDebug = 2;
		$mail->Host       = 'smtp.gmail.com';
		$mail->SMTPAuth   = true;
		$mail->Username   = 'edarasec@gmail.com';  // Your Gmail address
		$mail->Password   = 'vxwgihbbcuhvmimc';    // Gmail App Password (not your normal password)
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
		$mail->Port       = 465;

        $mail->setFrom('shehab.sayed@edaraproperty.net', 'HSE');
        $mail->addReplyTo('shehab.sayed@edaraproperty.net', 'HSE');
        $mail->addAddress('shehab.sayed@edaraproperty.net');

        $mail->isHTML(true);
        $mail->Subject = $subject;

        
        $mail->Body = "
        <table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; font-family: Arial, sans-serif;'>
            <tr>
                <th style='background-color: #f2f2f2; text-align: left;'>Name</th>
                <td>{$name}</td>
            </tr>
            <tr>
                <th style='background-color: #f2f2f2; text-align: left;'>Email</th>
                <td>{$email}</td>
            </tr>
            <tr>
                <th style='background-color: #f2f2f2; text-align: left;'>Message</th>
                <td>" . nl2br($message) . "</td>
            </tr>
        </table>";

        $mail->send();

        $_SESSION['msg'] = "✅ Message sent successfully!";
        $_SESSION['type'] = "success";
    } catch (Exception $e) {
        $_SESSION['msg'] = "❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        $_SESSION['type'] = "error";
    }

    header("Location: contact_form.php");
    exit;
}
