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
 * @return int|false             Die ID der neu erstellten Resource oder false, wenn die DOI bereits existiert oder ungültige Daten vorliegen.
 *
 * @throws mysqli_sql_exception  Wenn ein Datenbankfehler auftritt.
 */
function saveResourceInformationAndRights($connection, $postData)
{
    // Daten aus dem Formular an PHP-Variablen übergeben
    $doi = $postData["doi"];
    $year = isset($postData["year"]) ? (int) $postData["year"] : null;
    $datecreated = $postData["dateCreated"] ?? null;
    $dateembargountil = $postData["dateEmbargo"] ?? null;
    $resourcetype = isset($postData["resourcetype"]) && $postData["resourcetype"] !== '' ? (int) $postData["resourcetype"] : null;
    $version = isset($postData["version"]) && $postData["version"] !== '' ? (float) $postData["version"] : null;
    $language = isset($postData["language"]) ? (int) $postData["language"] : null;
    $rights = isset($postData["Rights"]) ? (int) $postData["Rights"] : null;

    // Prüfen, ob ein Datensatz mit der gleichen DOI bereits existiert
    $stmt = $connection->prepare("SELECT COUNT(*) FROM Resource WHERE doi = ?");
    $stmt->bind_param("s", $doi);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        return false; // DOI existiert bereits, nichts wird gespeichert
    }

    // Überprüfen, ob alle erforderlichen Daten vorhanden sind
    if (
        $year === null || $resourcetype === null || $language === null || $rights === null ||
        empty($postData['title']) || !is_array($postData['title'])
    ) {
        return false; // Ungültige oder fehlende Daten
    }

    // Insert-Anweisung für Ressource Information
    $stmt = $connection->prepare("INSERT INTO Resource (doi, version, year, dateCreated, dateEmbargoUntil, Rights_rights_id, Resource_Type_resource_name_id, Language_language_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdissiii", $doi, $version, $year, $datecreated, $dateembargountil, $rights, $resourcetype, $language);
    $stmt->execute();
    $resource_id = $stmt->insert_id;
    $stmt->close();

    // Speichern aller Titles und Title Types
    if (isset($postData['title'], $postData['titleType']) && is_array($postData['title']) && is_array($postData['titleType'])) {
        $titles = $postData['title'];
        $titleTypes = $postData['titleType'];
        $len = count($titles);
        for ($i = 0; $i < $len; $i++) {
            $stmt = $connection->prepare("INSERT INTO Title (`text`, `Title_Type_fk`, `Resource_resource_id`) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $titles[$i], $titleTypes[$i], $resource_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    return $resource_id;
}