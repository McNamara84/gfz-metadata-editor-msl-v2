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

        $len = count($familynames);
        for ($i = 0; $i < $len; $i++) {
            // Check if a valid affiliation is present
            $affiliationData = parseAffiliationCPData($affiliations[$i] ?? '[]');
            $rorIdData = parseAffiliationCPData($rorIds[$i] ?? '[]');


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
            saveContactPersonAffiliations($connection, $contact_person_id, $affiliationData, $rorIdData );
        }
    }
}

/**
 * Saves the affiliations of contact persons.
 *
 * @param mysqli $connection        The database connection.
 * @param int    $contact_person_id The ID of the contact person.
 * @param mixed  $affiliation_data       The affiliation data.
 * @param mixed  $rorId_data             The ROR ID data.
 *
 * @return void
 */
function saveContactPersonAffiliations($connection, $contact_person_id, $affiliation_data, $rorId_data) {
    // Parse the input data
    $affiliations = parseAffiliationCPData($affiliation_data);
    $rorIds = parseAffiliationCPData($rorId_data);

    // Ensure arrays are aligned
    $count = max(count($affiliations), count($rorIds));
    for ($index = 0; $index < $count; $index++) {
        $affiliation_name = $affiliations[$index] ?? null;
        $rorId = isset($rorIds[$index]) ? str_replace("https://ror.org/", "", $rorIds[$index]) : null;

        // Skip invalid or empty affiliation names
        if (empty($affiliation_name)) {
            continue;
        }

        // Insert or update the affiliation (handle null for rorId properly)
        $stmt = $connection->prepare(
            "INSERT INTO Affiliation (name, rorId) VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE 
             name = VALUES(name), 
             rorId = COALESCE(VALUES(rorId), rorId)"
        );
        $stmt->bind_param("ss", $affiliation_name, $rorId); // rorId will be null if not provided
        $stmt->execute();

        // Get the affiliation ID (newly inserted or existing)
        $affiliation_id = $stmt->insert_id ?: $connection->insert_id;
        $stmt->close();

        // Link the contact person to the affiliation
        $stmt = $connection->prepare(
            "INSERT IGNORE INTO Contact_Person_has_Affiliation (Contact_Person_contact_person_id, Affiliation_affiliation_id) VALUES (?, ?)"
        );
        $stmt->bind_param("ii", $contact_person_id, $affiliation_id);
        $stmt->execute();
        $stmt->close();
    }
}


/**
 * Parses the affiliation data.
 *
 * @param mixed $data The data to parse.
 *
 * @return array The parsed data as an array.
 */
function parseAffiliationCPData($data) {
    if (empty($data) || $data === '[]') {
        return [];
    }

    // Handle if $data is already an array
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

    // Handle if $data is a JSON string
    if (is_string($data)) {
        $decoded = json_decode($data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            // If decoded JSON is an array of objects or values
            if (is_array($decoded)) {
                return array_map(function ($item) {
                    return is_array($item) && isset($item['value']) ? trim($item['value']) : trim($item);
                }, $decoded);
            }
        }

        // Handle comma-separated strings
        if (strpos($data, ',') !== false) {
            return array_map('trim', explode(',', $data));
        }

        // Return as single-element array for a simple string
        return [trim($data)];
    }

    // Fallback for other types
    return [trim((string)$data)];
}

