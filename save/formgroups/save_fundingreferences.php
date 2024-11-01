<?php
/**
 * Speichert die Funding Reference Informationen in der Datenbank.
 *
 * Diese Funktion verarbeitet die Eingabedaten für Funding References,
 * speichert sie in der Datenbank und erstellt die Verknüpfung zur Ressource.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 *
 * @return boolean Gibt true zurück, wenn die Speicherung erfolgreich war, ansonsten false.
 *
 * @throws mysqli_sql_exception Wenn ein Datenbankfehler auftritt.
 */
function saveFundingReferences($connection, $postData, $resource_id)
{
    if (!$resource_id) {
        error_log("Invalid resource_id provided");
        return false;
    }

    if (
        isset($postData['funder'], $postData['funderId'], $postData['grantNummer'], $postData['grantName']) &&
        is_array($postData['funder']) && is_array($postData['funderId']) &&
        is_array($postData['grantNummer']) && is_array($postData['grantName'])
    ) {
        $funder = $postData['funder'];
        $funderId = $postData['funderId'];
        $grantNummer = $postData['grantNummer'];
        $grantName = $postData['grantName'];
        $len = count($funder);

        $saveSuccessful = false;

        for ($i = 0; $i < $len; $i++) {
            if (empty($funder[$i])) {
                continue;
            }

            $funderIdString = !empty($funderId[$i]) ? extractLastTenDigits($funderId[$i]) : null;
            $funderidTyp = !empty($funderIdString) ? "Crossref Funder ID" : null;

            error_log("Processing funding reference for funder: " . $funder[$i]);

            $funding_reference_id = insertFundingReference($connection, $funder[$i], $funderIdString, $funderidTyp, $grantNummer[$i], $grantName[$i]);

            if ($funding_reference_id) {
                error_log("Successfully inserted funding reference with ID: " . $funding_reference_id);
                $linkResult = linkResourceToFundingReference($connection, $resource_id, $funding_reference_id);
                if ($linkResult) {
                    $saveSuccessful = true;
                    error_log("Successfully linked resource to funding reference");
                } else {
                    error_log("Failed to link resource to funding reference");
                }
            } else {
                error_log("Failed to insert Funding Reference");
            }
        }

        return $saveSuccessful;
    } else {
        error_log("Invalid postData structure");
        return false;
    }
}

function insertFundingReference($connection, $funder, $funderId, $funderidTyp, $grantNummer, $grantName)
{
    $stmt = $connection->prepare("INSERT INTO Funding_Reference (`funder`, `funderid`, `funderidtyp`, `grantnumber`, `grantname`) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed: " . $connection->error);
        return null;
    }

    $stmt->bind_param("sssss", $funder, $funderId, $funderidTyp, $grantNummer, $grantName);

    if ($stmt->execute()) {
        $funding_reference_id = $stmt->insert_id;
        $stmt->close();
        return $funding_reference_id;
    } else {
        error_log("Error inserting Funding Reference: " . $stmt->error);
        $stmt->close();
        return null;
    }
}

function extractLastTenDigits($funderId)
{
    // Entferne alle nicht-numerischen Zeichen
    $numericOnly = preg_replace('/[^0-9]/', '', $funderId);

    // Extrahiere die letzten 10 Ziffern
    return substr($numericOnly, -10);
}

/**
 * Verknüpft eine Ressource mit einer Funding Reference.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $resource_id Die ID der Ressource.
 * @param int $funding_reference_id Die ID der Funding Reference.
 *
 * @return bool
 */
function linkResourceToFundingReference($connection, $resource_id, $funding_reference_id)
{
    // Debug-Ausgaben
    fwrite(STDERR, "Attempting to link resource_id: $resource_id with funding_reference_id: $funding_reference_id\n");

    // Prüfen ob die IDs gültig sind
    if (!$resource_id || !$funding_reference_id) {
        fwrite(STDERR, "Invalid IDs: resource_id or funding_reference_id is empty\n");
        return false;
    }

    $stmt = $connection->prepare("INSERT INTO Resource_has_Funding_Reference (Resource_resource_id, Funding_Reference_funding_reference_id) VALUES (?, ?)");
    if (!$stmt) {
        fwrite(STDERR, "Prepare failed in linkResourceToFundingReference: " . $connection->error . "\n");
        return false;
    }

    $stmt->bind_param("ii", $resource_id, $funding_reference_id);

    if (!$stmt->execute()) {
        fwrite(STDERR, "Execute failed in linkResourceToFundingReference: " . $stmt->error . "\n");
        $stmt->close();
        return false;
    }

    $affectedRows = $stmt->affected_rows;
    fwrite(STDERR, "Affected rows after insert: $affectedRows\n");

    $stmt->close();
    return $affectedRows > 0;
}

