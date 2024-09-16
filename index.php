<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
include_once ("settings.php");

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
include ("header.html");
include ("formgroups/resourceInformation.html");
include ("formgroups/rights.html");
include ("formgroups/authors.html");
include ("formgroups/contactpersons.html");
include ("formgroups/originatingLaboratory.html");
include ("formgroups/contributors.html");
include ("formgroups/descriptions.html");
include ("formgroups/general.html");
include ("formgroups/analogueModellingOfGeologicalProcesses.html");
include ("formgroups/geochemistry.html");
include ("formgroups/MicrosocopyAndTomography.html");
include ("formgroups/paleomagnetism.html");
include ("formgroups/rockAndMeltPhysics.html");
include ("formgroups/thesaurusKeywords.html");
include ("formgroups/freeKeywords.html");
include ("formgroups/dates.html");
include ("formgroups/spatialtemporalcoverage.html");
include ("formgroups/relatedwork.html");
include ("formgroups/fundingreference.html");
include ("footer.html");

// Formularverarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Daten aus dem Formular an PHP-Variablen übergeben
    $doi = $_POST["doi"];
    $year = (int) $_POST["year"];
    $datecreated = $_POST["dateCreated"];
    $dateembargountil = $_POST["dateEmbargo"];
    $resourcetype = (int) $_POST["resourcetype"];
    $version = (float) $_POST["version"];
    $language = (int) $_POST["language"];
    $rights = (int) $_POST["Rights"];

    // Insert-Anweisung für Ressource Information
    $stmt = $connection->prepare("INSERT INTO Resource (doi, version, year, dateCreated, dateEmbargoUntil, Rights_rights_id, Resource_Type_resource_name_id, Language_language_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdissiii", $doi, $version, $year, $datecreated, $dateembargountil, $rights, $resourcetype, $language);
    $stmt->execute();
    $resource_id = $stmt->insert_id;
    $stmt->close();

    // Speichern aller Titles und Title Types
    if (isset($_POST['title'], $_POST['titleType']) && is_array($_POST['title']) && is_array($_POST['titleType'])) {
        $titles = $_POST['title'];
        $titleTypes = $_POST['titleType'];
        $len = count($titles);
        for ($i = 0; $i < $len; $i++) {
            $stmt = $connection->prepare("INSERT INTO Title (`text`, `Title_Type_fk`, `Resource_resource_id`) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $titles[$i], $titleTypes[$i], $resource_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Speichern aller Autoren
    $familynames = $_POST['familynames'] ?? [];
    $givennames = $_POST['givennames'] ?? [];
    $orcids = $_POST['orcids'] ?? [];
    $affiliations = $_POST['affiliation'] ?? [];
    $rorIds = $_POST['authorRorIds'] ?? [];

    // Hilfsfunktion zum Parsen der Affiliationsdaten
    function parseAffiliationData($data)
    {
        if (empty($data)) {
            return [];
        }

        // Versuche, als JSON zu dekodieren
        $decoded = json_decode($data, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Erfolgreich als JSON dekodiert
            if (is_array($decoded)) {
                return array_map(function ($item) {
                    return is_array($item) && isset($item['value']) ? trim($item['value']) : trim($item);
                }, $decoded);
            } else {
                return [trim($decoded)];
            }
        } else {
            // Kein valides JSON, behandle als einfachen String
            return [trim($data)];
        }
    }

    if (
        !empty($familynames) && !empty($givennames) && !empty($orcids) &&
        count($familynames) === count($givennames) && count($givennames) === count($orcids)
    ) {
        $len = count($familynames);

        for ($i = 0; $i < $len; $i++) {
            $familyname = trim($familynames[$i]);
            $givenname = trim($givennames[$i]);
            $orcid = trim($orcids[$i]);
            $affiliation_data = isset($affiliations[$i]) ? $affiliations[$i] : '';
            $rorId_data = isset($rorIds[$i]) ? $rorIds[$i] : '';

            // Prüfen, ob der Autor bereits existiert
            $stmt = $connection->prepare("SELECT author_id FROM Author WHERE orcid = ?");
            $stmt->bind_param("s", $orcid);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Autor existiert bereits, hole die ID
                $row = $result->fetch_assoc();
                $author_id = $row['author_id'];

                // Aktualisiere die Autorendaten
                $stmt = $connection->prepare("UPDATE Author SET familyname = ?, givenname = ? WHERE author_id = ?");
                $stmt->bind_param("ssi", $familyname, $givenname, $author_id);
                $stmt->execute();
            } else {
                // Neuen Autor einfügen
                $stmt = $connection->prepare("INSERT INTO Author (familyname, givenname, orcid) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $familyname, $givenname, $orcid);
                $stmt->execute();
                $author_id = $stmt->insert_id;
            }
            $stmt->close();

            // Resource_has_author Eintrag
            $stmt = $connection->prepare("INSERT IGNORE INTO Resource_has_Author (Resource_resource_id, Author_author_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $resource_id, $author_id);
            $stmt->execute();
            $stmt->close();

            if (!empty($affiliation_data)) {
                $affiliations_array = parseAffiliationData($affiliation_data);
                $rorIds_array = parseAffiliationData($rorId_data);

                foreach ($affiliations_array as $index => $affiliation_name) {
                    $rorId = $rorIds_array[$index] ?? null;
                    // Teilstring "https://ror.org/" von ROR-ID entfernen
                    $rorId = str_replace("https://ror.org/", "", $rorId);

                    // Affiliation einfügen oder ID abrufen
                    $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?) 
                                                  ON DUPLICATE KEY UPDATE affiliation_id = LAST_INSERT_ID(affiliation_id), 
                                                  rorId = COALESCE(VALUES(rorId), rorId)");
                    $stmt->bind_param("ss", $affiliation_name, $rorId);
                    $stmt->execute();
                    $affiliation_id = $stmt->insert_id;
                    $stmt->close();

                    // Prüfen, ob die Verknüpfung zwischen Autor und Affiliation bereits existiert
                    $stmt = $connection->prepare("SELECT 1 FROM Author_has_Affiliation 
                                                  WHERE Author_author_id = ? AND Affiliation_affiliation_id = ?");
                    $stmt->bind_param("ii", $author_id, $affiliation_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows == 0) {
                        // Neue Verknüpfung hinzufügen
                        $stmt = $connection->prepare("INSERT INTO Author_has_Affiliation (Author_author_id, Affiliation_affiliation_id) VALUES (?, ?)");
                        $stmt->bind_param("ii", $author_id, $affiliation_id);
                        $stmt->execute();
                    }
                    $stmt->close();
                }
            }
        }
    }

    // Speichern der Free Keywords
    $freekeywords = isset($_POST['freekeywords']) && is_array($_POST['freekeywords']) ? $_POST['freekeywords'] : [];
    foreach ($freekeywords as $keywordJSON) {
        $keywords = json_decode($keywordJSON, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Invalid JSON input: $keywordJSON");
            continue;
        }
        foreach ($keywords as $keywordObj) {
            if (!isset($keywordObj['value'])) {
                continue;
            }
            $keyword = $keywordObj['value'];
            $isCurated = 0;

            // Prüfen, ob das Keyword bereits existiert
            $stmt = $connection->prepare("SELECT free_keywords_id, isCurated FROM Free_Keywords WHERE free_keyword = ?");
            $stmt->bind_param("s", $keyword);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $freekeyword_id = $row['free_keywords_id'];
                $isCurated = $row['isCurated'];
            } else {
                // Falls das Keyword nicht existiert, neues Keyword einfügen
                $stmt = $connection->prepare("INSERT INTO Free_Keywords (`free_keyword`, `isCurated`) VALUES (?, ?)");
                $stmt->bind_param("si", $keyword, $isCurated);
                $stmt->execute();
                $freekeyword_id = $stmt->insert_id;
            }
            $stmt->close();

            // Prüfen, ob die Verknüpfung bereits existiert
            $stmt = $connection->prepare("SELECT * FROM Resource_has_Free_Keywords WHERE Resource_resource_id = ? AND Free_Keywords_free_keywords_id = ?");
            $stmt->bind_param("ii", $resource_id, $freekeyword_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                // Verknüpfung zwischen Resource und Free_Keywords einfügen, falls sie noch nicht existiert
                $stmt = $connection->prepare("INSERT INTO Resource_has_Free_Keywords (`Resource_resource_id`, `Free_Keywords_free_keywords_id`) VALUES (?, ?)");
                $stmt->bind_param("ii", $resource_id, $freekeyword_id);
                $stmt->execute();
            }
            $stmt->close();
        }
    }

    // Speichern aller Contact Persons
    if (
        isset($_POST['cpLastname'], $_POST['cpFirstname'], $_POST['cpPosition'], $_POST['cpEmail']) &&
        is_array($_POST['cpLastname']) && is_array($_POST['cpFirstname']) && is_array($_POST['cpPosition']) && is_array($_POST['cpEmail'])
    ) {
        $familynames = $_POST['cpLastname'];
        $givennames = $_POST['cpFirstname'];
        $positions = $_POST['cpPosition'];
        $emails = $_POST['cpEmail'];
        $websites = $_POST['cpOnlineResource'] ?? [];
        $affiliations = $_POST['cpAffiliation'] ?? [];
        $rorIds = $_POST['hiddenCPRorId'] ?? [];

        $len = count($familynames);
        for ($i = 0; $i < $len; $i++) {
            // Prüfen, ob die Kontaktperson bereits existiert (basierend auf E-Mail)
            $stmt = $connection->prepare("SELECT contact_person_id FROM Contact_Person WHERE email = ?");
            $stmt->bind_param("s", $emails[$i]);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Kontaktperson existiert bereits, aktualisiere die Daten
                $row = $result->fetch_assoc();
                $contact_person_id = $row['contact_person_id'];
                $websites[$i] = preg_replace('#^https?://#', '', $websites[$i]); // Protokoll abschneiden
                $stmt = $connection->prepare("UPDATE Contact_Person SET familyname = ?, givenname = ?, position = ?, website = ? WHERE contact_person_id = ?");
                $stmt->bind_param("ssssi", $familynames[$i], $givennames[$i], $positions[$i], $websites[$i], $contact_person_id);
            } else {
                // Neue Kontaktperson einfügen
                $websites[$i] = preg_replace('#^https?://#', '', $websites[$i]);  // Protokoll abschneiden
                $stmt = $connection->prepare("INSERT INTO Contact_Person (familyname, givenname, position, email, website) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $familynames[$i], $givennames[$i], $positions[$i], $emails[$i], $websites[$i]);
            }
            $stmt->execute();
            $contact_person_id = $stmt->insert_id ?: $contact_person_id;
            $stmt->close();

            // Resource_has_contact_person Eintrag
            $stmt = $connection->prepare("INSERT IGNORE INTO Resource_has_Contact_Person (Resource_resource_id, Contact_Person_contact_person_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $resource_id, $contact_person_id);
            $stmt->execute();
            $stmt->close();

            if (!empty($affiliations[$i])) {
                $affiliation_data = $affiliations[$i];
                $rorId_data = $rorIds[$i] ?? '';

                $affiliations_array = parseAffiliationData($affiliation_data);
                $rorIds_array = parseAffiliationData($rorId_data);

                foreach ($affiliations_array as $index => $affiliation_name) {
                    $rorId = $rorIds_array[$index] ?? null;
                    // Teilstring "https://ror.org/" von ROR-ID entfernen
                    $rorId = str_replace("https://ror.org/", "", $rorId);

                    // Affiliation einfügen oder aktualisieren
                    $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?) 
                                              ON DUPLICATE KEY UPDATE affiliation_id = LAST_INSERT_ID(affiliation_id), 
                                              rorId = COALESCE(VALUES(rorId), rorId)");
                    $stmt->bind_param("ss", $affiliation_name, $rorId);
                    $stmt->execute();
                    $affiliation_id = $stmt->insert_id;
                    $stmt->close();

                    // Prüfen, ob die Verknüpfung zwischen Kontaktperson und Affiliation bereits existiert
                    $stmt = $connection->prepare("SELECT 1 FROM Contact_Person_has_Affiliation 
                                              WHERE contact_Person_contact_person_id = ? AND Affiliation_affiliation_id = ?");
                    $stmt->bind_param("ii", $contact_person_id, $affiliation_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows == 0) {
                        // Neue Verknüpfung hinzufügen
                        $stmt = $connection->prepare("INSERT INTO Contact_Person_has_Affiliation (contact_Person_contact_person_id, Affiliation_affiliation_id) VALUES (?, ?)");
                        $stmt->bind_param("ii", $contact_person_id, $affiliation_id);
                        $stmt->execute();
                    }
                    $stmt->close();
                }
            }
        }
    }

    // Workaround für Speicherung von Roles, weil bisher  nur der name (z. B. Producer) übergeben wird und nicht die Role ID. TODO: Umbau auf Role ID
    $valid_roles = [];
    $stmt = $connection->prepare("SELECT role_id, name FROM Role");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $valid_roles[$row['name']] = $row['role_id'];
    }
    $stmt->close();

    // Speichern aller Contributor Persons
    if (
        isset($_POST['cbPersonLastname'], $_POST['cbPersonFirstname'], $_POST['cbORCID'], $_POST['cbAffiliation'], $_POST['cbPersonRoles']) &&
        is_array($_POST['cbPersonLastname']) && is_array($_POST['cbPersonFirstname']) && is_array($_POST['cbORCID']) &&
        is_array($_POST['cbAffiliation']) && is_array($_POST['cbPersonRoles'])
    ) {
        $cbPersonLastnames = $_POST['cbPersonLastname'];
        $cbPersonFirstnames = $_POST['cbPersonFirstname'];
        $cbORCIDs = $_POST['cbORCID'];
        $cbAffiliations = $_POST['cbAffiliation'];
        $cbPersonRoles = $_POST['cbPersonRoles'];
        $cbRorIds = $_POST['cbpRorIds'] ?? []; // ROR-IDs aus dem versteckten Feld

        $len = count($cbPersonLastnames);
        for ($i = 0; $i < $len; $i++) {
            // Prüfen, ob Contributor Person bereits existiert
            $stmt = $connection->prepare("SELECT contributor_person_id FROM Contributor_Person WHERE orcid = ?");
            $stmt->bind_param("s", $cbORCIDs[$i]);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Contributor Person existiert bereits, aktualisiere die Daten
                $row = $result->fetch_assoc();
                $contributor_person_id = $row['contributor_person_id'];
                $stmt = $connection->prepare("UPDATE Contributor_Person SET familyname = ?, givenname = ? WHERE contributor_person_id = ?");
                $stmt->bind_param("ssi", $cbPersonLastnames[$i], $cbPersonFirstnames[$i], $contributor_person_id);
            } else {
                // Neue Contributor Person einfügen
                $stmt = $connection->prepare("INSERT INTO Contributor_Person (familyname, givenname, orcid) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $cbPersonLastnames[$i], $cbPersonFirstnames[$i], $cbORCIDs[$i]);
            }
            $stmt->execute();
            $contributor_person_id = $stmt->insert_id ?: $contributor_person_id;
            $stmt->close();

            // Resource_has_contributor_person Eintrag
            $stmt = $connection->prepare("INSERT IGNORE INTO Resource_has_Contributor_Person (Resource_resource_id, Contributor_Person_contributor_person_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $resource_id, $contributor_person_id);
            $stmt->execute();
            $stmt->close();

            // Affiliation verarbeiten
            if (!empty($cbAffiliations[$i])) {
                $affiliation_name = parseAffiliationData($cbAffiliations[$i])[0];
                $rorId = isset($cbRorIds[$i]) ? parseAffiliationData($cbRorIds[$i])[0] : null;
                $rorId = $rorId ? str_replace("https://ror.org/", "", $rorId) : null;

                // Affiliation einfügen oder aktualisieren
                $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?) 
                                          ON DUPLICATE KEY UPDATE 
                                          name = VALUES(name),
                                          rorId = COALESCE(VALUES(rorId), rorId)");
                $stmt->bind_param("ss", $affiliation_name, $rorId);
                $stmt->execute();
                $affiliation_id = $stmt->insert_id ?: $connection->insert_id;
                $stmt->close();

                // Prüfen, ob die Verknüpfung bereits existiert
                $stmt = $connection->prepare("SELECT 1 FROM Contributor_Person_has_Affiliation 
                                          WHERE Contributor_Person_contributor_person_id = ? AND Affiliation_affiliation_id = ?");
                $stmt->bind_param("ii", $contributor_person_id, $affiliation_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 0) {
                    // Neue Verknüpfung hinzufügen
                    $stmt = $connection->prepare("INSERT INTO Contributor_Person_has_Affiliation (Contributor_Person_contributor_person_id, Affiliation_affiliation_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $contributor_person_id, $affiliation_id);
                    $stmt->execute();
                }
                $stmt->close();
            }

            // Rollen verarbeiten
            $currentRoles = $cbPersonRoles[$i];
            if (!is_array($currentRoles)) {
                $currentRoles = [$currentRoles];
            }

            // Zuerst alle bestehenden Rollen für diese Person löschen
            $stmt = $connection->prepare("DELETE FROM Contributor_Person_has_Role WHERE Contributor_Person_contributor_person_id = ?");
            $stmt->bind_param("i", $contributor_person_id);
            $stmt->execute();
            $stmt->close();

            // Dann die neuen Rollen einfügen
            foreach ($currentRoles as $role_name) {
                // Überprüfen, ob der Rollenname gültig ist und die entsprechende ID abrufen
                if (isset($valid_roles[$role_name])) {
                    $role_id = $valid_roles[$role_name];
                    $stmt = $connection->prepare("INSERT INTO Contributor_Person_has_Role (Contributor_Person_contributor_person_id, Role_role_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $contributor_person_id, $role_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Protokollieren Sie ungültige Rollennamen
                    error_log("Ungültiger Rollenname für Contributor $contributor_person_id: $role_name");
                }
            }
        }
    }

    // Workaround für Roles TODO: Umbauen auf ID
    if (!isset($valid_roles)) {
        $valid_roles = [];
        $stmt = $connection->prepare("SELECT role_id, name FROM Role WHERE forInstitutions = 1");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $valid_roles[$row['name']] = $row['role_id'];
        }
        $stmt->close();
    }

    // Speichern aller Contributor Organisations
    if (
        isset($_POST['cbOrganisationName'], $_POST['cbOrganisationRoles'], $_POST['OrganisationAffiliation']) &&
        is_array($_POST['cbOrganisationName']) &&
        is_array($_POST['cbOrganisationRoles']) &&
        is_array($_POST['OrganisationAffiliation'])
    ) {
        $cbOrganisationNames = $_POST['cbOrganisationName'];
        $cbOrganisationRoles = $_POST['cbOrganisationRoles'];
        $cbOrganisationAffiliations = $_POST['OrganisationAffiliation'];
        $cbOrganisationRorIds = $_POST['hiddenOrganisationRorId'] ?? [];

        $len = count($cbOrganisationNames);
        for ($i = 0; $i < $len; $i++) {
            // Prüfen, ob Contributor Institution bereits existiert
            $stmt = $connection->prepare("SELECT contributor_institution_id FROM Contributor_Institution WHERE name = ?");
            $stmt->bind_param("s", $cbOrganisationNames[$i]);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Contributor Institution existiert bereits
                $row = $result->fetch_assoc();
                $contributor_institution_id = $row['contributor_institution_id'];
            } else {
                // Neue Contributor Institution einfügen
                $stmt = $connection->prepare("INSERT INTO Contributor_Institution (name) VALUES (?)");
                $stmt->bind_param("s", $cbOrganisationNames[$i]);
                $stmt->execute();
                $contributor_institution_id = $stmt->insert_id;
            }
            $stmt->close();

            // Resource_has_contributor_institution Eintrag
            $stmt = $connection->prepare("INSERT IGNORE INTO Resource_has_Contributor_Institution (Resource_resource_id, Contributor_Institution_contributor_institution_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $resource_id, $contributor_institution_id);
            $stmt->execute();
            $stmt->close();

            // Affiliation verarbeiten
            if (!empty($cbOrganisationAffiliations[$i])) {
                $affiliation_name = parseAffiliationData($cbOrganisationAffiliations[$i])[0];
                $rorId = isset($cbOrganisationRorIds[$i]) ? parseAffiliationData($cbOrganisationRorIds[$i])[0] : null;
                $rorId = $rorId ? str_replace("https://ror.org/", "", $rorId) : null;

                // Affiliation einfügen oder aktualisieren
                $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?) 
                                              ON DUPLICATE KEY UPDATE 
                                              name = VALUES(name),
                                              rorId = COALESCE(VALUES(rorId), rorId)");
                $stmt->bind_param("ss", $affiliation_name, $rorId);
                $stmt->execute();
                $affiliation_id = $stmt->insert_id ?: $connection->insert_id;
                $stmt->close();

                // Prüfen, ob die Verknüpfung bereits existiert
                $stmt = $connection->prepare("SELECT 1 FROM Contributor_Institution_has_Affiliation 
                                              WHERE Contributor_Institution_contributor_institution_id = ? AND Affiliation_affiliation_id = ?");
                $stmt->bind_param("ii", $contributor_institution_id, $affiliation_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 0) {
                    // Neue Verknüpfung hinzufügen
                    $stmt = $connection->prepare("INSERT INTO Contributor_Institution_has_Affiliation (Contributor_Institution_contributor_institution_id, Affiliation_affiliation_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $contributor_institution_id, $affiliation_id);
                    $stmt->execute();
                }
                $stmt->close();
            }

            // Rollen verarbeiten
            $currentRoles = $cbOrganisationRoles[$i];
            if (!is_array($currentRoles)) {
                $currentRoles = [$currentRoles];
            }

            // Zuerst alle bestehenden Rollen für diese Institution löschen
            $stmt = $connection->prepare("DELETE FROM Contributor_Institution_has_Role WHERE Contributor_Institution_contributor_institution_id = ?");
            $stmt->bind_param("i", $contributor_institution_id);
            $stmt->execute();
            $stmt->close();

            // Dann die neuen Rollen einfügen
            foreach ($currentRoles as $role_name) {
                // Überprüfen, ob der Rollenname gültig ist und die entsprechende ID abrufen
                if (isset($valid_roles[$role_name])) {
                    $role_id = $valid_roles[$role_name];
                    $stmt = $connection->prepare("INSERT INTO Contributor_Institution_has_Role (Contributor_Institution_contributor_institution_id, Role_role_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $contributor_institution_id, $role_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Protokollieren Sie ungültige Rollennamen
                    error_log("Ungültiger Rollenname für Contributor Institution $contributor_institution_id: $role_name");
                }
            }
        }
    }

    // Speichern aller Descriptions
    $descriptionAbstract = $_POST['descriptionAbstract'];
    $abstract = "Abstract";
    $descriptionMethods = $_POST['descriptionMethods'];
    $methods = "Methods";
    $descriptionTechnical = $_POST['descriptionTechnical'];
    $technical = "Technical Information";
    $descriptionOther = $_POST['descriptionOther'];
    $other = "Other";

    $stmt = $connection->prepare("INSERT INTO Description (`type`, `description`, `resource_id`) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $abstract, $descriptionAbstract, $resource_id);
    $stmt->execute();
    $stmt->close();
    if (
        !$descriptionMethods == ""
    ) {
        $stmt = $connection->prepare("INSERT INTO Description (`type`, `description`, `resource_id`) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $methods, $descriptionMethods, $resource_id);
        $stmt->execute();
        $stmt->close();
    }
    if (
        !$descriptionTechnical == ""
    ) {
        $stmt = $connection->prepare("INSERT INTO Description (`type`, `description`, `resource_id`) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $technical, $descriptionTechnical, $resource_id);
        $stmt->execute();
        $stmt->close();
    }
    if (
        !$descriptionOther == ""
    ) {
        $stmt = $connection->prepare("INSERT INTO Description (`type`, `description`, `resource_id`) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $other, $descriptionOther, $resource_id);
        $stmt->execute();
        $stmt->close();
    }

    // Speichern aller Related Works
    if (
        isset($_POST['rIdentifier'], $_POST['relation'], $_POST['rIdentifierType']) &&
        is_array($_POST['rIdentifier']) && is_array($_POST['relation']) &&
        is_array($_POST['rIdentifierType'])
    ) {
        $rIdentifier = $_POST['rIdentifier'];
        $relation = $_POST['relation'];
        $rIdentifierType = $_POST['rIdentifierType'];
        $len = count($rIdentifier);

        // Transaktion vorbereiten
        $connection->begin_transaction();
        //Alles rein damit
        for ($i = 0; $i < $len; $i++) {
            // Überprüfen Sie, ob die Relation existiert
            $stmt = $connection->prepare("SELECT `relation_id` FROM `Relation` WHERE `relation_id` = ?");
            $stmt->bind_param("i", $relation[$i]);
            $stmt->execute();
            $result = $stmt->get_result();
            $relation_id = $result->fetch_assoc()['relation_id'];
            $stmt->close();

            // Überprüfen Sie, ob der Identifier-Typ existiert (nach Namen suchen)
            $stmt = $connection->prepare("SELECT `identifier_type_id` FROM `Identifier_Type` WHERE `name` = ?");
            $stmt->bind_param("s", $rIdentifierType[$i]);
            $stmt->execute();
            $result = $stmt->get_result();
            $identifier_type_id = $result->fetch_assoc()['identifier_type_id'];
            $stmt->close();

            // Related Work einfügen
            $stmt = $connection->prepare("INSERT INTO Related_Work (`Identifier`, `relation_fk`, `identifier_type_fk`) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $rIdentifier[$i], $relation_id, $identifier_type_id);
            $stmt->execute();
            $related_work_id = $stmt->insert_id;
            $stmt->close();

            // Verknüpfung zwischen Resource und Related Work einfügen
            $stmt = $connection->prepare("INSERT INTO Resource_has_Related_Work (`Resource_resource_id`, `Related_Work_related_work_id`) VALUES (?, ?)");
            $stmt->bind_param("ii", $resource_id, $related_work_id);
            $stmt->execute();
            $stmt->close();
        }

        // Weg damit
        $connection->commit();
    }

    // Speichern aller Funding Reference
    if (
        isset($_POST['funder'], $_POST['funderId'], $_POST['grantNummer'], $_POST['grantName']) &&
        is_array($_POST['funder']) && is_array($_POST['funderId']) && is_array($_POST['funderidtyp']) && is_array($_POST['grantNummer']) &&
        is_array($_POST['grantName'])
    ) {
        $funder = $_POST['funder'];
        $funderId = $_POST['funderId'];
        $funderidTyp = "Crossref Funder ID";
        $grantNummer = $_POST['grantNummer'];
        $grantName = $_POST['grantName'];
        $len = count($funder);
        for ($i = 0; $i < $len; $i++) {
            $stmt = $connection->prepare("INSERT INTO Funding_Reference (`funder`, `funderId`, `funderidtyp`, `grantnumber`, `grantname`) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", $funder[$i], $funderId[$i], $funderidTyp, $grantNummer[$i], $grantName[$i]);
            $stmt->execute();
            $funding_reference_id = $stmt->insert_id;
            $stmt->close();
            $stmt2 = $connection->prepare("INSERT INTO Resource_has_Funding_Reference (`Resource_resource_id`, `Funding_Reference_funding_reference_id`) VALUES (?, ?)");
            $stmt2->bind_param("ii", $resource_id, $funding_reference_id);
            $stmt2->execute();
            $stmt2->close();
        }
    }




    // Speichern aller Spatial Temporal Coverages
    if (
        isset($_POST['tscLatitudeMin'], $_POST['tscLatitudeMax'], $_POST['tscLongitudeMin'], $_POST['tscLongitudeMax'], $_POST['tscDescription'], $_POST['tscTimeStart'], $_POST['tscTimeEnd'], $_POST['tscTimezone']) &&
        is_array($_POST['tscLatitudeMin']) && is_array($_POST['tscLatitudeMax']) && is_array($_POST['tscLongitudeMin']) && is_array($_POST['tscLongitudeMax']) && is_array($_POST['tscDescription']) && is_array($_POST['tscTimeStart']) && is_array($_POST['tscTimeEnd']) &&
        is_array($_POST['tscTimezone'])
    ) {
        $tscLatitudeMin = $_POST['tscLatitudeMin'];
        $tscLatitudeMax = $_POST['tscLatitudeMax'];
        $tscLongitudeMin = $_POST['tscLongitudeMin'];
        $tscLongitudeMax = $_POST['tscLongitudeMax'];
        $tscDescription = $_POST['tscDescription'];
        $tscDateStart = $_POST['tscDateStart'];
        $tscTimeStart = $_POST['tscTimeStart'];
        $tscDateEnd = $_POST['tscDateEnd'];
        $tscTimeEnd = $_POST['tscTimeEnd'];
        $tscTimezone = $_POST['tscTimezone'];
        $len = count($tscLatitudeMin);
        for ($i = 0; $i < $len; $i++) {
            $tscDateTimeStart = $tscDateStart[$i] . " " . $tscTimeStart[$i];
            $tscDateTimeEnd = $tscDateEnd[$i] . " " . $tscTimeEnd[$i];
            $stmt = $connection->prepare("INSERT INTO Spatial_Temporal_Coverage (`latitudeMin`, `latitudeMax`, `longitudeMin`, `longitudeMax`, `Description`, `dateTimeStart`, `dateTimeEnd`, `timezone`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $tscLatitudeMin[$i], $tscLatitudeMax[$i], $tscLongitudeMin[$i], $tscLongitudeMax[$i], $tscDescription[$i], $tscDateTimeStart, $tscDateTimeEnd, $tscTimezone[$i]);
            if ($stmt->execute()) {
                $spatial_temporal_coverage_id = $stmt->insert_id;
                $stmt->close();
                $stmt = $connection->prepare("INSERT INTO Resource_has_Spatial_Temporal_Coverage (`Resource_resource_id`, `Spatial_Temporal_Coverage_spatial_temporal_coverage_id`) VALUES (?, ?)");
                $stmt->bind_param("ss", $resource_id, $spatial_temporal_coverage_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    $fieldsToProcess = ['thesaurusKeywords', 'instruments', 'platforms', 'chrono', 'cgi', 'gemet'];

    foreach ($fieldsToProcess as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $fieldObject = $_POST[$field];
            $fieldArray = json_decode($fieldObject, true);

            // für jedes Feld den Inhalt in Thesaurus_Keywords speichern
            foreach ($fieldArray as $entry) {
                $value = $entry['value'];
                $valueURI = isset($entry['id']) ? $entry['id'] : null;
                $scheme = isset($entry['scheme']) ? $entry['scheme'] : '';
                $schemeURI = isset($entry['schemeURI']) ? $entry['schemeURI'] : '';
                $language = isset($entry['language']) ? $entry['language'] : '';

                if (!empty($value)) {
                    //Überprüfen, ob Keyword schon in Tabelle  Thesaurus_Keywords gespeichert ist
                    $stmt = $connection->prepare("SELECT thesaurus_keywords_id FROM Thesaurus_Keywords WHERE keyword = ?");
                    $stmt->bind_param("s", $value);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        // Wenn ja, ID holen
                        $stmt->bind_result($thesaurus_keywords_id);
                        $stmt->fetch();
                        $stmt->close();
                    } else {
                        // wenn nein, in Thesaurus_Keywords einfügen
                        $stmt->close();

                        $stmt = $connection->prepare("INSERT INTO Thesaurus_Keywords (`keyword`, `scheme`, `schemeURI`, `valueURI`, `language`) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssss", $value, $scheme, $schemeURI, $valueURI, $language);
                        $stmt->execute();

                        $thesaurus_keywords_id = $stmt->insert_id;
                        $stmt->close();
                    }

                    // Resource_has_Thesaurus_Keywords befüllen
                    $stmt = $connection->prepare("INSERT INTO Resource_has_Thesaurus_Keywords (`Resource_resource_id`, `Thesaurus_Keywords_thesaurus_keywords_id`) VALUES (?, ?)");
                    $stmt->bind_param("ii", $resource_id, $thesaurus_keywords_id);
                    $stmt->execute();


                    $stmt->close();
                }
            }
        }
    }

    // api.php?action=getResourcesAsOneFile&id=$resource_id aufrufen und Datensatz als Datei mit allen 3 XML-Schemata herunterladen
    $base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/";
    $url = $base_url . "api.php?action=getResourcesAsOneFile&id=" . $resource_id;
    header("Location: " . $url);
    ob_end_flush();
    exit();
}

