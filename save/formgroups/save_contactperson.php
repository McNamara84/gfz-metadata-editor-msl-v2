<?php
/**
 * Speichert die Contact Persons-informationen in der Datenbank.
 *
 * Diese Funktion verarbeitet die Eingabedaten für Contact Persons, speichert sie in der Datenbank
 * und erstellt zugehörige Einträge für Affiliationen.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular. Erwartet werden folgende Schlüssel:
 *                        - familynames: array
 *                        - givennames: array
 *                        - positions: array
 *                        - emails: array
 *                        - websites: array
 *                        - affiliation: array
 *                        - rorIds: array
 * @param int $resource_id Die ID der zugehörigen Ressource.
 *
 * @return void
 *
 * @throws mysqli_sql_exception Wenn ein Datenbankfehler auftritt.
 */

function saveContactPerson($connection, $postData, $resource_id)
{
    if (
        isset($postData['cpLastname'], $postData['cpFirstname'], $postData['cpPosition'], $postData['cpEmail']) &&
        is_array($postData['cpLastname']) && is_array($postData['cpFirstname']) && is_array($postData['cpPosition']) && is_array($postData['cpEmail'])
    ) {
        $familynames = $postData['cpLastname'];
        $givennames = $postData['cpFirstname'];
        $positions = $postData['cpPosition'];
        $emails = $postData['cpEmail'];
        $websites = $postData['cpOnlineResource'] ?? [];
        $affiliations = $postData['cpAffiliation'] ?? [];
        $rorIds = $postData['hiddenCPRorId'] ?? [];

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

            if (!empty($affiliations)) {
                saveContactPersonAffiliations($connection, $contact_person_id, $affiliations, $rorIds);
            }
        }
    }
}

/**
 * Speichert die Affiliationen von Contact persons.
 */


function saveContactPersonAffiliations($connection, $contact_person_id, $affiliations, $rorIds)
{
    $affiliations_array = parseAffiliationCPData($affiliations);
    $rorIds_array = parseAffiliationCPData($rorIds);

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



/**
 * Parst die Affiliationsdaten.
 *
 * @param string $data Die zu parsenden Daten.
 * @return array Die geparsten Daten als Array.
 */
function parseAffiliationCPData($data)
{
    
    if (empty($data)) {
        return [];
    }

    if (is_array($data)) {
        // Wenn es bereits ein Array ist, es direkt zurückgeben
        return array_map(function ($item) {
            return is_array($item) && isset($item['value']) ? trim($item['value']) : trim($item);
        }, $data);
    }

    // Wenn die Daten ein String sind, versuchen, JSON zu decodieren
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