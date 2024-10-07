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
function saveThesaurusKeywords($connection, $postData, $resource_id) {
    
    $fieldsToProcess = [
    // Thesaurus Keywords
    'thesaurusKeywords', 
    // General
    'GENMaterial', 'GENGeologicalAge', 'GENPoreFluid', 'GENGeologicalSetting',
    // Analogue modelling of geological processes
    'AMOGPModeledStructure', 'AMOGPModeledGeomorphologicalFeature', 'AMOGPApparatus', 'AMOGPAncillaryEquipment', 'AMOGPAMOGPMeasuredProperty', 'AMOGPSoftware',
    // Geochemistry
    'GEOCHAnalysisTechnique', 'GEOCHMeasuredProperty',
    // Microsocopy and tomography
    'MATomographyApparatus', 'MATomographyAncillaryEquipment', 'MATomographyAnalysisTechnique', 'MATomographyAnalyzedFeature', 'AMOGPMATomographyInferredParameter',
    // Paleomagnetism
    'PALTISApparatus', 'PALTISEnvironmentControl', 'PALTISMeasuredProperty', 'PALTISInferredBehavior',
    // Rock and melt physics
    'RAMPApparatus', 'RAMPAncillaryEquipment', 'RAMPMeasuredProperty', 'RAMPInferredDeformationBehavior'];

    foreach ($fieldsToProcess as $field) {
        if (isset($postData[$field]) && $postData[$field] !== '') {
            $fieldObject = $postData[$field];
            $fieldArray = json_decode($fieldObject, true);

            foreach ($fieldArray as $entry) {
                processThesaurusKeyword($connection, $entry, $resource_id);
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
 *
 * @return void
 */
function processThesaurusKeyword($connection, $entry, $resource_id) {
    $value = $entry['value'];
    $valueURI = isset($entry['id']) ? $entry['id'] : null;
    $scheme = isset($entry['scheme']) ? $entry['scheme'] : '';
    $schemeURI = isset($entry['schemeURI']) ? $entry['schemeURI'] : '';
    $language = isset($entry['language']) ? $entry['language'] : 'en';

    if (!empty($value)) {
        $thesaurus_keywords_id = getOrCreateThesaurusKeyword($connection, $value, $scheme, $schemeURI, $valueURI, $language);
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
function getOrCreateThesaurusKeyword($connection, $value, $scheme, $schemeURI, $valueURI, $language) {
    $stmt = $connection->prepare("SELECT thesaurus_keywords_id FROM Thesaurus_Keywords WHERE keyword = ?");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($thesaurus_keywords_id);
        $stmt->fetch();
    } else {
        $stmt->close();
        $stmt = $connection->prepare("INSERT INTO Thesaurus_Keywords (`keyword`, `scheme`, `schemeURI`, `valueURI`, `language`) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $value, $scheme, $schemeURI, $valueURI, $language);
        $stmt->execute();
        $thesaurus_keywords_id = $stmt->insert_id;
    }
    $stmt->close();

    return $thesaurus_keywords_id;
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
function linkResourceToThesaurusKeyword($connection, $resource_id, $thesaurus_keywords_id) {
    $stmt = $connection->prepare("INSERT INTO Resource_has_Thesaurus_Keywords (`Resource_resource_id`, `Thesaurus_Keywords_thesaurus_keywords_id`) VALUES (?, ?)");
    $stmt->bind_param("ii", $resource_id, $thesaurus_keywords_id);
    $stmt->execute();
    $stmt->close();
}