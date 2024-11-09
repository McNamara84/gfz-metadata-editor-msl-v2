<?php

function connectDb()
{
    $host = "localhost";
    $username = "DB_NUTZER";
    $password = "DB_PASSWORT";
    $database = "DB_NAME";
    $conn = new mysqli($host, $username, $password, $database);
    return $conn;
}

function getApiKey()
{
    // Google Maps API Key
    $apiKeyGoogleMaps = 'xxxxxxxxxxxxxxxxxxxxxxxx-xxxxxxxxxxxxxx';
    // API-Key als JSON zurückgeben
    echo json_encode(['apiKey' => $apiKeyGoogleMaps]);
}

// Prüfe, ob die Datei direkt über eine HTTP-Anfrage aufgerufen wird
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        getApiKey();
    }
}

$connection = connectDb();

// API Key für https://timezonedb.com/
$apiKeyTimezone = 'xxxxxxxxxxxx';
// maximale Anzahl der eingebbaren Titel
$maxTitles = 2;
// URL zur Quelle mit sämtlichen Laboren für MSL
$mslLabsUrl = 'https://raw.githubusercontent.com/UtrechtUniversity/msl_vocabularies/main/vocabularies/labs/labnames.json';
// Anzeige des Feedback-Links (true für Anzeige, false für keine Anzeige)
$showFeedbackLink = true;
// Einstellungen für Mailversand mit SMTP
$smtpHost = 'xxxxxx';
$smtpPort = 465;
$smtpUser = 'xxxxxxxxxxx';
$smtpPassword = 'xxxxxxxxxxxx';
$smtpSender = 'xxxxxxxxxxxx';
// Zieladresse für Feedback
$feedbackAdress = 'xxxxxxxx@xxxxxxxx';
// Zieladresse für XML-Submit
$xmlSubmitAdress = 'xxxxxxxx@xxxxxxxx';