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
 * @return boolean Gibt true zurück, wenn die Speicherung erfolgreich war, ansonsten false.
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
            return false;
        }
    }

    $len = count($postData['tscLatitudeMin']);
    $allSuccessful = true;

    for ($i = 0; $i < $len; $i++) {
        // Überprüfen, ob die Koordinaten gültig sind
        if (empty($postData['tscLatitudeMin'][$i]) && empty($postData['tscLatitudeMax'][$i])) {
            error_log("Both Latitude Min and Max are empty for entry $i");
            return false;
        }
        if (empty($postData['tscLongitudeMin'][$i]) && empty($postData['tscLongitudeMax'][$i])) {
            error_log("Both Longitude Min and Max are empty for entry $i");
            return false;
        }

        // Überprüfen, ob mindestens Latitude Min und Longitude Min vorhanden sind
        if (empty($postData['tscLatitudeMin'][$i]) || empty($postData['tscLongitudeMin'][$i])) {
            error_log("Latitude Min or Longitude Min is missing for entry $i");
            return false;
        }

        // Überprüfen, ob Enddatum und Enduhrzeit vorhanden sind, aber Startdatum oder Startuhrzeit fehlen
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

        // Entferne leere Strings
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