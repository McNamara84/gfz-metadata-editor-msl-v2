<?php
require_once __DIR__ . '/save_affiliations.php';

/**
 * Saves the originating laboratories in the database.
 *
 * @param mysqli $connection  The database connection
 * @param array  $postData    The POST data from the form
 * @param int    $resource_id The ID of the associated resource
 *
 * @return bool Returns true if saving was successful, false otherwise
 */
function saveOriginatingLaboratories($connection, $postData, $resource_id)
{
    error_log("Starting saveOriginatingLaboratories with resource_id: " . $resource_id);
    error_log("POST data received: " . print_r($postData, true));

    if (!isset($postData['laboratoryName']) || !is_array($postData['laboratoryName'])) {
        error_log("Missing or invalid laboratory name data");
        return false;
    }

    $success = true;
    $len = count($postData['laboratoryName']);
    error_log("Number of laboratories to process: " . $len);

    for ($i = 0; $i < $len; $i++) {
        error_log("Processing laboratory index: " . $i);

        // Parse laboratory name
        $labNameData = $postData['laboratoryName'][$i];
        error_log("Raw laboratory name data: " . $labNameData);

        $labNameArray = json_decode($labNameData, true);
        if (!$labNameArray || !isset($labNameArray[0]['value'])) {
            error_log("Failed to parse laboratory name JSON or missing value");
            continue;
        }

        $labName = $labNameArray[0]['value'];
        $labId = isset($postData['LabId'][$i]) ? $postData['LabId'][$i] : null;

        error_log("Parsed lab name: " . $labName);
        error_log("Lab ID: " . ($labId ?? 'null'));

        // Parse affiliation data
        $affiliation = null;
        $rorId = null;

        if (isset($postData['laboratoryAffiliation'][$i])) {
            $affiliationData = $postData['laboratoryAffiliation'][$i];
            error_log("Raw affiliation data: " . $affiliationData);

            $affiliationArray = json_decode($affiliationData, true);
            if ($affiliationArray && isset($affiliationArray[0]['value'])) {
                $affiliation = $affiliationArray[0]['value'];
                error_log("Parsed affiliation: " . $affiliation);
            }
        }

        if (isset($postData['laboratoryRorIds'][$i])) {
            $rorData = $postData['laboratoryRorIds'][$i];
            error_log("Raw ROR data: " . $rorData);

            $rorArray = json_decode($rorData, true);
            if ($rorArray && isset($rorArray[0]['value'])) {
                $rorId = $rorArray[0]['value'];
                error_log("Parsed ROR ID: " . $rorId);
            }
        }

        // Save laboratory
        if (!empty($labName)) {
            try {
                $lab_id = saveOrUpdateOriginatingLaboratory($connection, $labName, $labId);
                error_log("Saved laboratory with ID: " . ($lab_id ?: 'failed'));

                if (!$lab_id) {
                    error_log("Failed to save laboratory");
                    $success = false;
                    continue;
                }

                $linkResult = linkResourceToOriginatingLaboratory($connection, $resource_id, $lab_id);
                error_log("Linked laboratory to resource: " . ($linkResult ? 'success' : 'failed'));

                if (!$linkResult) {
                    $success = false;
                    continue;
                }

                if (!empty($affiliation)) {
                    $affiliation_id = saveAffiliation($connection, $affiliation, $rorId);
                    error_log("Saved affiliation with ID: " . ($affiliation_id ?: 'failed'));

                    if ($affiliation_id) {
                        $linkAffResult = linkLaboratoryToAffiliation($connection, $lab_id, $affiliation_id);
                        error_log("Linked affiliation to laboratory: " . ($linkAffResult ? 'success' : 'failed'));

                        if (!$linkAffResult) {
                            $success = false;
                        }
                    } else {
                        error_log("Failed to save affiliation");
                        $success = false;
                    }
                }
            } catch (Exception $e) {
                error_log("Error processing laboratory: " . $e->getMessage());
                $success = false;
            }
        } else {
            error_log("Empty laboratory name, skipping");
        }
    }

    error_log("Finished processing laboratories. Overall success: " . ($success ? 'true' : 'false'));
    return $success;
}

/**
 * Saves or updates an originating laboratory in the database.
 * Uses UPSERT operation to handle duplicate entries.
 *
 * @param mysqli      $connection The database connection
 * @param string     $labName    The name of the laboratory
 * @param string|null $labId      The ID of the laboratory
 *
 * @return int|false The ID of the saved or updated laboratory, or false on failure
 */
