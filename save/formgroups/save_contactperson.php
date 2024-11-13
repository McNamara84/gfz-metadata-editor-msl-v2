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
        $familynames = $postData['cpLastname'];
        $givennames = $postData['cpFirstname'];
        $positions = $postData['cpPosition'];
        $emails = $postData['cpEmail'];
        $websites = $postData['cpOnlineResource'] ?? [];
        $affiliations = $postData['cpAffiliation'] ?? [];
        $rorIds = $postData['hiddenCPRorId'] ?? [];

        $len = count($familynames);
        for ($i = 0; $i < $len; $i++) {
            // Check if all required fields are filled
            if (empty($familynames[$i]) || empty($emails[$i]) || empty($websites[$i])) {
                continue; // Skip this contact person if a required field is missing
            }

            // Check if a valid affiliation is present
            $affiliationData = parseAffiliationCPData($affiliations[$i] ?? '[]');
            $rorIdData = parseAffiliationCPData($rorIds[$i] ?? '[]');
            if (empty($affiliationData) && !empty($rorIdData)) {
                continue; // Skip this contact person if only a ROR ID without affiliation is present
            }

            // Check if the contact person already exists (based on email)
            $stmt = $connection->prepare("SELECT contact_person_id FROM Contact_Person WHERE email = ?");
            $stmt->bind_param("s", $emails[$i]);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Contact person already exists, update the data
                $row = $result->fetch_assoc();
                $contact_person_id = $row['contact_person_id'];
                $websites[$i] = isset($websites[$i]) ? preg_replace('#^https?://#', '', $websites[$i]) : ''; // Remove protocol
                $stmt = $connection->prepare("UPDATE Contact_Person SET familyname = ?, givenname = ?, position = ?, website = ? WHERE contact_person_id = ?");
                $stmt->bind_param("ssssi", $familynames[$i], $givennames[$i], $positions[$i], $websites[$i], $contact_person_id);
            } else {
                // Insert new contact person
                $website = isset($websites[$i]) ? preg_replace('#^https?://#', '', $websites[$i]) : '';  // Remove protocol
                $stmt = $connection->prepare("INSERT INTO Contact_Person (familyname, givenname, position, email, website) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $familynames[$i], $givennames[$i], $positions[$i], $emails[$i], $website);
            }
            $stmt->execute();
            $contact_person_id = $stmt->insert_id ?: $contact_person_id;
            $stmt->close();

            // Insert into Resource_has_Contact_Person
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
 * Saves the affiliations of contact persons.
 *
 * @param mysqli $connection        The database connection.
 * @param int    $contact_person_id The ID of the contact person.
 * @param mixed  $affiliation       The affiliation data.
 * @param mixed  $rorId             The ROR ID data.
 *
 * @return void
 */
function saveContactPersonAffiliations($connection, $contact_person_id, $affiliation, $rorId)
{
    $affiliation_data = parseAffiliationCPData($affiliation);
    $rorId_data = parseAffiliationCPData($rorId);

    // If no affiliation was provided, exit the function without saving
    if (empty($affiliation_data)) {
        return;
    }

    $affiliation_name = $affiliation_data[0];
    $rorId = !empty($rorId_data) ? $rorId_data[0] : null;

    // Remove the substring "https://ror.org/" from ROR ID
    $rorId = $rorId ? str_replace("https://ror.org/", "", $rorId) : null;

    // Insert or update affiliation
    $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?) 
                              ON DUPLICATE KEY UPDATE affiliation_id = LAST_INSERT_ID(affiliation_id), 
                              rorId = COALESCE(VALUES(rorId), rorId)");
    $stmt->bind_param("ss", $affiliation_name, $rorId);
    $stmt->execute();
    $affiliation_id = $stmt->insert_id;
    $stmt->close();

    // Check if the link between contact person and affiliation already exists
    $stmt = $connection->prepare("SELECT 1 FROM Contact_Person_has_Affiliation 
                              WHERE contact_Person_contact_person_id = ? AND Affiliation_affiliation_id = ?");
    $stmt->bind_param("ii", $contact_person_id, $affiliation_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Add new link
        $stmt = $connection->prepare("INSERT INTO Contact_Person_has_Affiliation (contact_Person_contact_person_id, Affiliation_affiliation_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $contact_person_id, $affiliation_id);
        $stmt->execute();
    }
    $stmt->close();
}

/**
 * Parses the affiliation data.
 *
 * @param mixed $data The data to parse.
 *
 * @return array The parsed data as an array.
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
