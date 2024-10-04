<?php
/**
 * Speichert die Resource Information und Rights in der Datenbank.
 *
 * Diese Funktion prüft zuerst, ob ein Datensatz mit der gleichen DOI bereits existiert.
 * Falls ja, gibt sie false zurück. Andernfalls speichert sie die Daten in der Datenbank.
 *
 * @param mysqli $connection     Die Datenbankverbindung.
 * @param array  $postData       Die POST-Daten aus dem Formular.
 *
 * @return int|false             Die ID der neu erstellten Resource oder false, wenn die DOI bereits existiert oder Pflichtfelder fehlen.
 *
 * @throws mysqli_sql_exception  Wenn ein Datenbankfehler auftritt.
 */
function saveResourceInformationAndRights($connection, $postData)
{
    // Pflichtfelder überprüfen
    $requiredFields = ['year', 'dateCreated', 'resourcetype', 'language', 'Rights', 'title', 'titleType'];
    foreach ($requiredFields as $field) {
        if (!isset($postData[$field]) || $postData[$field] === '' || $postData[$field] === null) {
            return false; // Pflichtfeld fehlt oder ist leer
        }
    }

    // Weitere Überprüfungen für Arrays
    if (!is_array($postData['title']) || !is_array($postData['titleType']) || empty($postData['title']) || empty($postData['titleType'])) {
        return false; // title oder titleType ist kein Array oder leer
    }

    // Daten aus dem Formular an PHP-Variablen übergeben
    $doi = $postData["doi"] ?? null;
    $year = (int) $postData["year"];
    $datecreated = $postData["dateCreated"];
    $dateembargountil = $postData["dateEmbargo"] ?? null;
    $resourcetype = (int) $postData["resourcetype"];
    $version = isset($postData["version"]) && $postData["version"] !== '' ? (float) $postData["version"] : null;
    $language = (int) $postData["language"];
    $rights = (int) $postData["Rights"];

    // Prüfen, ob ein Datensatz mit der gleichen DOI bereits existiert
    if ($doi !== null) {
        $stmt = $connection->prepare("SELECT COUNT(*) FROM Resource WHERE doi = ?");
        $stmt->bind_param("s", $doi);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            return false; // DOI existiert bereits, nichts wird gespeichert
        }
    }

    // Insert-Anweisung für Ressource Information
    $stmt = $connection->prepare("INSERT INTO Resource (doi, version, year, dateCreated, dateEmbargoUntil, Rights_rights_id, Resource_Type_resource_name_id, Language_language_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdissiii", $doi, $version, $year, $datecreated, $dateembargountil, $rights, $resourcetype, $language);
    $stmt->execute();
    $resource_id = $stmt->insert_id;
    $stmt->close();

    // Speichern aller Titles und Title Types
    $titles = $postData['title'];
    $titleTypes = $postData['titleType'];
    $len = count($titles);
    for ($i = 0; $i < $len; $i++) {
        $stmt = $connection->prepare("INSERT INTO Title (`text`, `Title_Type_fk`, `Resource_resource_id`) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $titles[$i], $titleTypes[$i], $resource_id);
        $stmt->execute();
        $stmt->close();
    }

    return $resource_id;
}