function saveOrUpdateOriginatingLaboratory($connection, $labName, $labId)
{
    try {
        $stmt = $connection->prepare("INSERT INTO Originating_Laboratory (laboratoryname, labId) 
                                    VALUES (?, ?) 
                                    ON DUPLICATE KEY UPDATE 
                                        laboratoryname = VALUES(laboratoryname),
                                        labId = COALESCE(VALUES(labId), labId)");
        $stmt->bind_param("ss", $labName, $labId);

        if (!$stmt->execute()) {
            error_log("Error executing laboratory save: " . $stmt->error);
            $stmt->close();
            return false;
        }

        $id = $stmt->insert_id ?: $connection->insert_id;
        $stmt->close();

        if (!$id) {
            // If no insert ID (because of update), get the existing ID
            $stmt = $connection->prepare("SELECT originating_laboratory_id FROM Originating_Laboratory WHERE laboratoryname = ?");
            $stmt->bind_param("s", $labName);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $id = $row['originating_laboratory_id'];
            $stmt->close();
        }

        return $id;
    } catch (mysqli_sql_exception $e) {
        error_log("Database error in saveOrUpdateOriginatingLaboratory: " . $e->getMessage());
        return false;
    }
}

/**
 * Saves an affiliation into the database.
 * Handles ROR ID processing and duplicate entries.
 *
 * @param mysqli      $connection       The database connection
 * @param string     $affiliation_name The name of the affiliation
 * @param string|null $rorId           The ROR ID (can be full URL)
 *
 * @return int|false The ID of the saved affiliation, or false on failure
 */
function saveAffiliation($connection, $affiliation_name, $rorId)
{
    try {
        // Clean ROR ID if provided
        if ($rorId) {
            $rorId = str_replace("https://ror.org/", "", $rorId);
        }

        $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) 
                                    VALUES (?, ?) 
                                    ON DUPLICATE KEY UPDATE 
                                        rorId = COALESCE(VALUES(rorId), rorId)");
        $stmt->bind_param("ss", $affiliation_name, $rorId);

        if (!$stmt->execute()) {
            error_log("Error executing affiliation save: " . $stmt->error);
            $stmt->close();
            return false;
        }

        $id = $stmt->insert_id ?: $connection->insert_id;
        $stmt->close();

        if (!$id) {
            // If no insert ID (because of update), get the existing ID
            $stmt = $connection->prepare("SELECT affiliation_id FROM Affiliation WHERE name = ?");
            $stmt->bind_param("s", $affiliation_name);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $id = $row['affiliation_id'];
            $stmt->close();
        }

        return $id;
    } catch (mysqli_sql_exception $e) {
        error_log("Database error in saveAffiliation: " . $e->getMessage());
        return false;
    }
}

/**
 * Links a resource to an originating laboratory.
 * Uses INSERT IGNORE to handle duplicate entries safely.
 *
 * @param mysqli $connection  The database connection
 * @param int    $resource_id The ID of the resource
 * @param int    $lab_id      The ID of the originating laboratory
 *
 * @return bool True if link was created successfully, false otherwise
 */
function linkResourceToOriginatingLaboratory($connection, $resource_id, $lab_id)
{
    try {
        $stmt = $connection->prepare("INSERT IGNORE INTO Resource_has_Originating_Laboratory 
                                    (Resource_resource_id, Originating_Laboratory_originating_laboratory_id) 
                                    VALUES (?, ?)");
        $stmt->bind_param("ii", $resource_id, $lab_id);

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    } catch (mysqli_sql_exception $e) {
        error_log("Database error in linkResourceToOriginatingLaboratory: " . $e->getMessage());
        return false;
    }
}

/**
 * Links an originating laboratory to an affiliation.
 * Uses INSERT IGNORE to handle duplicate entries safely.
 *
 * @param mysqli $connection     The database connection
 * @param int    $lab_id        The ID of the originating laboratory
 * @param int    $affiliation_id The ID of the affiliation
 *
 * @return bool True if link was created successfully, false otherwise
 */
function linkLaboratoryToAffiliation($connection, $lab_id, $affiliation_id)
{
    try {
        $stmt = $connection->prepare("INSERT IGNORE INTO Originating_Laboratory_has_Affiliation 
                                    (Originating_Laboratory_originating_laboratory_id, Affiliation_affiliation_id) 
                                    VALUES (?, ?)");
        $stmt->bind_param("ii", $lab_id, $affiliation_id);

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    } catch (mysqli_sql_exception $e) {
        error_log("Database error in linkLaboratoryToAffiliation: " . $e->getMessage());
        return false;
    }
}