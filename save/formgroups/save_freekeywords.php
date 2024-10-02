<?php
/**
 * Speichert die freien Schlüsselwörter in der Datenbank.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 *
 * @return void
 */
function saveFreeKeywords($connection, $postData, $resource_id)
{
    $freekeywords = isset($postData['freekeywords']) && is_array($postData['freekeywords']) ? $postData['freekeywords'] : [];

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
}