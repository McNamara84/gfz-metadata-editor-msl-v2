<?php
/**
 * Saves the resource information and rights into the database.
 *
 * This function first checks if a record with the same DOI already exists.
 * If it does, it returns false. Otherwise, it saves the data into the database.
 * Duplicate titles are only saved once.
 *
 * @param mysqli $connection The database connection.
 * @param array  $postData   The POST data from the form.
 *
 * @return int|false The ID of the newly created resource or false if the DOI already exists or required fields are missing.
 *
 * @throws mysqli_sql_exception If a database error occurs.
 */
function saveResourceInformationAndRights($connection, $postData)
{
    // Check required fields
    $requiredFields = ['year', 'dateCreated', 'resourcetype', 'language', 'Rights', 'title', 'titleType'];
    foreach ($requiredFields as $field) {
        if (!isset($postData[$field]) || $postData[$field] === '' || $postData[$field] === null) {
            return false; // Required field is missing or empty
        }
    }

    // Additional checks for arrays
    if (!is_array($postData['title']) || !is_array($postData['titleType']) || empty($postData['title']) || empty($postData['titleType'])) {
        return false; // 'title' or 'titleType' is not an array or is empty
    }

    // Assign form data to PHP variables
    // Konvertiere leere Strings zu NULL für die DOI
    $doi = isset($postData["doi"]) ? (trim($postData["doi"]) === '' ? null : trim($postData["doi"])) : null;
    $year = (int) $postData["year"];
    $dateCreated = $postData["dateCreated"];
    $dateEmbargoUntil = isset($postData["dateEmbargo"]) && trim($postData["dateEmbargo"]) !== '' ? $postData["dateEmbargo"] : null;
    $resourceType = (int) $postData["resourcetype"];
    $version = isset($postData["version"]) && trim($postData["version"]) !== '' ? (float) $postData["version"] : null;
    $language = (int) $postData["language"];
    $rights = (int) $postData["Rights"];

    // Check if a record with the same DOI already exists
    if ($doi !== null) {
        $stmt = $connection->prepare("SELECT COUNT(*) FROM Resource WHERE doi = ?");
        $stmt->bind_param("s", $doi);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            return false; // DOI already exists, nothing will be saved
        }
    }

    // Insert statement for Resource Information
    $stmt = $connection->prepare("INSERT INTO Resource (doi, version, year, dateCreated, dateEmbargoUntil, Rights_rights_id, Resource_Type_resource_name_id, Language_language_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdissiii", $doi, $version, $year, $dateCreated, $dateEmbargoUntil, $rights, $resourceType, $language);
    $stmt->execute();
    $resource_id = $stmt->insert_id;
    $stmt->close();

    // Save all unique titles and title types
    $titles = $postData['title'];
    $titleTypes = $postData['titleType'];
    $uniqueTitles = [];

    for ($i = 0; $i < count($titles); $i++) {
        $key = $titles[$i] . '|' . $titleTypes[$i]; // Unique key for each title-titleType combination
        if (!isset($uniqueTitles[$key])) {
            $uniqueTitles[$key] = [
                'text' => $titles[$i],
                'type' => $titleTypes[$i]
            ];
        }
    }

    foreach ($uniqueTitles as $title) {
        $stmt = $connection->prepare("INSERT INTO Title (`text`, `Title_Type_fk`, `Resource_resource_id`) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $title['text'], $title['type'], $resource_id);
        $stmt->execute();
        $stmt->close();
    }

    return $resource_id;
}