<?php

/**
 * Saves the originating laboratories in the database.
 *
 * @param mysqli $connection  The database connection.
 * @param array  $postData    The POST data from the form.
 * @param int    $resource_id The ID of the associated resource.
 *
 * @return void
 */
function saveOriginatingLaboratories($connection, $postData, $resource_id)
{
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


/**
 * Saves or updates an originating laboratory in the database.
 *
 * @param mysqli $connection The database connection.
 * @param string $labName    The name of the laboratory.
 * @param string $labId      The ID of the laboratory.
 *
 * @return int The ID of the saved or updated originating laboratory.
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

/**
 * Saves an affiliation into the database.
 *
 * @param mysqli      $connection       The database connection.
 * @param string      $affiliation_name The name of the affiliation.
 * @param string|null $rorId            The ROR ID of the affiliation.
 *
 * @return int The ID of the saved affiliation.
 */
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
 * Links a resource to an originating laboratory.
 *
 * @param mysqli $connection  The database connection.
 * @param int    $resource_id The ID of the resource.
 * @param int    $lab_id      The ID of the originating laboratory.
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

/**
 * Links an originating laboratory to an affiliation.
 *
 * @param mysqli $connection    The database connection.
 * @param int    $lab_id        The ID of the originating laboratory.
 * @param int    $affiliation_id The ID of the affiliation.
 *
 * @return void
 */
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
 * Saves the affiliation of an originating laboratory.
 *
 * @param mysqli      $connection       The database connection.
 * @param int         $lab_id           The ID of the originating laboratory.
 * @param string      $affiliation_name The name of the affiliation.
 * @param string|null $rorId            The ROR ID of the affiliation.
 *
 * @return void
 */
function saveOriginatingLaboratoryAffiliation($connection, $lab_id, $affiliation_name, $rorId)
{
    error_log("Saving affiliation: Lab ID: $lab_id, Affiliation: $affiliation_name, ROR ID: $rorId");

    $rorId = $rorId ? str_replace("https://ror.org/", "", $rorId) : null;

    // Check if the affiliation already exists
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
