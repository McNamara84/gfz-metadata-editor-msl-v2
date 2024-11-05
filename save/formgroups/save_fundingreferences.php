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
    fwrite(STDERR, "\n=== Linking Resource to Funding Reference ===\n");
    fwrite(STDERR, "Resource ID: $resource_id\n");
    fwrite(STDERR, "Funding Reference ID: $funding_reference_id\n");

    // Überprüfen, ob die IDs gültig sind
    if (!$resource_id || !$funding_reference_id) {
        fwrite(STDERR, "ERROR: Invalid IDs provided\n");
        return false;
    }

    // Überprüfen, ob die Resource existiert
    $resourceCheck = $connection->prepare("SELECT resource_id FROM Resource WHERE resource_id = ?");
    $resourceCheck->bind_param("i", $resource_id);
    $resourceCheck->execute();
    if ($resourceCheck->get_result()->num_rows === 0) {
        fwrite(STDERR, "ERROR: Resource does not exist\n");
        return false;
    }

    // Überprüfen, ob die Funding Reference existiert
    $fundingCheck = $connection->prepare("SELECT funding_reference_id FROM Funding_Reference WHERE funding_reference_id = ?");
    $fundingCheck->bind_param("i", $funding_reference_id);
    $fundingCheck->execute();
    if ($fundingCheck->get_result()->num_rows === 0) {
        fwrite(STDERR, "ERROR: Funding Reference does not exist\n");
        return false;
    }

    // Überprüfen, ob die Verknüpfung bereits existiert
    $existingCheck = $connection->prepare(
        "SELECT 1 FROM Resource_has_Funding_Reference 
         WHERE Resource_resource_id = ? AND Funding_Reference_funding_reference_id = ?"
    );
    $existingCheck->bind_param("ii", $resource_id, $funding_reference_id);
    $existingCheck->execute();
    if ($existingCheck->get_result()->num_rows > 0) {
        fwrite(STDERR, "NOTE: Link already exists\n");
        return true;
    }

    // Verknüpfung erstellen
    $stmt = $connection->prepare(
        "INSERT INTO Resource_has_Funding_Reference 
         (Resource_resource_id, Funding_Reference_funding_reference_id) 
         VALUES (?, ?)"
    );

    if (!$stmt) {
        fwrite(STDERR, "ERROR: Prepare failed: " . $connection->error . "\n");
        return false;
    }

    $stmt->bind_param("ii", $resource_id, $funding_reference_id);

    $success = $stmt->execute();
    if (!$success) {
        fwrite(STDERR, "ERROR: Execute failed: " . $stmt->error . "\n");
        $stmt->close();
        return false;
    }

    fwrite(STDERR, "SUCCESS: Link created successfully\n");
    $stmt->close();
    return true;
}
