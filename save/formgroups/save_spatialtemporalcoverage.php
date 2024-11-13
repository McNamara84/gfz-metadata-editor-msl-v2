<?php
/**
 * Saves the Spatial Temporal Coverage (STC) information into the database.
 *
 * This function processes the input data for spatial-temporal coverage,
 * saves it into the database, and creates the linkage to the resource.
 *
 * @param mysqli $connection  The database connection.
 * @param array  $postData    The POST data from the form.
 * @param int    $resource_id The ID of the associated resource.
 *
 * @return bool Returns true if the saving was successful, otherwise false.
 *
 * @throws mysqli_sql_exception If a database error occurs.
 */
function saveSpatialTemporalCoverage($connection, $postData, $resource_id)
{
    $requiredFields = [
        'tscLatitudeMin',
        'tscLatitudeMax',
        'tscLongitudeMin',
        'tscLongitudeMax',
        'tscDescription',
        'tscDateStart',
        'tscTimeStart',
        'tscDateEnd',
        'tscTimeEnd',
        'tscTimezone'
    ];

    // Check if all required fields are present
    foreach ($requiredFields as $field) {
        if (!isset($postData[$field]) || !is_array($postData[$field])) {
            error_log("Missing or invalid STC field: $field");
            return false;
        }
    }

    $len = count($postData['tscLatitudeMin']);
    $allSuccessful = true;

    for ($i = 0; $i < $len; $i++) {
        // Check if the coordinates are valid
        if (empty($postData['tscLatitudeMin'][$i]) && empty($postData['tscLatitudeMax'][$i])) {
            error_log("Both Latitude Min and Max are empty for entry $i");
            return false;
        }
        if (empty($postData['tscLongitudeMin'][$i]) && empty($postData['tscLongitudeMax'][$i])) {
            error_log("Both Longitude Min and Max are empty for entry $i");
            return false;
        }

        // Check if at least Latitude Min and Longitude Min are present
        if (empty($postData['tscLatitudeMin'][$i]) || empty($postData['tscLongitudeMin'][$i])) {
            error_log("Latitude Min or Longitude Min is missing for entry $i");
            return false;
        }

        // Check if end date and end time are provided but start date or start time are missing
        if (
            (!empty($postData['tscDateEnd'][$i]) || !empty($postData['tscTimeEnd'][$i])) &&
            (empty($postData['tscDateStart'][$i]) || empty($postData['tscTimeStart'][$i]))
        ) {
            error_log("End date/time is set but start date/time is missing for entry $i");
            return false;
        }

        $stcData = [
            'latitudeMin' => $postData['tscLatitudeMin'][$i],
            'latitudeMax' => $postData['tscLatitudeMax'][$i],
            'longitudeMin' => $postData['tscLongitudeMin'][$i],
            'longitudeMax' => $postData['tscLongitudeMax'][$i],
            'description' => $postData['tscDescription'][$i],
            'dateTimeStart' => $postData['tscDateStart'][$i] . " " . $postData['tscTimeStart'][$i],
            'dateTimeEnd' => $postData['tscDateEnd'][$i] . " " . $postData['tscTimeEnd'][$i],
            'timezone' => $postData['tscTimezone'][$i]
        ];

        // Remove empty strings by converting them to null
        $stcData = array_map(function ($value) {
            return $value === '' ? null : $value;
        }, $stcData);

        $stc_id = insertSpatialTemporalCoverage($connection, $stcData);
        if ($stc_id) {
            linkResourceToSTC($connection, $resource_id, $stc_id);
        } else {
            $allSuccessful = false;
        }
    }

    return $allSuccessful;
}

/**
 * Inserts a single Spatial Temporal Coverage entry into the database.
 *
 * @param mysqli $connection The database connection.
 * @param array  $stcData    The data for the STC entry.
 *
 * @return int|null The ID of the inserted STC entry, or null on failure.
 */
function insertSpatialTemporalCoverage($connection, $stcData)
{
    $stmt = $connection->prepare("INSERT INTO Spatial_Temporal_Coverage 
        (`latitudeMin`, `latitudeMax`, `longitudeMin`, `longitudeMax`, `Description`, `dateTimeStart`, `dateTimeEnd`, `timezone`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssssssss",
        $stcData['latitudeMin'],
        $stcData['latitudeMax'],
        $stcData['longitudeMin'],
        $stcData['longitudeMax'],
        $stcData['description'],
        $stcData['dateTimeStart'],
        $stcData['dateTimeEnd'],
        $stcData['timezone']
    );

    if ($stmt->execute()) {
        $stc_id = $stmt->insert_id;
        $stmt->close();
        return $stc_id;
    } else {
        error_log("Error inserting STC: " . $stmt->error);
        $stmt->close();
        return null;
    }
}

/**
 * Links a resource to a Spatial Temporal Coverage entry.
 *
 * @param mysqli $connection  The database connection.
 * @param int    $resource_id The ID of the resource.
 * @param int    $stc_id      The ID of the STC entry.
 *
 * @return void
 */
function linkResourceToSTC($connection, $resource_id, $stc_id)
{
    $stmt = $connection->prepare("INSERT INTO Resource_has_Spatial_Temporal_Coverage 
        (`Resource_resource_id`, `Spatial_Temporal_Coverage_spatial_temporal_coverage_id`) 
        VALUES (?, ?)");
    $stmt->bind_param("ii", $resource_id, $stc_id);
    $stmt->execute();
    $stmt->close();
}
