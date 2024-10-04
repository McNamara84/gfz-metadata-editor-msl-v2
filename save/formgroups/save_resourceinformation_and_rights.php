<?php
/**
 * Speichert die Resource Information und Rights in der Datenbank.
 *
 * Diese Funktion verarbeitet die Eingabedaten für Resource Information und Rights,
 * speichert sie in der Datenbank und erstellt zugehörige Einträge für Titel.
 *
 * @param mysqli $connection     Die Datenbankverbindung.
 * @param array  $postData       Die POST-Daten aus dem Formular. Erwartet werden folgende Schlüssel:
 *                               - doi: string
 *                               - year: int
 *                               - dateCreated: string (Datum)
 *                               - dateEmbargo: string (Datum)
 *                               - resourcetype: int
 *                               - version: float
 *                               - language: int
 *                               - Rights: int
 *                               - title: array (optional)
 *                               - titleType: array (optional)
 *
 * @return int                   Die ID der neu erstellten Resource.
 *
 * @throws mysqli_sql_exception  Wenn ein Datenbankfehler auftritt.
 *
 * @example
 * $resource_id = saveResourceInformationAndRights($connection, $_POST);
 */
function saveResourceInformationAndRights($connection, $postData)
{
    // Daten aus dem Formular an PHP-Variablen übergeben
    $doi = $postData["doi"];
    $year = (int) $postData["year"];
    $datecreated = $postData["dateCreated"];
    $dateembargountil = $postData["dateEmbargo"];
    if ($postData["resourcetype"] != null || $postData["resourcetype"] != '') {
        $resourcetype = (int) $postData["resourcetype"];
    }
    if ($postData["version"] === null) {
        $version = null;
    } else {
        $version = (float) $postData["version"];
    }
    $language = (int) $postData["language"];
    $rights = (int) $postData["Rights"];

    if ($year != null && $resourcetype != null && $postData['title'] != null && $resourcetype != null && $language != null && $rights != null && empty($postData['title']) === false) {

        // Insert-Anweisung für Ressource Information
        $stmt = $connection->prepare("SELECT COUNT(*) FROM Resource WHERE doi = ?");
        $stmt->bind_param("s", $doi);
        $stmt->bind_result($resul);

        echo $resul;
        if ($resul > 0) {
            return false;
        } else {
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
    } else {
        return false;
    }
}