<?php
/**
 * Saves author information in the database.
 *
 * This function processes input data for authors, saves it in the database,
 * and creates corresponding entries for affiliations.
 *
 * @param mysqli $connection The database connection.
 * @param array  $postData   The POST data from the form. Expected keys are:
 *                           - familynames: array
 *                           - givennames: array
 *                           - orcids: array
 *                           - affiliation: array
 *                           - authorRorIds: array
 * @param int    $resource_id The ID of the associated resource.
 *
 * @return void
 *
 * @throws mysqli_sql_exception If a database error occurs.
 */
function saveAuthors($connection, $postData, $resource_id)
{
    $familynames = $postData['familynames'] ?? [];
    $givennames = $postData['givennames'] ?? [];
    $orcids = $postData['orcids'] ?? [];
    $affiliations = $postData['affiliation'] ?? [];
    $rorIds = $postData['authorRorIds'] ?? [];

    if (
        !empty($familynames) && !empty($givennames) && !empty($orcids) &&
        count($familynames) === count($givennames) && count($givennames) === count($orcids)
    ) {
        $len = count($familynames);

        for ($i = 0; $i < $len; $i++) {
            $familyname = trim($familynames[$i]);
            $givenname = trim($givennames[$i]);
            $orcid = trim($orcids[$i]);
            $affiliation_data = isset($affiliations[$i]) ? $affiliations[$i] : '';
            $rorId_data = isset($rorIds[$i]) ? $rorIds[$i] : '';

            // Skip authors without family names
            if (empty($familyname)) {
                continue;
            }

            // Check if there is a ROR ID without an affiliation
            $rorIdArray = parseAffiliationData($rorId_data);
            $affiliationArray = parseAffiliationData($affiliation_data);
            if (!empty($rorIdArray) && empty($affiliationArray)) {
                continue; // Skip this author
            }

            // Check if the author already exists
            $stmt = $connection->prepare("SELECT author_id FROM Author WHERE orcid = ?");
            $stmt->bind_param("s", $orcid);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Author already exists, get the ID
                $row = $result->fetch_assoc();
                $author_id = $row['author_id'];

                // Update the author's data
                $stmt = $connection->prepare("UPDATE Author SET familyname = ?, givenname = ? WHERE author_id = ?");
                $stmt->bind_param("ssi", $familyname, $givenname, $author_id);
                $stmt->execute();
            } else {
                // Insert new author
                $stmt = $connection->prepare("INSERT INTO Author (familyname, givenname, orcid) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $familyname, $givenname, $orcid);
                $stmt->execute();
                $author_id = $stmt->insert_id;
            }
            $stmt->close();

            // Insert into Resource_has_Author
            $stmt = $connection->prepare("INSERT IGNORE INTO Resource_has_Author (Resource_resource_id, Author_author_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $resource_id, $author_id);
            $stmt->execute();
            $stmt->close();

            // Always save affiliations, regardless of whether the author is new or already exists
            if (!empty($affiliation_data)) {
                saveAuthorAffiliations($connection, $author_id, $affiliation_data, $rorId_data);
            }
        }
    }
}

/**
 * Saves the affiliations of an author.
 *
 * @param mysqli $connection       The database connection.
 * @param int    $author_id        The ID of the author.
 * @param string $affiliation_data The affiliation data as a JSON string.
 * @param string $rorId_data       The ROR ID data as a JSON string.
 *
 * @return void
 */
function saveAuthorAffiliations($connection, $author_id, $affiliation_data, $rorId_data)
{
    $affiliations_array = parseAffiliationData($affiliation_data);
    $rorIds_array = parseAffiliationData($rorId_data);

    foreach ($affiliations_array as $index => $affiliation_name) {
        if (empty($affiliation_name)) {
            continue;
        }

        $rorId = isset($rorIds_array[$index]) ? str_replace("https://ror.org/", "", $rorIds_array[$index]) : null;

        // Search for existing affiliation
        $affiliation_id = null;

        // First priority: Exact match of name and ROR ID
        if ($rorId) {
            $stmt = $connection->prepare("SELECT affiliation_id FROM Affiliation WHERE name = ? AND rorId = ?");
            $stmt->bind_param("ss", $affiliation_name, $rorId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $affiliation_id = $row['affiliation_id'];
            }
            $stmt->close();
        }

        // Second priority: Match of ROR ID
        if (!$affiliation_id && $rorId) {
            $stmt = $connection->prepare("SELECT affiliation_id FROM Affiliation WHERE rorId = ?");
            $stmt->bind_param("s", $rorId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $affiliation_id = $row['affiliation_id'];

                // Update the name if it has changed
                $stmt = $connection->prepare("UPDATE Affiliation SET name = ? WHERE affiliation_id = ?");
                $stmt->bind_param("si", $affiliation_name, $affiliation_id);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Third priority: Match of name
        if (!$affiliation_id) {
            $stmt = $connection->prepare("SELECT affiliation_id FROM Affiliation WHERE name = ? AND (rorId IS NULL OR rorId = '')");
            $stmt->bind_param("s", $affiliation_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $affiliation_id = $row['affiliation_id'];

                // Update the ROR ID if a new one is available
                if ($rorId) {
                    $stmt = $connection->prepare("UPDATE Affiliation SET rorId = ? WHERE affiliation_id = ?");
                    $stmt->bind_param("si", $rorId, $affiliation_id);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }

        // If no existing affiliation was found, create a new one
        if (!$affiliation_id) {
            $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?)");
            $stmt->bind_param("ss", $affiliation_name, $rorId);
            $stmt->execute();
            $affiliation_id = $stmt->insert_id;
            $stmt->close();

            // Link author with affiliation
            $stmt = $connection->prepare("INSERT IGNORE INTO Author_has_Affiliation (Author_author_id, Affiliation_affiliation_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $author_id, $affiliation_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

/**
 * Parses affiliation data.
 *
 * @param string $data The data to parse.
 *
 * @return array The parsed data as an array.
 */
function parseAffiliationData($data)
{
    if (empty($data)) {
        return [];
    }

    $decoded = json_decode($data, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        if (is_array($decoded)) {
            return array_map(function ($item) {
                return is_array($item) && isset($item['value']) ? trim($item['value']) : trim($item);
            }, $decoded);
        } else {
            return [trim($decoded)];
        }
    } else {
        return [trim($data)];
    }
}
