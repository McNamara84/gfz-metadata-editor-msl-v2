<?php
/**
 * Speichert die Thesaurus-Schlüsselwörter in der Datenbank.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 *
 * @return void
 */
function saveThesaurusKeywords($connection, $postData, $resource_id)
{
    // Definiert die Felder, die verarbeitet werden sollen
    $fieldsToProcess = [
        'thesaurusKeywords', // Thesaurus Keywords
        'MSLKeywords' // MSL Keywords
    ];

    // Durchläuft die Felder und prüft, ob sie im POST-Daten existieren und nicht leer sind
    foreach ($fieldsToProcess as $field) {
        if (isset($postData[$field]) && $postData[$field] !== '') {
            $fieldObject = $postData[$field]; // JSON-String
            $fieldArray = json_decode($fieldObject, true); // Dekodiert den JSON-String in ein Array
            
            // Debugging-Ausgabe
            // print_r($fieldArray);

            // Verarbeitet jedes Schlüsselwort im Array
            foreach ($fieldArray as $entry) {
                processThesaurusKeyword($connection, $entry, $resource_id, $field);
            }
        }
    }
}

/**
 * Verarbeitet ein einzelnes Thesaurus-Schlüsselwort.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $entry Die Daten des Schlüsselworts.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 * @param string $field Das aktuelle Feld, entweder 'thesaurusKeywords' oder 'MSLKeywords'.
 *
 * @return void
 */
function processThesaurusKeyword($connection, $entry, $resource_id, $field)
{
    // Holt die Werte aus dem Schlüsselwort-Array
    $value = $entry['value'];
    $valueURI = isset($entry['id']) ? $entry['id'] : null; // Falls vorhanden, wird die URI verwendet
    $scheme = isset($entry['scheme']) ? $entry['scheme'] : $field; // Falls kein Schema vorhanden, nutze den Feldnamen
    $schemeURI = isset($entry['schemeURI']) ? $entry['schemeURI'] : ''; // Optional: URI des Schemas
    $language = isset($entry['language']) ? $entry['language'] : 'en'; // Standard-Sprache: Englisch

    // Wenn der Wert nicht leer ist, verarbeite ihn
    if (!empty($value)) {
        // Holt die ID des Schlüsselworts oder erstellt ein neues, wenn es noch nicht existiert
        $thesaurus_keywords_id = getOrCreateThesaurusKeyword($connection, $value, $scheme, $schemeURI, $valueURI, $language);
        // Verknüpft die Ressource mit dem Schlüsselwort
        linkResourceToThesaurusKeyword($connection, $resource_id, $thesaurus_keywords_id);
    }
}

/**
 * Holt ein bestehendes Thesaurus-Schlüsselwort oder erstellt ein neues.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param string $value Der Wert des Schlüsselworts.
 * @param string $scheme Das Schema des Schlüsselworts.
 * @param string $schemeURI Die URI des Schemas.
 * @param string|null $valueURI Die URI des Werts.
 * @param string $language Die Sprache des Schlüsselworts.
 *
 * @return int Die ID des Thesaurus-Schlüsselworts.
 */
function getOrCreateThesaurusKeyword($connection, $value, $scheme, $schemeURI, $valueURI, $language)
{
    // Prüft, ob das Schlüsselwort schon existiert
    $stmt = $connection->prepare("SELECT thesaurus_keywords_id FROM Thesaurus_Keywords WHERE keyword = ?");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $stmt->store_result();

    // Wenn es existiert, wird die ID geholt
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($thesaurus_keywords_id);
        $stmt->fetch();
    } else {
        // Wenn nicht, wird ein neues Schlüsselwort in die Datenbank eingefügt
        $stmt->close();
        $stmt = $connection->prepare("INSERT INTO Thesaurus_Keywords (`keyword`, `scheme`, `schemeURI`, `valueURI`, `language`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $value, $scheme, $schemeURI, $valueURI, $language);
        $stmt->execute();
        // Holt die ID des neu eingefügten Schlüsselworts
        $thesaurus_keywords_id = $stmt->insert_id;
    }
    $stmt->close();

    return $thesaurus_keywords_id; // Gibt die ID des Schlüsselworts zurück
}

/**
 * Verknüpft eine Ressource mit einem Thesaurus-Schlüsselwort.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $resource_id Die ID der Ressource.
 * @param int $thesaurus_keywords_id Die ID des Thesaurus-Schlüsselworts.
 *
 * @return void
 */
function linkResourceToThesaurusKeyword($connection, $resource_id, $thesaurus_keywords_id)
{
    // Fügt eine Verknüpfung zwischen der Ressource und dem Schlüsselwort in die Datenbank ein
    $stmt = $connection->prepare("INSERT INTO Resource_has_Thesaurus_Keywords (`Resource_resource_id`, `Thesaurus_Keywords_thesaurus_keywords_id`) VALUES (?, ?)");
    $stmt->bind_param("ii", $resource_id, $thesaurus_keywords_id);
    $stmt->execute();
    $stmt->close();
}
