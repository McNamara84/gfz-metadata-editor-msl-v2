<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
include 'settings.php';

function sendFeedbackMail($feedbackQuestion1, $feedbackQuestion2, $feedbackQuestion3, $feedbackQuestion4, $feedbackQuestion5, $feedbackQuestion6, $feedbackQuestion7)
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
        $mail->Body =
            "Which functions of the new metadata editor do you find particularly helpful?:\n" . $feedbackQuestion1
            . "\n\nIs there a particular design or user interface change that you like?:\n" . $feedbackQuestion2
            . "\n\nWhat do you find positive about the usability of the new editor?:\n" . $feedbackQuestion3
            . "\n\nWhich functions of the new editor do you find difficult to use?:\n" . $feedbackQuestion4
            . "\n\nAre there any aspects of the user interface that you find confusing or annoying?:\n" . $feedbackQuestion5
            . "\n\nDo you miss certain functions in the new metadata editor?:\n" . $feedbackQuestion6
            . "\n\nIs Is there a specific improvement you would like to see?:\n" . $feedbackQuestion7;

        $mail->send();
        echo 'Feedback erfolgreich gesendet.';
    } catch (Exception $e) {
        echo "Fehler beim Senden der E-Mail: {$mail->ErrorInfo}";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging: Überprüfen Sie, ob Daten ankommen
    error_log(print_r($_POST, true));
    $feedbackQuestion1 = $_POST['feedbackQuestion1'] ?? '';
    $feedbackQuestion2 = $_POST['feedbackQuestion2'] ?? '';
    $feedbackQuestion3 = $_POST['feedbackQuestion3'] ?? '';
    $feedbackQuestion4 = $_POST['feedbackQuestion4'] ?? '';
    $feedbackQuestion5 = $_POST['feedbackQuestion5'] ?? '';
    $feedbackQuestion6 = $_POST['feedbackQuestion6'] ?? '';
    $feedbackQuestion7 = $_POST['feedbackQuestion7'] ?? '';


    sendFeedbackMail($feedbackQuestion1, $feedbackQuestion2, $feedbackQuestion3, $feedbackQuestion4, $feedbackQuestion5, $feedbackQuestion6, $feedbackQuestion7);
}