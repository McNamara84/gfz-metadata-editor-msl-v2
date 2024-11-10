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

require_once './settings.php';
require_once 'formgroups/save_resourceinformation_and_rights.php';
require_once 'formgroups/save_authors.php';
require_once 'formgroups/save_contactperson.php';
require_once 'formgroups/save_freekeywords.php';
require_once 'formgroups/save_contributors.php';
require_once 'formgroups/save_descriptions.php';
require_once 'formgroups/save_thesauruskeywords.php';
require_once 'formgroups/save_spatialtemporalcoverage.php';
require_once 'formgroups/save_relatedwork.php';
require_once 'formgroups/save_fundingreferences.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/**
 * Save all form data to the database
 * Each function handles a specific part of the metadata
 * 
 * @param mysqli $connection Database connection
 * @param array $_POST Form data
 * @return int ID of the saved resource
 */
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

/**
 * Get XML content from API
 * Uses the API endpoint that returns XML
 * 
 * @var string $base_url Base URL of the application
 * @var string $url Complete API endpoint URL
 * @var string $xml_content Retrieved XML content
 */
$base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$url = $base_url . "api/v2/dataset/export/" . $resource_id . "/all";
$xml_content = file_get_contents($url);

/**
 * Send email with XML attachment using PHPMailer
 * Configuration is loaded from settings.php
 * 
 * @var PHPMailer $mail PHPMailer instance
 */
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $smtpPort;

    // Recipients
    $mail->setFrom($smtpSender);
    $mail->addAddress($xmlSubmitAdress);

    // Attachments
    $mail->addStringAttachment($xml_content, "dataset_" . $resource_id . ".xml");

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Dataset Submission (ID: ' . $resource_id . ')';
    $mail->Body = 'A new dataset has been submitted.<br>Dataset ID: ' . $resource_id;
    $mail->AltBody = 'A new dataset has been submitted. Dataset ID: ' . $resource_id;

    $mail->send();
    echo "Dataset saved and sent successfully";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}