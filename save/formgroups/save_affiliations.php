<?php
/**
 * Saves affiliations to the database and links them to a specified entity.
 *
 * This function processes the provided affiliation and ROR ID data, saving them to the database
 * if they do not already exist, and links them to the specified entity (e.g., Author, Contact Person).
 *
 * @param mysqli  $connection       The database connection.
 * @param int     $entity_id        The ID of the entity to link the affiliations to (e.g., Author or Contact Person).
 * @param string  $affiliation_data The raw affiliation data (e.g., a string of comma-separated names).
 * @param string  $rorId_data       The raw ROR ID data (e.g., a string of comma-separated ROR IDs).
 * @param string  $link_table       The name of the table linking the entity to the affiliations.
 *                                  Expected format: `<Entity>_has_Affiliation`.
 * @param string  $entity_column    The name of the column in the linking table that refers to the entity ID.
 *                                  Expected format: `<Entity>_<entity_column>`.
 *
 * @return void
 *
 */
function saveAffiliations($connection, $entity_id, $affiliation_data, $rorId_data, $link_table, $entity_column)
{
    $affiliationNames = parseAffiliationData($affiliation_data);
    $rorIds = parseRorIds($rorId_data);

    $length = count($affiliationNames);

    for ($i = 0; $i < $length; $i++) {
        $affiliationName = $affiliationNames[$i];
        if (empty($affiliationName)) {
            continue; // Skip empty affiliations
        }

        $rorId = isset($rorIds[$i]) ? str_replace("https://ror.org/", "", $rorIds[$i]) : null;

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

        // Link entity (author/contact person/contributor) with affiliation
        $stmt = $connection->prepare("INSERT IGNORE INTO $link_table ($entity_column, Affiliation_affiliation_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $entity_id, $affiliation_id);
        $stmt->execute();
        $stmt->close();
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
