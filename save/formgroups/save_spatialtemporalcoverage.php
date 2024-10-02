<?php
/**
 * Speichert die Spatial Temporal Coverage (STC) Informationen in der Datenbank.
 *
 * Diese Funktion verarbeitet die Eingabedaten für die räumlich-zeitliche Abdeckung,
 * speichert sie in der Datenbank und erstellt die Verknüpfung zur Ressource.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 *
 * @return void
 *
 * @throws mysqli_sql_exception Wenn ein Datenbankfehler auftritt.
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

    // Überprüfen, ob alle erforderlichen Felder vorhanden sind
    foreach ($requiredFields as $field) {
        if (!isset($postData[$field]) || !is_array($postData[$field])) {
            error_log("Missing or invalid STC field: $field");
            return;
        }
    }

    $len = count($postData['tscLatitudeMin']);

    for ($i = 0; $i < $len; $i++) {
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

        $stc_id = insertSpatialTemporalCoverage($connection, $stcData);
        if ($stc_id) {
            linkResourceToSTC($connection, $resource_id, $stc_id);
        }
    }
}

/**
 * Fügt einen einzelnen Spatial Temporal Coverage Eintrag in die Datenbank ein.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $stcData Die Daten für den STC-Eintrag.
 *
 * @return int|null Die ID des eingefügten STC-Eintrags oder null bei einem Fehler.
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
 * Verknüpft eine Ressource mit einem Spatial Temporal Coverage Eintrag.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $resource_id Die ID der Ressource.
 * @param int $stc_id Die ID des STC-Eintrags.
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