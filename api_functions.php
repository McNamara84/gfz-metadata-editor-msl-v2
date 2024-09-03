<?php
require 'vendor/autoload.php';
use EasyRdf\Graph;
use EasyRdf\RdfNamespace;

// MSL labs und zugehörige Affiliationen von
$url = 'https://raw.githubusercontent.com/UtrechtUniversity/msl_vocabularies/main/vocabularies/labs/labnames.json';
// abrufen und verarbeiten
function fetchAndProcessMslLabs()
{
    global $url;

    // Daten von der URL abrufen mit User-Agent
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: PHP Script'
        ]
    ];
    $context = stream_context_create($opts);
    $jsonData = file_get_contents($url, false, $context);

    if ($jsonData === false) {
        throw new Exception('Fehler beim Abrufen der Daten von GitHub: ' . error_get_last()['message']);
    }

    // Zeichenkodierung korrigieren
    $jsonData = mb_convert_encoding($jsonData, 'UTF-8', mb_detect_encoding($jsonData, 'UTF-8, ISO-8859-1', true));

    // JSON-Daten decodieren
    $labs = json_decode($jsonData, true);

    if ($labs === null) {
        throw new Exception('Fehler beim Decodieren der JSON-Daten: ' . json_last_error_msg());
    }

    // Daten verarbeiten und nur benötigte Felder behalten
    $processedLabs = array_map(function ($lab) {
        return [
            'name' => $lab['lab_editor_name'],
            'affiliation' => $lab['affiliation']
        ];
    }, $labs);

    return $processedLabs;
}

function transformAndSaveOrDownloadXml($id, $format, $download = false)
{
    $formatInfo = [
        'dif' => [
            'xsltFile' => 'MappingMapToDif.xslt',
            'outputPrefix' => 'dif'
        ],
        'iso' => [
            'xsltFile' => 'MappingMapToIso.xslt',
            'outputPrefix' => 'iso'
        ],
        'datacite' => [
            'xsltFile' => 'MappingMapToDataCiteSchema45.xslt',
            'outputPrefix' => 'datacite'
        ]
    ];

    if (!isset($formatInfo[$format])) {
        throw new Exception("Ungültiges Format.");
    }

    $inputXmlPath = "xml/resource_$id.xml";
    $xsltPath = "schemas/XSLT/" . $formatInfo[$format]['xsltFile'];
    $outputXmlPath = "xml/" . $formatInfo[$format]['outputPrefix'] . "_resource_$id.xml";

    // FreestyleXML temporär erstellen
    getResourceAsXml($GLOBALS['connection'], $id);

    // Überprüfen, ob die Eingabe-XML und XSLT-Datei existieren
    if (!file_exists($inputXmlPath) || !file_exists($xsltPath)) {
        throw new Exception("Erforderliche Dateien fehlen.");
    }

    // XML-Dokument und XSLT-Dokument laden
    $xml = new DOMDocument;
    $xml->load($inputXmlPath);
    $xsl = new DOMDocument;
    $xsl->load($xsltPath);

    // XSLT-Prozessor erstellen, konfigurieren und Transformation durchführen
    $proc = new XSLTProcessor;
    $proc->importStyleSheet($xsl);
    $newXml = $proc->transformToXML($xml);

    if ($newXml === false) {
        throw new Exception("Fehler bei der XSLT-Transformation.");
    }

    if ($download) {
        // Header für den Download setzen und Datei ausgeben
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . basename($outputXmlPath) . '"');
        header('Content-Length: ' . strlen($newXml));
        echo $newXml;
        exit();
    } else {
        // XML-String zurückgeben
        return $newXml;
    }
}

