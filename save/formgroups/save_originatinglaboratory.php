<?php

/**
 * Speichert die Originating Laboratories in der Datenbank.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 *
 * @return void
 */
function saveOriginatingLaboratories($connection, $postData, $resource_id)
{
    if (
        isset($postData['laboratoryName'], $postData['LabId'], $postData['laboratoryAffiliation'], $postData['laboratoryRorIds']) &&
        is_array($postData['laboratoryName']) &&
        is_array($postData['LabId']) &&
        is_array($postData['laboratoryAffiliation']) &&
        is_array($postData['laboratoryRorIds'])
    ) {
        $len = count($postData['laboratoryName']);
        for ($i = 0; $i < $len; $i++) {
            $labName = json_decode($postData['laboratoryName'][$i], true)[0]['value'];
            $labId = $postData['LabId'][$i];
            $affiliation = json_decode($postData['laboratoryAffiliation'][$i], true)[0]['value'] ?? null;
            $rorId = json_decode($postData['laboratoryRorIds'][$i], true)[0]['value'] ?? null;
            if (!empty($labName)) {
                $lab_id = saveOrUpdateOriginatingLaboratory($connection, $labName, $labId);
                linkResourceToOriginatingLaboratory($connection, $resource_id, $lab_id);

                if (!empty($affiliation)) {
                    $affiliation_id = saveAffiliation($connection, $affiliation, $rorId);
                    linkLaboratoryToAffiliation($connection, $lab_id, $affiliation_id);
                }
            }
        }
    }
}


/**
 * Speichert oder aktualisiert ein Originating Laboratory in der Datenbank.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param string $labName Der Name des Labors.
 * @param string $labId Die ID des Labors.
 *
 * @return int Die ID des gespeicherten oder aktualisierten Originating Laboratory.
 */
function saveOrUpdateOriginatingLaboratory($connection, $labName, $labId)
{
    $stmt = $connection->prepare("INSERT INTO Originating_Laboratory (laboratoryname, labId) VALUES (?, ?)
                                  ON DUPLICATE KEY UPDATE laboratoryname = VALUES(laboratoryname)");
    $stmt->bind_param("ss", $labName, $labId);
    $stmt->execute();
    $lab_id = $stmt->insert_id ?: $connection->insert_id;
    $stmt->close();

    return $lab_id;
}

function saveAffiliation($connection, $affiliation_name, $rorId)
{
    $rorId = $rorId ? str_replace("https://ror.org/", "", $rorId) : null;

    $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE rorId = COALESCE(VALUES(rorId), rorId)");
    $stmt->bind_param("ss", $affiliation_name, $rorId);
    $stmt->execute();
    $affiliation_id = $stmt->insert_id ?: $connection->insert_id;
    $stmt->close();

    return $affiliation_id;
}


/**
 * Verknüpft eine Resource mit einem Originating Laboratory.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $resource_id Die ID der Resource.
 * @param int $lab_id Die ID des Originating Laboratory.
 *
 * @return void
 */
function linkResourceToOriginatingLaboratory($connection, $resource_id, $lab_id)
{
    $stmt = $connection->prepare("INSERT IGNORE INTO Resource_has_Originating_Laboratory (Resource_resource_id, Originating_Laboratory_originating_laboratory_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $resource_id, $lab_id);
    $stmt->execute();
    $stmt->close();
}

function linkLaboratoryToAffiliation($connection, $lab_id, $affiliation_id)
{
    $stmt = $connection->prepare("INSERT IGNORE INTO Originating_Laboratory_has_Affiliation 
                                  (Originating_Laboratory_originating_laboratory_id, Affiliation_affiliation_id) 
                                  VALUES (?, ?)");
    $stmt->bind_param("ii", $lab_id, $affiliation_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Speichert die Affiliation eines Originating Laboratory.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $lab_id Die ID des Originating Laboratory.
 * @param string $affiliation_name Der Name der Affiliation.
 * @param string|null $rorId Die ROR-ID der Affiliation.
 *
 * @return void
 */
function saveOriginatingLaboratoryAffiliation($connection, $lab_id, $affiliation_name, $rorId)
{
    error_log("Saving affiliation: Lab ID: $lab_id, Affiliation: $affiliation_name, ROR ID: $rorId");

    $rorId = $rorId ? str_replace("https://ror.org/", "", $rorId) : null;

    // Überprüfen, ob die Affiliation bereits existiert
    $stmt = $connection->prepare("SELECT affiliation_id FROM Affiliation WHERE name = ?");
    $stmt->bind_param("s", $affiliation_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $affiliation_id = $row['affiliation_id'];
        $stmt = $connection->prepare("UPDATE Affiliation SET rorId = COALESCE(?, rorId) WHERE affiliation_id = ?");
        $stmt->bind_param("si", $rorId, $affiliation_id);
    } else {
        $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?)");
        $stmt->bind_param("ss", $affiliation_name, $rorId);
    }
    $stmt->execute();
    $affiliation_id = $stmt->insert_id ?: $affiliation_id;
    $stmt->close();

    error_log("Affiliation saved with ID: $affiliation_id");

    $stmt = $connection->prepare("INSERT IGNORE INTO Originating_Laboratory_has_Affiliation 
                                  (Originating_Laboratory_originating_laboratory_id, Affiliation_affiliation_id) 
                                  VALUES (?, ?)");
    $stmt->bind_param("ii", $lab_id, $affiliation_id);
    $result = $stmt->execute();
    $stmt->close();

    error_log("Link between Lab and Affiliation created: " . ($result ? "Success" : "Failure"));

    return $affiliation_id;
}