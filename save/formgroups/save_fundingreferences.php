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
 * @return void
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
        $funderidTyp = "Crossref Funder ID";
        $grantNummer = $postData['grantNummer'];
        $grantName = $postData['grantName'];
        $len = count($funder);

        for ($i = 0; $i < $len; $i++) {
            $funding_reference_id = insertFundingReference($connection, $funder[$i], $funderId[$i], $funderidTyp, $grantNummer[$i], $grantName[$i]);
            if ($funding_reference_id) {
                linkResourceToFundingReference($connection, $resource_id, $funding_reference_id);
            }
        }
    }
}

/**
 * Fügt eine Funding Reference in die Datenbank ein.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param string $funder Der Name des Geldgebers.
 * @param string $funderId Die ID des Geldgebers.
 * @param string $funderidTyp Der Typ der Geldgeber-ID.
 * @param string $grantNummer Die Nummer des Zuschusses.
 * @param string $grantName Der Name des Zuschusses.
 *
 * @return int|null Die ID der eingefügten Funding Reference oder null bei einem Fehler.
 */
function insertFundingReference($connection, $funder, $funderId, $funderidTyp, $grantNummer, $grantName)
{
    $stmt = $connection->prepare("INSERT INTO Funding_Reference (`funder`, `funderId`, `funderidtyp`, `grantnumber`, `grantname`) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $funder, $funderId, $funderidTyp, $grantNummer, $grantName);

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

/**
 * Verknüpft eine Ressource mit einer Funding Reference.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $resource_id Die ID der Ressource.
 * @param int $funding_reference_id Die ID der Funding Reference.
 *
 * @return void
 */
function linkResourceToFundingReference($connection, $resource_id, $funding_reference_id)
{
    $stmt = $connection->prepare("INSERT INTO Resource_has_Funding_Reference (`Resource_resource_id`, `Funding_Reference_funding_reference_id`) VALUES (?, ?)");
    $stmt->bind_param("ii", $resource_id, $funding_reference_id);
    $stmt->execute();
    $stmt->close();
}