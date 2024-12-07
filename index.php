<?php
/**
 * This script initializes the application, handles error reporting, sets up language preferences,
 * includes necessary HTML components, and processes form submissions.
 *
 */

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering
ob_start();

// Include settings and configurations
include_once("settings.php");

/**
 * Generates HTML <option> elements from database query results.
 *
 * @param mysqli  $conn      The MySQLi connection object.
 * @param string  $query     The SQL query to fetch data.
 * @param string  $idField   The field name to be used as the option value.
 * @param string  $nameField The field name to be used as the option display text.
 *
 * @return string The generated HTML <option> elements.
 */
function generateOptions($conn, $query, $idField, $nameField)
{
    $options = "";
    if ($stmt = $conn->prepare($query)) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $options .= "<option value='" . htmlspecialchars($row[$idField]) . "'>" . htmlspecialchars($row[$nameField]) . "</option>";
        }
        $stmt->close();
    }
    return $options;
}

// Generate dropdown options
$optionresourcentype = generateOptions(
    $connection,
    "SELECT resource_name_id, resource_type_general FROM Resource_Type",
    "resource_name_id",
    "resource_type_general"
);
$optionlanguage = generateOptions(
    $connection,
    "SELECT language_id, name FROM Language",
    "language_id",
    "name"
);
$optionrole = generateOptions(
    $connection,
    "SELECT role_id, name FROM Role",
    "role_id",
    "name"
);
$optiontitle_type = generateOptions(
    $connection,
    "SELECT title_type_id, name FROM Title_Type",
    "title_type_id",
    "name"
);
$optionrelation = generateOptions(
    $connection,
    "SELECT relation_id, name FROM Relation",
    "relation_id",
    "name"
);
$optionidentifier_type = generateOptions(
    $connection,
    "SELECT identifier_type_id, name FROM Identifier_Type",
    "identifier_type_id",
    "name"
);

// Determine user language preference
if (isset($_GET['lang'])) {
    $userLanguage = $_GET['lang'];
} elseif (isset($_GET['auto'])) {
    $userLanguage = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (!in_array($userLanguage, ['en', 'de', 'fr'])) {
        $userLanguage = 'en';
    }
} else {
    // Default to English if no language is set
    $userLanguage = 'en';
}

// Set language and include language file
$languageFile = "lang/" . $userLanguage . '.php';
if (!file_exists($languageFile)) {
    $languageFile = 'lang/en.php'; // Default language is English
    $userLanguage = 'en';
}
include $languageFile;

// Include HTML components
include("header.html");
include("formgroups/resourceInformation.html");
include("formgroups/rights.html");
include("formgroups/authors.html");
include("formgroups/contactpersons.html");
include("formgroups/originatingLaboratory.html");
include("formgroups/contributors.html");
include("formgroups/descriptions.html");
include("formgroups/mslKeywords.html");
include("formgroups/thesaurusKeywords.html");
include("formgroups/freeKeywords.html");
include("formgroups/dates.html");
include("formgroups/spatialtemporalcoverage.html");
include("formgroups/relatedwork.html");
include("formgroups/fundingreference.html");
include("modals.html");
include("footer.html");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include("save/save_data.php");
}
