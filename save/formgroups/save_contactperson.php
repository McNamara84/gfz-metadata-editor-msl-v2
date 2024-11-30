<?php

/**
 * Saves the contact person information in the database.
 *
 * This function processes the input data for contact persons, saves it in the database,
 * and creates associated entries for affiliations.
 *
 * @param mysqli $connection  The database connection.
 * @param array  $postData    The POST data from the form. The following keys are expected:
 *                            - cpLastname: array
 *                            - cpFirstname: array
 *                            - cpPosition: array
 *                            - cpEmail: array
 *                            - cpOnlineResource: array
 *                            - cpAffiliation: array (optional, but only if no ROR ID is present)
 *                            - hiddenCPRorId: array (optional)
 * @param int    $resource_id The ID of the associated resource.
 *
 * @return void
 *
 * @throws mysqli_sql_exception If a database error occurs.
 */
function saveContactPerson($connection, $postData, $resource_id)
{
    if (
        isset($postData['cpLastname'], $postData['cpFirstname'], $postData['cpPosition'], $postData['cpEmail']) &&
        is_array($postData['cpLastname']) && is_array($postData['cpFirstname']) && is_array($postData['cpPosition']) && is_array($postData['cpEmail'])
    ) {
        $familynames = $postData['cpLastname'] ?? [];
        $givennames = $postData['cpFirstname'] ?? [];
        $positions = $postData['cpPosition'] ?? [];
        $emails = $postData['cpEmail'] ?? [];
        $websites = $postData['cpOnlineResource'] ?? [];
        $affiliations = $postData['cpAffiliation'] ?? [];
        $rorIds = $postData['hiddenCPRorId'] ?? [];
        error_log("HALLO: " . $rorIds[0]);
        $len = count($familynames);
        for ($i = 0; $i < $len; $i++) {

            // Insert new contact person
            $website = isset($websites[$i]) ? preg_replace('#^https?://#', '', $websites[$i]) : '';  // Remove protocol
            $stmt = $connection->prepare("INSERT INTO Contact_Person (familyname, givenname, position, email, website) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $familynames[$i], $givennames[$i], $positions[$i], $emails[$i], $website);
            $stmt->execute();
            $contact_person_id = $stmt->insert_id;
            $stmt->close();

            // Insert into Resource_has_Contact_Person
            $stmt = $connection->prepare("INSERT IGNORE INTO Resource_has_Contact_Person (Resource_resource_id, Contact_Person_contact_person_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $resource_id, $contact_person_id);
            $stmt->execute();
            $stmt->close();
            error_log("Affiliations für diese Person: " . $affiliations[$i]);
            error_log("ROR IDs für diese Person:" . $rorIds[$i]);
            saveContactPersonAffiliations($connection, $contact_person_id, $affiliations[$i], $rorIds[$i]);
        }
    }
}


/**
 * Parses affiliation data from JSON string into an array of affiliation names
 * 
 * @param string|null $affiliation_data JSON string containing affiliation data in format [{"value": "affiliation name"}, ...]
 * @return array Array of affiliation names, empty array if input is invalid or empty
 */
function parseAffiliationData($affiliation_data)
{
    if (empty($affiliation_data)) {
        return [];
    }

    $affiliations = json_decode($affiliation_data, true);
    if (!$affiliations) {
        return [];
    }

    return array_map(function ($aff) {
        return $aff['value'];
    }, $affiliations);
}

/**
 * Parses ROR IDs from a comma-separated string into an array
 * Extracts the ID part from full ROR URLs if present
 * 
 * @param string|null $rorId_data Comma-separated string of ROR IDs (can be full URLs or just IDs)
 * @return array Array of ROR IDs (without URL prefix), null values for empty entries
 */
function parseRorIds($rorId_data)
{
    if (empty($rorId_data)) {
        return [];
    }

    $rorIds = explode(',', $rorId_data);
    return array_map(function ($rorId) {
        $rorId = trim($rorId);
        if (strpos($rorId, 'https://ror.org/') === 0) {
            $rorId = substr($rorId, strlen('https://ror.org/'));
        }
        return $rorId ?: null;
    }, $rorIds);
}

/**
 * Saves affiliations for a contact person to the database
 * Creates new affiliations if they don't exist and links them to the contact person
 * Updates ROR IDs for existing affiliations if provided
 * 
 * @param mysqli $connection Database connection object
 * @param int $contact_person_id ID of the contact person
 * @param string $affiliation_data JSON string containing affiliation data
 * @param string $rorId_data Comma-separated string of ROR IDs
 * @return void
 */
function saveContactPersonAffiliations($connection, $contact_person_id, $affiliation_data, $rorId_data)
{
    $affiliationNames = parseAffiliationData($affiliation_data);
    $rorIds = parseRorIds($rorId_data);

    $length = count($affiliationNames);

    for ($i = 0; $i < $length; $i++) {
        $affiliationName = $affiliationNames[$i];
        $rorId = isset($rorIds[$i]) ? $rorIds[$i] : null;

        // Check if affiliation already exists
        $stmt = $connection->prepare("SELECT affiliation_id FROM Affiliation WHERE name = ?");
        $stmt->bind_param("s", $affiliationName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing affiliation
            $row = $result->fetch_assoc();
            $affiliation_id = $row['affiliation_id'];

            if ($rorId !== null) {
                $updateStmt = $connection->prepare("UPDATE Affiliation SET rorId = ? WHERE affiliation_id = ?");
                $updateStmt->bind_param("si", $rorId, $affiliation_id);
                $updateStmt->execute();
                $updateStmt->close();
            }
        } else {
            // Create new affiliation
            $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?)");
            $stmt->bind_param("ss", $affiliationName, $rorId);
            $stmt->execute();
            $affiliation_id = $stmt->insert_id;
        }
        $stmt->close();

        // Link contact person with affiliation
        $stmt = $connection->prepare("INSERT IGNORE INTO Contact_Person_has_Affiliation 
            (Contact_Person_contact_person_id, Affiliation_affiliation_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $contact_person_id, $affiliation_id);
        $stmt->execute();
        $stmt->close();
    }
}
