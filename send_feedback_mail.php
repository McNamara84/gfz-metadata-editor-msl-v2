<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
include 'settings.php';

function sendFeedbackMail($feedbackTextPositiv, $feedbackTextNegativ)
{
    global $smtpHost, $smtpPort, $smtpUser, $smtpPassword, $smtpSender, $feedbackAdress;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SMTPS aktivieren
        $mail->Port = $smtpPort;
        $mail->setFrom($smtpSender, 'Feedback System');
        $mail->addAddress($feedbackAdress);
        // Inhalt der E-Mail
        $mail->isHTML(false);
        $mail->Subject = 'Neues Feedback zu MDE2-MSL';
        $mail->Body = "Positives Feedback:\n" . $feedbackTextPositiv . "\n\nNegatives Feedback:\n" . $feedbackTextNegativ;

        $mail->send();
        echo 'Feedback erfolgreich gesendet.';
    } catch (Exception $e) {
        echo "Fehler beim Senden der E-Mail: {$mail->ErrorInfo}";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedbackTextPositiv = $_POST['feedbackTextPositiv'] ?? '';
    $feedbackTextNegativ = $_POST['feedbackTextNegativ'] ?? '';
    sendFeedbackMail($feedbackTextPositiv, $feedbackTextNegativ);
}