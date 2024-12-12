<?php
/**
 * Script to save dataset metadata and send it as XML via email
 * 
 * This script saves all form data to the database and sends the resulting
 * XML file as an email attachment to a preconfigured email address.
 * It uses the same save functions as save_data.php but sends an email
 * instead of triggering a download.
 * 
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Buffer output
ob_start();

// Include required files
require_once './settings.php';
require_once 'save/formgroups/save_resourceinformation_and_rights.php';
require_once 'save/formgroups/save_authors.php';
require_once 'save/formgroups/save_contactperson.php';
require_once 'save/formgroups/save_freekeywords.php';
require_once 'save/formgroups/save_contributors.php';
require_once 'save/formgroups/save_descriptions.php';
require_once 'save/formgroups/save_thesauruskeywords.php';
require_once 'save/formgroups/save_spatialtemporalcoverage.php';
require_once 'save/formgroups/save_relatedwork.php';
require_once 'save/formgroups/save_fundingreferences.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'vendor/phpmailer/phpmailer/src/Exception.php';
require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once 'vendor/phpmailer/phpmailer/src/SMTP.php';

try {
    // Save all form components
    $resource_id = saveResourceInformationAndRights($connection, $_POST);
    saveAuthors($connection, $_POST, $resource_id);
    saveContactPerson($connection, $_POST, $resource_id);
    saveContributors($connection, $_POST, $resource_id);
    saveDescriptions($connection, $_POST, $resource_id);
    saveThesaurusKeywords($connection, $_POST, $resource_id);
    saveFreeKeywords($connection, $_POST, $resource_id);
    saveSpatialTemporalCoverage($connection, $_POST, $resource_id);
    saveRelatedWork($connection, $_POST, $resource_id);
    saveFundingReferences($connection, $_POST, $resource_id);

    // Get XML content from API
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $base_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    $url = $base_url . "/api/v2/dataset/export/" . $resource_id . "/all";

    // Get XML content with error handling
    $xml_content = file_get_contents($url);

    if ($xml_content === FALSE) {
        throw new Exception("Failed to retrieve XML content from API");
    }

    // Send email with XML attachment
    $mail = new PHPMailer(true);

    // Capture SMTP debugging output
    $debugging_output = '';
    $mail->Debugoutput = function ($str, $level) use (&$debugging_output) {
        $debugging_output .= "$str\n";
    };

    // Server settings
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $smtpPort;
    $mail->CharSet = 'UTF-8';

    // Recipients
    $mail->setFrom($smtpSender);
    $mail->addAddress($xmlSubmitAddress);

    // Attachments
    $mail->addStringAttachment($xml_content, "dataset_" . $resource_id . ".xml");

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Dataset Submission (ID: ' . $resource_id . ')';
    $mail->Body = 'A new dataset has been submitted.<br>Dataset ID: ' . $resource_id;
    $mail->AltBody = 'A new dataset has been submitted. Dataset ID: ' . $resource_id;

    // Send email
    if (!$mail->send()) {
        throw new Exception("Email could not be sent: " . $mail->ErrorInfo);
    }

    // Clear any output buffers
    ob_clean();

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Dataset saved and email sent successfully'
    ]);

} catch (Exception $e) {
    // Clear any output buffers
    ob_clean();

    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => isset($debugging_output) ? $debugging_output : ''
    ]);
}

// End output buffering
ob_end_flush();