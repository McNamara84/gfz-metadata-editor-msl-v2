<?php
/**
 *
 * This script handles sending feedback emails using PHPMailer.
 *
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include 'settings.php';

/**
 * Sends a feedback email containing positive and negative feedback.
 *
 * @param string $feedbackQuestion1 The first Question of feedback.
 * @param string $feedbackQuestion2 The second Question of feedback.
 * @param string $feedbackQuestion3 The third Question of feedback.
 * @param string $feedbackQuestion4 The fourth Question of feedback.
 * @param string $feedbackQuestion5 The fifth Question of feedback.
 * @param string $feedbackQuestion6 The sixth Question of feedback.
 * @param string $feedbackQuestion7 The seventh Question of feedback.
 *
 * @return void
 */
function sendFeedbackMail($feedbackQuestion1, $feedbackQuestion2, $feedbackQuestion3, $feedbackQuestion4, $feedbackQuestion5, $feedbackQuestion6, $feedbackQuestion7)

{
    global $smtpHost, $smtpPort, $smtpUser, $smtpPassword, $smtpSender, $feedbackAddress;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable SMTPS encryption
        $mail->Port = $smtpPort;
        $mail->setFrom($smtpSender, 'Feedback System');
        $mail->addAddress($feedbackAddress);

        // Email content
        $mail->isHTML(false);
        $mail->Subject = 'Neues Feedback zu MDE2-MSL';
        $mail->Body =
            "Which functions of the new metadata editor do you find particularly helpful?:\n" . $feedbackQuestion1
            . "\n\nIs there a particular design or user interface change that you like?:\n" . $feedbackQuestion2
            . "\n\nWhat do you find positive about the usability of the new editor?:\n" . $feedbackQuestion3
            . "\n\nWhich functions of the new editor do you find difficult to use?:\n" . $feedbackQuestion4
            . "\n\nAre there any aspects of the user interface that you find confusing or annoying?:\n" . $feedbackQuestion5
            . "\n\nDo you miss certain functions in the new metadata editor?:\n" . $feedbackQuestion6
            . "\n\nIs there a specific improvement you would like to see?:\n" . $feedbackQuestion7;

        $mail->send();
        echo 'Feedback sent successfully.';
    } catch (Exception $e) {
        echo "Error sending email: {$mail->ErrorInfo}";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    $feedbackQuestion1 = $_POST['feedbackQuestion1'] ?? '';
    $feedbackQuestion2 = $_POST['feedbackQuestion2'] ?? '';
    $feedbackQuestion3 = $_POST['feedbackQuestion3'] ?? '';
    $feedbackQuestion4 = $_POST['feedbackQuestion4'] ?? '';
    $feedbackQuestion5 = $_POST['feedbackQuestion5'] ?? '';
    $feedbackQuestion6 = $_POST['feedbackQuestion6'] ?? '';
    $feedbackQuestion7 = $_POST['feedbackQuestion7'] ?? '';


    sendFeedbackMail($feedbackQuestion1, $feedbackQuestion2, $feedbackQuestion3, $feedbackQuestion4, $feedbackQuestion5, $feedbackQuestion6, $feedbackQuestion7);
}
