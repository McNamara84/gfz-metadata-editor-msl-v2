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
            // Überprüfe, ob das Pflichtfeld 'funder' ausgefüllt ist
            if (empty($funder[$i])) {
                continue;  // Überspringe diesen Eintrag, wenn das Pflichtfeld leer ist
            }

            // Extrahiere die letzten 10 Stellen der CrossRef Funder ID, falls vorhanden
            $funderIdString = !empty($funderId[$i]) ? extractLastTenDigits($funderId[$i]) : null;
            $funderidTyp = !empty($funderIdString) ? "Crossref Funder ID" : null;

            error_log("Original FunderId: " . $funderId[$i]);
            error_log("Extracted FunderId: " . $funderIdString);

            $funding_reference_id = insertFundingReference($connection, $funder[$i], $funderIdString, $funderidTyp, $grantNummer[$i], $grantName[$i]);
            if ($funding_reference_id) {
                linkResourceToFundingReference($connection, $resource_id, $funding_reference_id);
                $saveSuccessful = true;
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
    $stmt = $connection->prepare("INSERT INTO Resource_has_Funding_Reference (`Resource_resource_id`, `Funding_Reference_funding_reference_id`) VALUES (?, ?)");
    if (!$stmt) {
        error_log("Prepare failed in linkResourceToFundingReference: " . $connection->error);
        return false;
    }
    $stmt->bind_param("ii", $resource_id, $funding_reference_id);
    if (!$stmt->execute()) {
        error_log("Execute failed in linkResourceToFundingReference: " . $stmt->error);
        return false;
    }
    $stmt->close();
    return true;
}
