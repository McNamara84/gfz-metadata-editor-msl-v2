<?php
/**
 * Speichert die Related Work Informationen in der Datenbank.
 *
 * Diese Funktion verarbeitet die Eingabedaten für Related Work, wenn alle Felder einer Zeile
 * ausgefüllt wurden und speichert sie in der Datenbank und erstellt die Verknüpfung zur Ressource.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 *
 * @return void
 *
 * @throws mysqli_sql_exception Wenn ein Datenbankfehler auftritt.
 */
function saveRelatedWork($connection, $postData, $resource_id)
{
    if (
        isset($postData['rIdentifier'], $postData['relation'], $postData['rIdentifierType']) &&
        is_array($postData['rIdentifier']) && is_array($postData['relation']) &&
        is_array($postData['rIdentifierType'])
    ) {
        $rIdentifier = $postData['rIdentifier'];
        $relation = $postData['relation'];
        $rIdentifierType = $postData['rIdentifierType'];
        $len = count($rIdentifier);

        for ($i = 0; $i < $len; $i++) {
            // Überspringe leere Identifier oder wenn keine Relation ausgewählt wurde
            if (empty($rIdentifier[$i]) || empty($relation[$i])) {
                continue;
            }

            $relation_id = getRelationId($connection, $relation[$i]);
            $identifier_type_id = getIdentifierTypeId($connection, $rIdentifierType[$i]);

            if ($relation_id && $identifier_type_id) {
                $related_work_id = insertRelatedWork($connection, $rIdentifier[$i], $relation_id, $identifier_type_id);
                if ($related_work_id) {
                    linkResourceToRelatedWork($connection, $resource_id, $related_work_id);
                }
            }
        }
    }
}

/**
 * Holt die Relation ID basierend auf der übergebenen Relation.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $relation_id Die zu suchende Relation ID.
 *
 * @return int|null Die gefundene Relation ID oder null, wenn nicht gefunden.
 */
function getRelationId($connection, $relation_id)
{
    $stmt = $connection->prepare("SELECT `relation_id` FROM `Relation` WHERE `relation_id` = ?");
    $stmt->bind_param("i", $relation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['relation_id'] : null;
}

/**
 * Holt die Identifier Type ID basierend auf dem übergebenen Namen.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param string $identifier_type_name Der Name des Identifier Types.
 *
 * @return int|null Die gefundene Identifier Type ID oder null, wenn nicht gefunden.
 */
function getIdentifierTypeId($connection, $identifier_type_name)
{
    $stmt = $connection->prepare("SELECT `identifier_type_id` FROM `Identifier_Type` WHERE `name` = ?");
    $stmt->bind_param("s", $identifier_type_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['identifier_type_id'] : null;
}

/**
 * Fügt einen Related Work Eintrag in die Datenbank ein.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param string $identifier Der Identifier des Related Work.
 * @param int $relation_id Die Relation ID.
 * @param int $identifier_type_id Die Identifier Type ID.
 *
 * @return int|null Die ID des eingefügten Related Work Eintrags oder null bei einem Fehler.
 */
function insertRelatedWork($connection, $identifier, $relation_id, $identifier_type_id)
{
    $stmt = $connection->prepare("INSERT INTO Related_Work (`Identifier`, `relation_fk`, `identifier_type_fk`) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $identifier, $relation_id, $identifier_type_id);
    if ($stmt->execute()) {
        $related_work_id = $stmt->insert_id;
        $stmt->close();
        return $related_work_id;
    } else {
        error_log("Error inserting Related Work: " . $stmt->error);
        $stmt->close();
        return null;
    }
}

/**
 * Verknüpft eine Ressource mit einem Related Work Eintrag.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $resource_id Die ID der Ressource.
 * @param int $related_work_id Die ID des Related Work Eintrags.
 *
 * @return void
 */
function linkResourceToRelatedWork($connection, $resource_id, $related_work_id)
{
    $stmt = $connection->prepare("INSERT INTO Resource_has_Related_Work (`Resource_resource_id`, `Related_Work_related_work_id`) VALUES (?, ?)");
    $stmt->bind_param("ii", $resource_id, $related_work_id);
    $stmt->execute();
    $stmt->close();
}