function getResourceType($connection, $resource_type_id)
{
    $stmt = $connection->prepare("
        SELECT * FROM Resource_Type 
        WHERE resource_name_id = ?
    ");
    $stmt->bind_param('i', $resource_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getRights($connection, $rightsIdentifier)
{
    $stmt = $connection->prepare("
        SELECT * FROM Rights 
        WHERE rightsIdentifier = ?
    ");
    $stmt->bind_param('s', $rightsIdentifier);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getLanguage($connection, $language_id)
{
    $stmt = $connection->prepare("
        SELECT * FROM Language 
        WHERE language_id = ?
    ");
    $stmt->bind_param('i', $language_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getThesaurusKeywords($connection, $resource_id)
{
    $stmt = $connection->prepare("
        SELECT tk.*
        FROM Resource_has_Thesaurus_Keywords rhtk
        JOIN Thesaurus_Keywords tk ON rhtk.Thesaurus_Keywords_thesaurus_keywords_id = tk.thesaurus_keywords_id
        WHERE rhtk.Resource_resource_id = ?
    ");
    $stmt->bind_param('i', $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getRelatedData($connection, $table, $idColumn, $id)
{
    $stmt = $connection->prepare("SELECT * FROM $table WHERE $idColumn = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getRelatedDataMultiple($connection, $table, $foreignKeyColumn, $id)
{
    $stmt = $connection->prepare("SELECT * FROM $table WHERE $foreignKeyColumn = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAuthorAffiliations($connection, $author_id)
{
    $affiliations = [];
    $stmt = $connection->prepare("
        SELECT a.*
        FROM Affiliation a
        JOIN Author_has_Affiliation aha ON a.affiliation_id = aha.Affiliation_affiliation_id
        WHERE aha.Author_author_id = ?
    ");
    $stmt->bind_param('i', $author_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $affiliations[] = $row;
    }
    return $affiliations;
}

function getAuthors($connection, $resource_id)
{
    $authors = [];
    $stmt = $connection->prepare("
        SELECT a.*, rha.Resource_has_Author_id
        FROM Author a
        JOIN Resource_has_Author rha ON a.author_id = rha.Author_author_id
        WHERE rha.Resource_resource_id = ?
    ");
    $stmt->bind_param('i', $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $author = [
            'familyname' => $row['familyname'] ?? null,
            'givenname' => $row['givenname'] ?? null,
            'orcid' => $row['orcid'] ?? null,
            'Affiliations' => getAuthorAffiliations($connection, $row['author_id'])
        ];
        $authors[] = $author;
    }
    return $authors;
}

function getTitles($connection, $resource_id)
{
    $stmt = $connection->prepare("
        SELECT t.*, tt.name as title_type_name
        FROM Title t
        JOIN Title_Type tt ON t.Title_Type_fk = tt.title_type_id
        WHERE t.Resource_resource_id = ?
    ");
    $stmt->bind_param('i', $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getDescriptions($connection, $resource_id)
{
    $stmt = $connection->prepare("
        SELECT * FROM Description
        WHERE resource_id = ?
    ");
    $stmt->bind_param('i', $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getContributorPersonAffiliations($connection, $contributor_person_id)
{
    $affiliations = [];
    $stmt = $connection->prepare("
        SELECT a.*
        FROM Affiliation a
        JOIN Contributor_Person_has_Affiliation cpha ON a.affiliation_id = cpha.Affiliation_affiliation_id
        WHERE cpha.Contributor_Person_contributor_person_id = ?
    ");
    $stmt->bind_param('i', $contributor_person_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $affiliations[] = [
            'affiliation_id' => $row['affiliation_id'] ?? null,
            'name' => $row['name'] ?? null,
            'city' => $row['city'] ?? null,
            'country' => $row['country'] ?? null,
            'rorId' => $row['rorId'] ?? null
        ];
    }

    return $affiliations;
}

function getContributorPersonRoles($connection, $contributor_person_id)
{
    $roles = [];
    $stmt = $connection->prepare("
        SELECT r.*
        FROM Role r
        JOIN Contributor_Person_has_Role cphr ON r.role_id = cphr.Role_role_id
        WHERE cphr.Contributor_Person_contributor_person_id = ?
    ");
    $stmt->bind_param('i', $contributor_person_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $roles[] = [
            'name' => $row['name'] ?? null
        ];
    }
    return $roles;
}

function getContributorInstitutionAffiliations($connection, $contributor_institution_id)
{
    $affiliations = [];
    $stmt = $connection->prepare("
        SELECT a.*
        FROM Affiliation a
        JOIN Contributor_Institution_has_Affiliation ciha ON a.affiliation_id = ciha.Affiliation_affiliation_id
        WHERE ciha.Contributor_Institution_contributor_institution_id = ?
    ");
    $stmt->bind_param('i', $contributor_institution_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $affiliations[] = [
            'affiliation_id' => $row['affiliation_id'] ?? null,
            'name' => $row['name'] ?? null,
            'city' => $row['city'] ?? null,
            'country' => $row['country'] ?? null,
            'rorId' => $row['rorId'] ?? null
        ];
    }
    return $affiliations;
}

function getContributorInstitutionRoles($connection, $contributor_institution_id)
{
    $roles = [];
    $stmt = $connection->prepare("
        SELECT r.*
        FROM Role r
        JOIN Contributor_Institution_has_Role cihr ON r.role_id = cihr.Role_role_id
        WHERE cihr.Contributor_Institution_contributor_institution_id = ?
    ");
    $stmt->bind_param('i', $contributor_institution_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $roles[] = [
            'name' => $row['name'] ?? null
        ];
    }
    return $roles;
}

function getContributors($connection, $resource_id)
{
    $contributors = ['persons' => [], 'institutions' => []];
    $stmt = $connection->prepare("
        SELECT cp.*, rhcp.Resource_has_Contributor_Person_id
        FROM Contributor_Person cp
        JOIN Resource_has_Contributor_Person rhcp ON cp.contributor_person_id = rhcp.Contributor_Person_contributor_person_id
        WHERE rhcp.Resource_resource_id = ?
    ");
    $stmt->bind_param('i', $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $person = [
            'familyname' => $row['familyname'] ?? null,
            'givenname' => $row['givenname'] ?? null,
            'orcid' => $row['orcid'] ?? null,
            'Affiliations' => getContributorPersonAffiliations($connection, $row['contributor_person_id']),
            'Roles' => getContributorPersonRoles($connection, $row['contributor_person_id'])
        ];
        $contributors['persons'][] = $person;
    }
    $stmt = $connection->prepare("
        SELECT ci.*, rhci.Resource_has_Contributor_Institution_id
        FROM Contributor_Institution ci
        JOIN Resource_has_Contributor_Institution rhci ON ci.contributor_institution_id = rhci.Contributor_Institution_contributor_institution_id
        WHERE rhci.Resource_resource_id = ?
    ");
    $stmt->bind_param('i', $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $institution = [
            'name' => $row['name'] ?? null,
            'Affiliations' => getContributorInstitutionAffiliations($connection, $row['contributor_institution_id']),
            'Roles' => getContributorInstitutionRoles($connection, $row['contributor_institution_id'])
        ];
        $contributors['institutions'][] = $institution;
    }
    return $contributors;
}

function getContactPersons($connection, $resource_id)
{
    $contactPersons = [];
    $stmt = $connection->prepare("
        SELECT cp.*, rhcp.Resource_has_Contact_Person_id
        FROM Contact_Person cp
        JOIN Resource_has_Contact_Person rhcp ON cp.contact_person_id = rhcp.Contact_Person_contact_person_id
        WHERE rhcp.Resource_resource_id = ?
    ");
    $stmt->bind_param('i', $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $contactPerson = [
            'familyname' => $row['familyname'] ?? null,
            'givenname' => $row['givenname'] ?? null,
            'position' => $row['position'] ?? null,
            'email' => $row['email'] ?? null,
            'website' => $row['website'] ?? null,
            'Affiliations' => getContactPersonAffiliations($connection, $row['contact_person_id'])
        ];
        $contactPersons[] = $contactPerson;
    }
    return $contactPersons;
}

function getContactPersonAffiliations($connection, $contact_person_id)
{
    $affiliations = [];
    $stmt = $connection->prepare("
        SELECT a.*
        FROM Affiliation a
        JOIN Contact_Person_has_Affiliation cpha ON a.affiliation_id = cpha.Affiliation_affiliation_id
        WHERE cpha.Contact_Person_contact_person_id = ?
    ");
    $stmt->bind_param('i', $contact_person_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $affiliations[] = [
            'affiliation_id' => $row['affiliation_id'] ?? null,
            'name' => $row['name'] ?? null,
            'city' => $row['city'] ?? null,
            'country' => $row['country'] ?? null,
            'rorId' => $row['rorId'] ?? null
        ];
    }
    return $affiliations;
}

function getFundingReferences($connection, $resource_id)
{
    $funding_references = getRelatedDataMultiple($connection, 'Resource_has_Funding_Reference', 'Resource_resource_id', $resource_id);
    foreach ($funding_references as &$reference) {
        $reference_data = getRelatedData($connection, 'Funding_Reference', 'funding_reference_id', $reference['Funding_Reference_funding_reference_id']);
        if ($reference_data) {
            $reference = array_merge($reference, $reference_data);
        }
    }
    return $funding_references;
}

function getRelatedWorks($connection, $resource_id)
{
    $stmt = $connection->prepare("
        SELECT rw.*, r.name as relation_name, it.name as identifier_type_name
        FROM Resource_has_Related_Work rhrw
        JOIN Related_Work rw ON rhrw.Related_Work_related_work_id = rw.related_work_id
        LEFT JOIN Relation r ON rw.relation_fk = r.relation_id
        LEFT JOIN Identifier_Type it ON rw.identifier_type_fk = it.identifier_type_id
        WHERE rhrw.Resource_resource_id = ?
    ");
    $stmt->bind_param('i', $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $relatedWorks = [];
    while ($row = $result->fetch_assoc()) {
        $relatedWorks[] = [
            'Identifier' => $row['Identifier'],
            'Relation' => ['name' => $row['relation_name']],
            'IdentifierType' => ['name' => $row['identifier_type_name']]
        ];
    }
    return $relatedWorks;
}

function getSpatialTemporalCoverage($connection, $resource_id)
{
    $stmt = $connection->prepare("
        SELECT stc.*
        FROM Resource_has_Spatial_Temporal_Coverage rhstc
        JOIN Spatial_Temporal_Coverage stc ON rhstc.Spatial_Temporal_Coverage_spatial_temporal_coverage_id = stc.spatial_temporal_coverage_id
        WHERE rhstc.Resource_resource_id = ?
    ");
    $stmt->bind_param('i', $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getFreeKeywords($connection, $resource_id)
{
    $stmt = $connection->prepare("
        SELECT fk.free_keywords_id, fk.free_keyword, fk.isCurated
        FROM Free_Keywords fk
        JOIN Resource_has_Free_Keywords rhfk ON fk.free_keywords_id = rhfk.Free_Keywords_free_keywords_id
        WHERE rhfk.Resource_resource_id = ?
    ");
    $stmt->bind_param('i', $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Speichern einer Resource als XML-Datei nach unserem eigenen "Freestyle"-Schema
function getResourceAsXml($connection, $id)
{
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $stmt = $connection->prepare('SELECT * FROM Resource WHERE resource_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();

    if (!$resource) {
        throw new Exception("Resource nicht gefunden!");
    }

    $xml = new SimpleXMLElement('<Resource/>');

    // Resource Information
    $xml->addChild('doi', htmlspecialchars($resource['doi']));
    $xml->addChild('version', htmlspecialchars($resource['version']));
    $xml->addChild('year', htmlspecialchars($resource['year']));
    $xml->addChild('dateCreated', htmlspecialchars($resource['dateCreated']));
    $xml->addChild('dateEmbargoUntil', htmlspecialchars($resource['dateEmbargoUntil']));

    // Rights
    $defaultRightsIdentifier = "CC-BY-4.0";
    if (isset($_POST['Rights']) && !empty($_POST['Rights'])) {
        $rightsIdentifier = $_POST['Rights'];
    } else {
        $rightsIdentifier = $defaultRightsIdentifier;
        error_log("Kein Rights-Wert übermittelt, verwende Standardwert: " . $defaultRightsIdentifier);
    }
    $rights = getRights($connection, $rightsIdentifier);
    if ($rights) {
        $rightsXml = $xml->addChild('Rights');
        foreach ($rights as $key => $value) {
            $rightsXml->addChild($key, htmlspecialchars($value));
        }
    } else {
        error_log("Keine Rechte gefunden für rightsIdentifier: " . $rightsIdentifier);
    }

    // Aktualisieren Sie $resource mit dem verwendeten rightsIdentifier
    $resource['Rights_rights_id'] = $rightsIdentifier;

    // Resource Type
    $resourceType = getResourceType($connection, $resource['Resource_Type_resource_name_id']);
    $resourceTypeXml = $xml->addChild('ResourceType');
    foreach ($resourceType as $key => $value) {
        $resourceTypeXml->addChild($key, htmlspecialchars($value));
    }

    // Language
    $language = getLanguage($connection, $resource['Language_language_id']);
    $languageXml = $xml->addChild('Language');
    foreach ($language as $key => $value) {
        $languageXml->addChild($key, htmlspecialchars($value));
    }

    // Titles
    $titles = getTitles($connection, $id);
    $titlesXml = $xml->addChild('Titles');
    foreach ($titles as $title) {
        $titleXml = $titlesXml->addChild('Title');
        $titleXml->addChild('text', htmlspecialchars($title['text']));
        $titleXml->addChild('type', htmlspecialchars($title['title_type_name']));
    }

    // Authors
    $authors = getAuthors($connection, $id);
    $authorsXml = $xml->addChild('Authors');
    foreach ($authors as $author) {
        $authorXml = $authorsXml->addChild('Author');

        $authorXml->addChild('familyname', htmlspecialchars($author['familyname'] ?? ''));
        $authorXml->addChild('givenname', htmlspecialchars($author['givenname'] ?? ''));

        // ORCID hinzufügen
        $orcid = $author['orcid'] ?? null;
        if ($orcid !== null && $orcid !== '') {
            $authorXml->addChild('orcid', htmlspecialchars($orcid));
        }

        if (isset($author['Affiliations']) && is_array($author['Affiliations'])) {
            $affiliationsXml = $authorXml->addChild('Affiliations');
            foreach ($author['Affiliations'] as $affiliation) {
                $affiliationXml = $affiliationsXml->addChild('Affiliation');
                foreach ($affiliation as $key => $value) {
                    $affiliationXml->addChild($key, htmlspecialchars($value ?? ''));
                }
            }
        }
    }


    // Descriptions
    $descriptions = getDescriptions($connection, $id);
    $descriptionsXml = $xml->addChild('Descriptions');
    foreach ($descriptions as $description) {
        $descriptionXml = $descriptionsXml->addChild('Description');
        $descriptionXml->addChild('type', htmlspecialchars($description['type']));
        $descriptionXml->addChild('description', htmlspecialchars($description['description']));
    }

    // Contributors
    $contributors = getContributors($connection, $id);
    $contributorsXml = $xml->addChild('Contributors');

    // Contributor Persons
    $personsXml = $contributorsXml->addChild('Persons');
    foreach ($contributors['persons'] as $person) {
        $personXml = $personsXml->addChild('Person');
        $personXml->addChild('familyname', htmlspecialchars($person['familyname'] ?? ''));
        $personXml->addChild('givenname', htmlspecialchars($person['givenname'] ?? ''));

        $orcid = $person['orcid'] ?? null;
        if ($orcid !== null && $orcid !== '') {
            $personXml->addChild('orcid', htmlspecialchars($orcid));
        }

        if (isset($person['Affiliations']) && is_array($person['Affiliations'])) {
            $affiliationsXml = $personXml->addChild('Affiliations');
            foreach ($person['Affiliations'] as $affiliation) {
                $affiliationXml = $affiliationsXml->addChild('Affiliation');
                foreach ($affiliation as $key => $value) {
                    $affiliationXml->addChild($key, htmlspecialchars($value ?? ''));
                }
            }
        }

        if (isset($person['Roles']) && is_array($person['Roles'])) {
            $rolesXml = $personXml->addChild('Roles');
            foreach ($person['Roles'] as $role) {
                $roleXml = $rolesXml->addChild('Role');
                $roleXml->addChild('name', htmlspecialchars($role['name'] ?? ''));
            }
        }
    }

    // Contributor Institutions
    $institutionsXml = $contributorsXml->addChild('Institutions');
    foreach ($contributors['institutions'] as $institution) {
        $institutionXml = $institutionsXml->addChild('Institution');
        $institutionXml->addChild('name', htmlspecialchars($institution['name'] ?? ''));

        if (isset($institution['Affiliations']) && is_array($institution['Affiliations'])) {
            $affiliationsXml = $institutionXml->addChild('Affiliations');
            foreach ($institution['Affiliations'] as $affiliation) {
                $affiliationXml = $affiliationsXml->addChild('Affiliation');
                foreach ($affiliation as $key => $value) {
                    $affiliationXml->addChild($key, htmlspecialchars($value ?? ''));
                }
            }
        }

        if (isset($institution['Roles']) && is_array($institution['Roles'])) {
            $rolesXml = $institutionXml->addChild('Roles');
            foreach ($institution['Roles'] as $role) {
                $roleXml = $rolesXml->addChild('Role');
                $roleXml->addChild('name', htmlspecialchars($role['name'] ?? ''));
            }
        }
    }

    // Contact Persons
    $contactPersons = getContactPersons($connection, $id);
    $contactPersonsXml = $xml->addChild('ContactPersons');
    foreach ($contactPersons as $contactPerson) {
        $contactPersonXml = $contactPersonsXml->addChild('ContactPerson');
        $contactPersonXml->addChild('familyname', htmlspecialchars($contactPerson['familyname'] ?? ''));
        $contactPersonXml->addChild('givenname', htmlspecialchars($contactPerson['givenname'] ?? ''));
        $contactPersonXml->addChild('position', htmlspecialchars($contactPerson['position'] ?? ''));
        $contactPersonXml->addChild('email', htmlspecialchars($contactPerson['email'] ?? ''));
        $contactPersonXml->addChild('website', htmlspecialchars($contactPerson['website'] ?? ''));

        if (isset($contactPerson['Affiliations']) && is_array($contactPerson['Affiliations'])) {
            $affiliationsXml = $contactPersonXml->addChild('Affiliations');
            foreach ($contactPerson['Affiliations'] as $affiliation) {
                $affiliationXml = $affiliationsXml->addChild('Affiliation');
                foreach ($affiliation as $key => $value) {
                    $affiliationXml->addChild($key, htmlspecialchars($value ?? ''));
                }
            }
        }
    }

    // Related Works
    $relatedWorks = getRelatedWorks($connection, $id);
    $relatedWorksXml = $xml->addChild('RelatedWorks');
    foreach ($relatedWorks as $work) {
        $workXml = $relatedWorksXml->addChild('RelatedWork');
        $workXml->addChild('Identifier', htmlspecialchars($work['Identifier']));

        $relationXml = $workXml->addChild('Relation');
        $relationXml->addChild('name', htmlspecialchars($work['Relation']['name']));

        $identifierTypeXml = $workXml->addChild('IdentifierType');
        $identifierTypeXml->addChild('name', htmlspecialchars($work['IdentifierType']['name']));
    }

    // Spatial Temporal Coverages
    $spatialTemporalCoverages = getSpatialTemporalCoverage($connection, $id);
    $coveragesXml = $xml->addChild('SpatialTemporalCoverages');
    foreach ($spatialTemporalCoverages as $coverage) {
        $coverageXml = $coveragesXml->addChild('SpatialTemporalCoverage');
        foreach ($coverage as $key => $value) {
            $coverageXml->addChild($key, htmlspecialchars($value));
        }
    }

    // Thesaurus Keywords
    $thesaurusKeywords = getThesaurusKeywords($connection, $id);
    $keywordsXml = $xml->addChild('ThesaurusKeywords');
    foreach ($thesaurusKeywords as $keyword) {
        $keywordXml = $keywordsXml->addChild('Keyword');
        foreach ($keyword as $key => $value) {
            $keywordXml->addChild($key, htmlspecialchars($value));
        }
    }

    // Free Keywords
    $freeKeywords = getFreeKeywords($connection, $id);
    $freeKeywordsXml = $xml->addChild('FreeKeywords');
    foreach ($freeKeywords as $keyword) {
        $keywordXml = $freeKeywordsXml->addChild('Keyword');
        $keywordXml->addChild('free_keywords_id', htmlspecialchars($keyword['free_keywords_id']));
        $keywordXml->addChild('free_keyword', htmlspecialchars($keyword['free_keyword']));
        $keywordXml->addChild('isCurated', htmlspecialchars($keyword['isCurated']));
    }

    // Funding References
    $fundingReferences = getFundingReferences($connection, $id);
    $fundingReferencesXml = $xml->addChild('FundingReferences');
    foreach ($fundingReferences as $reference) {
        $referenceXml = $fundingReferencesXml->addChild('FundingReference');
        foreach ($reference as $key => $value) {
            $referenceXml->addChild($key, htmlspecialchars($value));
        }
    }

    // XML hübsch formatieren
    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = true;

    // XML-Datei speichern
    $dom->save('xml/resource_' . $id . '.xml');

    // DB-Verbindung schließen
    $stmt->close();

    return $xml->asXML();
}

function fetchAndProcessCGIKeywords()
{
    $url = 'https://geosciml.org/resource/vocabulary/cgi/2016/simplelithology.rdf';

    // RDF-Namensräume registrieren
    RdfNamespace::set('skos', 'http://www.w3.org/2004/02/skos/core#');
    RdfNamespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');

    // RDF-Daten laden
    $graph = new Graph($url);
    $graph->load();

    $keywordMap = [];

    // Alle SKOS-Konzepte durchlaufen
    foreach ($graph->allOfType('skos:Concept') as $concept) {
        $id = $concept->getUri();
        $prefLabel = (string) $concept->get('skos:prefLabel');
        $definition = (string) $concept->get('skos:definition');

        $keywordMap[$id] = [
            'id' => $id,
            'text' => $prefLabel,
            'language' => 'en',
            'scheme' => 'CGI Simple Lithology',
            'schemeURI' => 'https://geosciml.org/resource/vocabulary/cgi/2016/simplelithology',
            'description' => $definition,
            'children' => []
        ];
    }

    // Hierarchie erstellen
    $rootId = 'http://resource.geosciml.org/classifier/cgi/lithology/compound_material';
    foreach ($graph->allOfType('skos:Concept') as $concept) {
        $id = $concept->getUri();
        if ($id === $rootId) {
            continue; // Überspringen Sie das Wurzelelement
        }
        $broaderConcepts = $concept->all('skos:broader');
        if (empty($broaderConcepts)) {
            // Wenn kein breiteres Konzept gefunden wird, fügen Sie es dem Wurzelelement hinzu
            $keywordMap[$rootId]['children'][] = &$keywordMap[$id];
        } else {
            foreach ($broaderConcepts as $broaderConcept) {
                $parentId = $broaderConcept->getUri();
                if (isset($keywordMap[$parentId])) {
                    $keywordMap[$parentId]['children'][] = &$keywordMap[$id];
                    break; // Nur einmal hinzufügen
                }
            }
        }
    }

    // Nur das Wurzelelement zurückgeben
    return [$keywordMap[$rootId]];
}

function fetchRdfData($conceptScheme, $pageNum, $pageSize)
{
    $url = "https://gcmd.earthdata.nasa.gov/kms/concepts/concept_scheme/{$conceptScheme}?format=rdf&page_num={$pageNum}&page_size={$pageSize}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Fehler beim Abrufen der Thesaurus Keywords. HTTP-Statuscode: {$httpCode}");
    }

    return $response;
}

function buildHierarchy($graph, $conceptScheme, $schemeName)
{
    $hierarchy = [];
    $concepts = $graph->allOfType('skos:Concept');
    $conceptMap = [];

    $schemeURI = "https://gcmd.earthdata.nasa.gov/kms/concepts/concept_scheme/{$conceptScheme}";

    foreach ($concepts as $concept) {
        $uri = $concept->getUri();
        $label = $concept->getLiteral('skos:prefLabel') ? $concept->getLiteral('skos:prefLabel')->getValue() : '';
        $lang = $concept->getLiteral('skos:prefLabel') ? $concept->getLiteral('skos:prefLabel')->getLang() : '';
        $description = $concept->getLiteral('skos:definition', 'en') ? $concept->getLiteral('skos:definition', 'en')->getValue() : '';
        $conceptMap[$uri] = [
            'id' => $uri,
            'text' => $label,
            'language' => $lang,
            'scheme' => $schemeName,
            'schemeURI' => $schemeURI,
            'description' => $description,
            'children' => []
        ];
    }

    foreach ($concepts as $concept) {
        $uri = $concept->getUri();
        $broader = $concept->getResource('skos:broader');
        if ($broader) {
            $broaderUri = $broader->getUri();
            if (isset($conceptMap[$broaderUri])) {
                $conceptMap[$broaderUri]['children'][] = &$conceptMap[$uri];
            }
        } else {
            $hierarchy[] = &$conceptMap[$uri];
        }
    }

    return $hierarchy;
}

function saveJsonToFile($data, $filePath)
{
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function processKeywords($conceptScheme, $schemeName, $outputFile)
{
    $pageNum = 1;
    $pageSize = 2000;
    $graph = new Graph();
    while (true) {
        try {
            $data = fetchRdfData($conceptScheme, $pageNum, $pageSize);
            $tempGraph = new Graph();
            $tempGraph->parse($data, 'rdf');

            foreach ($tempGraph->resources() as $resource) {
                foreach ($tempGraph->properties($resource) as $property) {
                    foreach ($tempGraph->all($resource, $property) as $value) {
                        $graph->add($resource, $property, $value);
                    }
                }
            }

            if (strpos($data, '<skos:Concept') === false) {
                break;
            }
            $pageNum++;
        } catch (Exception $e) {
            if ($pageNum == 1) {
                throw $e;
            } else {
                break;
            }
        }
    }
    $hierarchicalData = buildHierarchy($graph, $conceptScheme, $schemeName);
    saveJsonToFile($hierarchicalData, $outputFile);

    echo "{$schemeName} erfolgreich aktualisiert";
}

function saveKeywordsToJson($connection, $filename, $curationType)
{
    if ($connection === null) {
        die('Datenbankverbindung ist nicht initialisiert.');
    }

    $sql = 'SELECT free_keywords_id, free_keyword, isCurated FROM Free_Keywords';
    if ($curationType === 'isCurated') {
        $sql .= ' WHERE isCurated = 1';
    } elseif ($curationType !== 'all') {
        die('Ungültiger curationType');
    }

    $result = $connection->query($sql);

    if ($result && $result->num_rows > 0) {
        $keywords = $result->fetch_all(MYSQLI_ASSOC);
        $jsonString = json_encode($keywords);

        if (file_put_contents($filename, $jsonString) !== false) {
            echo 'Keywords erfolgreich in ' . $filename . ' gespeichert.';
        } else {
            echo 'Fehler beim Speichern der Datei ' . $filename;
        }
    } else {
        echo 'Keine Keywords gefunden.';
    }
}
