<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // PHPMailer autoload

function sendKeyEmail($recipientEmail, $keyCode, $role, $expiresAt = null) {
    $mail = new PHPMailer(true);

     try {
                  $mail->isSMTP();                                             // Send using SMTP
                        $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server to send through
                        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                        $mail->Username   = 'catherinemaemauricio.bsit@gmail.com'; // Your Gmail
                        $mail->Password   = 'togd lnqm uyvz trwc';                 // Your App Password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                        $mail->Port       = 587;      
                        
        // Recipients
        $mail->setFrom('your-email@gmail.com', 'Prison Management System');
        $mail->addAddress($recipientEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your " . ucfirst($role) . " Registration Key";

        $expiryText = $expiresAt ? "This key will expire on <b>$expiresAt</b>." : "This key does not expire immediately.";

        $mail->Body = "
            <h3>Hello,</h3>
            <p>You have been provided a registration key for <strong>" . ucfirst($role) . "</strong> access.</p>
            <p><b>Key Code:</b> $keyCode</p>
            <p>$expiryText</p>
            <p>Please go to <a href='https://yourdomain.com/auth/key.php'>this page</a> 
            and enter the key to complete your registration.</p>
            <br>
            <p>Best Regards,<br>Prison Management System</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Mailer Error: {$mail->ErrorInfo}";
    }
}
