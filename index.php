<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
include_once("settings.php");

// Funktion zur Generierung von Dropdown-Optionen aus der DB
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

// Dropdown-Auswahlmöglichkeiten generieren
$optionresourcentype = generateOptions($connection, "SELECT resource_name_id, resource_type_general FROM Resource_Type", "resource_name_id", "resource_type_general");
$optionlanguage = generateOptions($connection, "SELECT language_id, name FROM Language", "language_id", "name");
$optionrole = generateOptions($connection, "SELECT role_id, name FROM Role", "role_id", "name");
$optiontitle_type = generateOptions($connection, "SELECT title_type_id, name FROM Title_Type", "title_type_id", "name");
$optionrelation = generateOptions($connection, "SELECT relation_id, name FROM Relation", "relation_id", "name");
$optionidentifier_type = generateOptions($connection, "SELECT identifier_type_id, name FROM Identifier_Type", "identifier_type_id", "name");

if (isset($_GET['lang'])) {
    $userLanguage = $_GET['lang'];
} elseif (isset($_GET['auto'])) {
    $userLanguage = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (!in_array($userLanguage, ['en', 'de', 'fr'])) {
        $userLanguage = 'en';
    }
} else {
    // Standardmäßig Englisch, falls keine Sprache gesetzt ist
    $userLanguage = 'en';
}
// Sprache festlegen und Sprachdatei einbinden
$languageFile = "lang/" . $userLanguage . '.php';
if (!file_exists($languageFile)) {
    $languageFile = 'lang/en.php'; // Standardsprache = Englisch
    $userLanguage = 'en';
}
include $languageFile;

// HTML-Teile einbinden
include("header.html");
include("formgroups/resourceInformation.html");
include("formgroups/rights.html");
include("formgroups/authors.html");
include("formgroups/contactpersons.html");
include("formgroups/originatingLaboratory.html");
include("formgroups/contributors.html");
include("formgroups/descriptions.html");
include("formgroups/general.html");
include("formgroups/analogueModellingOfGeologicalProcesses.html");
include("formgroups/geochemistry.html");
include("formgroups/microsocopyAndTomography.html");
include("formgroups/paleomagnetism.html");
include("formgroups/rockAndMeltPhysics.html");
include("formgroups/thesaurusKeywords.html");
include("formgroups/freeKeywords.html");
include("formgroups/dates.html");
include("formgroups/spatialtemporalcoverage.html");
include("formgroups/relatedwork.html");
include("formgroups/fundingreference.html");
include("footer.html");

// Formularverarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include("save/save_data.php");
}
