<?php
/**
 * Speichert die Beschreibungen einer Ressource in der Datenbank.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 *
 * @return void
 */
function saveDescriptions($connection, $postData, $resource_id)
{
    $descriptionTypes = [
        'Abstract' => 'descriptionAbstract',
        'Methods' => 'descriptionMethods',
        'Technical Information' => 'descriptionTechnical',
        'Other' => 'descriptionOther'
    ];

    foreach ($descriptionTypes as $type => $postKey) {
        if (isset($postData[$postKey]) && !empty($postData[$postKey])) {
            insertDescription($connection, $type, $postData[$postKey], $resource_id);
        }
    }
}

/**
 * Fügt eine einzelne Beschreibung in die Datenbank ein.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param string $type Der Typ der Beschreibung.
 * @param string $description Der Inhalt der Beschreibung.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 *
 * @return void
 */
function insertDescription($connection, $type, $description, $resource_id)
{
    $stmt = $connection->prepare("INSERT INTO Description (`type`, `description`, `resource_id`) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $type, $description, $resource_id);
    $stmt->execute();
    $stmt->close();
}