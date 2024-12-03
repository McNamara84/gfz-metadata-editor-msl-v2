<?php
require_once 'parse_affiliations.php';
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
            $rorIdArray = parseRorIds($rorId_data);
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

        // Link author with affiliation
        $stmt = $connection->prepare("INSERT IGNORE INTO Author_has_Affiliation 
            (Author_author_id, Affiliation_affiliation_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $author_id, $affiliation_id);
        $stmt->execute();
        $stmt->close();
    }
}