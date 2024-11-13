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
 * @param string $feedbackTextPositive The positive feedback text.
 * @param string $feedbackTextNegative The negative feedback text.
 *
 * @return void
 */
function sendFeedbackMail($feedbackTextPositive, $feedbackTextNegative)
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
        $mail->Subject = 'New Feedback for MDE2-MSL';
        $mail->Body = "Positive Feedback:\n" . $feedbackTextPositive . "\n\nNegative Feedback:\n" . $feedbackTextNegative;

        $mail->send();
        echo 'Feedback sent successfully.';
    } catch (Exception $e) {
        echo "Error sending email: {$mail->ErrorInfo}";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedbackTextPositive = $_POST['feedbackTextPositiv'] ?? '';
    $feedbackTextNegative = $_POST['feedbackTextNegativ'] ?? '';
    sendFeedbackMail($feedbackTextPositive, $feedbackTextNegative);
}
