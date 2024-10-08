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
 *                        - affiliation: array (optional, aber nur, wenn auch keine RorID vorhanden ist)
 *                        - rorIds: array (optional)
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
            // Prüfen, ob alle Pflichtfelder ausgefüllt sind
            if (empty($familynames[$i]) || empty($emails[$i])) {
                continue; // Überspringe diese Kontaktperson, wenn ein Pflichtfeld fehlt
            }

            // Prüfen, ob eine gültige Affiliation vorhanden ist
            $affiliationData = parseAffiliationCPData($affiliations[$i] ?? '[]');
            $rorIdData = parseAffiliationCPData($rorIds[$i] ?? '[]');
            if (empty($affiliationData) && !empty($rorIdData)) {
                continue; // Überspringe diese Kontaktperson, wenn nur eine ROR-ID ohne Affiliation vorhanden ist
            }

            // Prüfen, ob die Kontaktperson bereits existiert (basierend auf E-Mail)
            $stmt = $connection->prepare("SELECT contact_person_id FROM Contact_Person WHERE email = ?");
            $stmt->bind_param("s", $emails[$i]);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Kontaktperson existiert bereits, aktualisiere die Daten
                $row = $result->fetch_assoc();
                $contact_person_id = $row['contact_person_id'];
                $websites[$i] = isset($websites[$i]) ? preg_replace('#^https?://#', '', $websites[$i]) : ''; // Protokoll abschneiden
                $stmt = $connection->prepare("UPDATE Contact_Person SET familyname = ?, givenname = ?, position = ?, website = ? WHERE contact_person_id = ?");
                $stmt->bind_param("ssssi", $familynames[$i], $givennames[$i], $positions[$i], $websites[$i], $contact_person_id);
            } else {
                // Neue Kontaktperson einfügen
                $website = isset($websites[$i]) ? preg_replace('#^https?://#', '', $websites[$i]) : '';  // Protokoll abschneiden
                $stmt = $connection->prepare("INSERT INTO Contact_Person (familyname, givenname, position, email, website) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $familynames[$i], $givennames[$i], $positions[$i], $emails[$i], $website);
            }
            $stmt->execute();
            $contact_person_id = $stmt->insert_id ?: $contact_person_id;
            $stmt->close();

            // Resource_has_contact_person Eintrag
            $stmt = $connection->prepare("INSERT IGNORE INTO Resource_has_Contact_Person (Resource_resource_id, Contact_Person_contact_person_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $resource_id, $contact_person_id);
            $stmt->execute();
            $stmt->close();

            if (!empty($affiliationData)) {
                saveContactPersonAffiliations($connection, $contact_person_id, $affiliations[$i], $rorIds[$i] ?? null);
            }
        }
    }
}

/**
 * Speichert die Affiliationen von Contact persons.
 */
function saveContactPersonAffiliations($connection, $contact_person_id, $affiliation, $rorId)
{
    $affiliation_data = parseAffiliationCPData($affiliation);
    $rorId_data = parseAffiliationCPData($rorId);

    // Wenn keine Affiliation angegeben wurde, beenden wir die Funktion ohne etwas zu speichern
    if (empty($affiliation_data)) {
        return;
    }

    $affiliation_name = $affiliation_data[0];
    $rorId = !empty($rorId_data) ? $rorId_data[0] : null;

    // Teilstring "https://ror.org/" von ROR-ID entfernen
    $rorId = $rorId ? str_replace("https://ror.org/", "", $rorId) : null;

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

/**
 * Parst die Affiliationsdaten.
 *
 * @param mixed $data Die zu parsenden Daten.
 * @return array Die geparsten Daten als Array.
 */
function parseAffiliationCPData($data)
{
    if (empty($data) || $data === '[]') {
        return [];
    }

    if (is_array($data)) {
        return array_map(function ($item) {
            if (is_string($item)) {
                $decoded = json_decode($item, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded[0]['value'])) {
                    return trim($decoded[0]['value']);
                }
            }
            return is_array($item) && isset($item['value']) ? trim($item['value']) : trim($item);
        }, $data);
    }

    if (is_string($data)) {
        $decoded = json_decode($data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_array($decoded) && isset($decoded[0]['value'])) {
                return [trim($decoded[0]['value'])];
            }
            if (is_array($decoded)) {
                return array_map(function ($item) {
                    return is_array($item) && isset($item['value']) ? trim($item['value']) : trim($item);
                }, $decoded);
            }
        }
        return [trim($data)];
    }

    return [trim($data)];
}