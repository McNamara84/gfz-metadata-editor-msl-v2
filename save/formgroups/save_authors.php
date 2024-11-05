<?php
/**
 * Speichert die Autoreninformationen in der Datenbank.
 *
 * Diese Funktion verarbeitet die Eingabedaten für Autoren, speichert sie in der Datenbank
 * und erstellt zugehörige Einträge für Affiliationen.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular. Erwartet werden folgende Schlüssel:
 *                        - familynames: array
 *                        - givennames: array
 *                        - orcids: array
 *                        - affiliation: array
 *                        - authorRorIds: array
 * @param int $resource_id Die ID der zugehörigen Ressource.
 *
 * @return void
 *
 * @throws mysqli_sql_exception Wenn ein Datenbankfehler auftritt.
 */
function saveAuthors($connection, $postData, $resource_id)
{
    $familynames = $postData['familynames'] ?? [];
    $givennames = $postData['givennames'] ?? [];
    $orcids = $postData['orcids'] ?? [];
    $affiliations = $postData['affiliation'] ?? [];
    $rorIds = $postData['authorRorIds'] ?? [];

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

            // Überspringe Autoren ohne Nachnamen
            if (empty($familyname)) {
                continue;
            }

            // Überprüfe, ob eine ROR-ID ohne Affiliation vorliegt
            $rorIdArray = parseAffiliationData($rorId_data);
            $affiliationArray = parseAffiliationData($affiliation_data);
            if (!empty($rorIdArray) && empty($affiliationArray)) {
                continue; // Überspringe diesen Autor
            }

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

            // Immer Affiliationen speichern, unabhängig davon, ob der Autor neu ist oder bereits existiert
            if (!empty($affiliation_data)) {
                saveAuthorAffiliations($connection, $author_id, $affiliation_data, $rorId_data);
            }
        }
    }
}

/**
 * Speichert die Affiliationen eines Autors.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $author_id Die ID des Autors.
 * @param string $affiliation_data Die Affiliationsdaten als JSON-String.
 * @param string $rorId_data Die ROR-ID-Daten als JSON-String.
 *
 * @return void
 */
function saveAuthorAffiliations($connection, $author_id, $affiliation_data, $rorId_data)
{
    $affiliations_array = parseAffiliationData($affiliation_data);
    $rorIds_array = parseAffiliationData($rorId_data);

    // Debug-Ausgaben
    fwrite(STDERR, "\nProcessing affiliations for author_id: $author_id\n");
    fwrite(STDERR, "Affiliations: " . print_r($affiliations_array, true) . "\n");
    fwrite(STDERR, "ROR IDs: " . print_r($rorIds_array, true) . "\n");

    foreach ($affiliations_array as $index => $affiliation_name) {
        if (empty($affiliation_name)) {
            continue;
        }

        $rorId = isset($rorIds_array[$index]) ? str_replace("https://ror.org/", "", $rorIds_array[$index]) : null;

        // Suche nach existierender Affiliation
        $affiliation_id = null;

        // Erste Priorität: Exakte Übereinstimmung von Name und ROR-ID
        if ($rorId) {
            $stmt = $connection->prepare("SELECT affiliation_id FROM Affiliation WHERE name = ? AND rorId = ?");
            $stmt->bind_param("ss", $affiliation_name, $rorId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $affiliation_id = $row['affiliation_id'];
            }
            $stmt->close();
        }

        // Zweite Priorität: Übereinstimmung der ROR-ID
        if (!$affiliation_id && $rorId) {
            $stmt = $connection->prepare("SELECT affiliation_id FROM Affiliation WHERE rorId = ?");
            $stmt->bind_param("s", $rorId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $affiliation_id = $row['affiliation_id'];

                // Update den Namen, falls er sich geändert hat
                $stmt = $connection->prepare("UPDATE Affiliation SET name = ? WHERE affiliation_id = ?");
                $stmt->bind_param("si", $affiliation_name, $affiliation_id);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Dritte Priorität: Übereinstimmung des Namens
        if (!$affiliation_id) {
            $stmt = $connection->prepare("SELECT affiliation_id FROM Affiliation WHERE name = ? AND (rorId IS NULL OR rorId = '')");
            $stmt->bind_param("s", $affiliation_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $affiliation_id = $row['affiliation_id'];

                // Update die ROR-ID, falls eine neue vorhanden ist
                if ($rorId) {
                    $stmt = $connection->prepare("UPDATE Affiliation SET rorId = ? WHERE affiliation_id = ?");
                    $stmt->bind_param("si", $rorId, $affiliation_id);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }

        // Wenn keine existierende Affiliation gefunden wurde, erstelle eine neue
        if (!$affiliation_id) {
            $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?)");
            $stmt->bind_param("ss", $affiliation_name, $rorId);
            $stmt->execute();
            $affiliation_id = $stmt->insert_id;
            $stmt->close();

            fwrite(STDERR, "Created new affiliation with ID: $affiliation_id\n");
        } else {
            fwrite(STDERR, "Using existing affiliation with ID: $affiliation_id\n");
        }

        // Verknüpfe Autor mit Affiliation
        $stmt = $connection->prepare("INSERT IGNORE INTO Author_has_Affiliation (Author_author_id, Affiliation_affiliation_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $author_id, $affiliation_id);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Parst die Affiliationsdaten.
 *
 * @param string $data Die zu parsenden Daten.
 * @return array Die geparsten Daten als Array.
 */
function parseAffiliationData($data)
{
    if (empty($data)) {
        return [];
    }

    $decoded = json_decode($data, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        if (is_array($decoded)) {
            return array_map(function ($item) {
                return is_array($item) && isset($item['value']) ? trim($item['value']) : trim($item);
            }, $decoded);
        } else {
            return [trim($decoded)];
        }
    } else {
        return [trim($data)];
    }
}