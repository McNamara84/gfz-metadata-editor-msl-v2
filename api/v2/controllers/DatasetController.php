<?php
require_once __DIR__ . '/../../../settings.php';

class DatasetController
{
    private $connection;

    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }

    /**
     * Retrieves thesaurus keywords for a given resource.
     *
     * @param mysqli $connection The database connection.
     * @param int $resource_id The ID of the resource.
     * @return array An array of thesaurus keywords.
     */
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

    /**
     * Retrieves related data for a single record from a specified table.
     *
     * @param mysqli $connection The database connection.
     * @param string $table The name of the table.
     * @param string $idColumn The name of the ID column.
     * @param int $id The ID value to search for.
     * @return array|null An associative array of the related data, or null if not found.
     */
    function getRelatedData($connection, $table, $idColumn, $id)
    {
        $stmt = $connection->prepare("SELECT * FROM $table WHERE $idColumn = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Retrieves multiple related data records from a specified table.
     *
     * @param mysqli $connection The database connection.
     * @param string $table The name of the table.
     * @param string $foreignKeyColumn The name of the foreign key column.
     * @param int $id The ID value to search for.
     * @return array An array of associative arrays containing the related data.
     */
    function getRelatedDataMultiple($connection, $table, $foreignKeyColumn, $id)
    {
        $stmt = $connection->prepare("SELECT * FROM $table WHERE $foreignKeyColumn = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Retrieves affiliations for a given author.
     *
     * @param mysqli $connection The database connection.
     * @param int $author_id The ID of the author.
     * @return array An array of affiliations for the author.
     */
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

    /**
     * Retrieves authors for a given resource.
     *
     * @param mysqli $connection The database connection.
     * @param int $resource_id The ID of the resource.
     * @return array An array of authors with their details and affiliations.
     */
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
                'Affiliations' => $this->getAuthorAffiliations($connection, $row['author_id'])
            ];
            $authors[] = $author;
        }
        return $authors;
    }

    /**
     * Retrieves titles for a given resource.
     *
     * @param mysqli $connection The database connection.
     * @param int $resource_id The ID of the resource.
     * @return array An array of titles with their types.
     */
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

    /**
     * Retrieves descriptions for a given resource.
     *
     * @param mysqli $connection The database connection.
     * @param int $resource_id The ID of the resource.
     * @return array An array of descriptions.
     */
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

    /**
     * Retrieves affiliations for a given contributor person.
     *
     * @param mysqli $connection The database connection.
     * @param int $contributor_person_id The ID of the contributor person.
     * @return array An array of affiliations for the contributor person.
     */
    function getContributorPersonAffiliations($connection, $contributor_person_id)
    {
        $affiliations = [];
        $stmt = $connection->prepare("
        SELECT a.affiliation_id, a.name, a.rorId
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
                'rorId' => $row['rorId'] ?? null
            ];
        }
        return $affiliations;
    }

    /**
     * Retrieves roles for a given contributor person.
     *
     * @param mysqli $connection The database connection.
     * @param int $contributor_person_id The ID of the contributor person.
     * @return array An array of roles for the contributor person.
     */
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

    /**
     * Retrieves affiliations for a given contributor institution.
     *
     * @param mysqli $connection The database connection.
     * @param int $contributor_institution_id The ID of the contributor institution.
     * @return array An array of affiliations for the contributor institution.
     */
    function getContributorInstitutionAffiliations($connection, $contributor_institution_id)
    {
        $affiliations = [];
        $stmt = $connection->prepare("
        SELECT a.affiliation_id, a.name, a.rorId
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
                'rorId' => $row['rorId'] ?? null
            ];
        }
        return $affiliations;
    }

    /**
     * Retrieves roles for a given contributor institution.
     *
     * @param mysqli $connection The database connection.
     * @param int $contributor_institution_id The ID of the contributor institution.
     * @return array An array of roles for the contributor institution.
     */
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

    /**
     * Retrieves contributors (persons and institutions) for a given resource.
     *
     * @param mysqli $connection The database connection.
     * @param int $resource_id The ID of the resource.
     * @return array An array containing two sub-arrays: 'persons' and 'institutions'.
     */
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
                'Affiliations' => $this->getContributorPersonAffiliations($connection, $row['contributor_person_id']),
                'Roles' => $this->getContributorPersonRoles($connection, $row['contributor_person_id'])
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
                'Affiliations' => $this->getContributorInstitutionAffiliations($connection, $row['contributor_institution_id']),
                'Roles' => $this->getContributorInstitutionRoles($connection, $row['contributor_institution_id'])
            ];
            $contributors['institutions'][] = $institution;
        }
        return $contributors;
    }

    /**
     * Retrieves contact persons for a given resource.
     *
     * @param mysqli $connection The database connection.
     * @param int $resource_id The ID of the resource.
     * @return array An array of contact persons with their details and affiliations.
     */
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
                'Affiliations' => $this->getContactPersonAffiliations($connection, $row['contact_person_id'])
            ];
            $contactPersons[] = $contactPerson;
        }
        return $contactPersons;
    }

    /**
     * Retrieves affiliations for a given contact person.
     *
     * @param mysqli $connection The database connection.
     * @param int $contact_person_id The ID of the contact person.
     * @return array An array of affiliations for the contact person.
     */
    function getContactPersonAffiliations($connection, $contact_person_id)
    {
        $affiliations = [];
        $stmt = $connection->prepare("
        SELECT a.affiliation_id, a.name, a.rorId
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
                'rorId' => $row['rorId'] ?? null
            ];
        }
        return $affiliations;
    }

    /**
     * Retrieves funding references for a given resource.
     *
     * @param mysqli $connection The database connection.
     * @param int $resource_id The ID of the resource.
     * @return array An array of funding references.
     */
    function getFundingReferences($connection, $resource_id)
    {
        $funding_references = $this->getRelatedDataMultiple($connection, 'Resource_has_Funding_Reference', 'Resource_resource_id', $resource_id);
        foreach ($funding_references as &$reference) {
            $reference_data = $this->getRelatedData($connection, 'Funding_Reference', 'funding_reference_id', $reference['Funding_Reference_funding_reference_id']);
            if ($reference_data) {
                $reference = array_merge($reference, $reference_data);
            }
        }
        return $funding_references;
    }

    /**
     * Retrieves related works for a given resource.
     *
     * @param mysqli $connection The database connection.
     * @param int $resource_id The ID of the resource.
     * @return array An array of related works with their identifiers, relations, and identifier types.
     */
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

    /**
     * Retrieves spatial temporal coverage for a given resource.
     *
     * @param mysqli $connection The database connection.
     * @param int $resource_id The ID of the resource.
     * @return array An array of spatial temporal coverage data.
     */
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

    /**
     * Retrieves free keywords for a given resource.
     *
     * @param mysqli $connection The database connection.
     * @param int $resource_id The ID of the resource.
     * @return array An array of free keywords.
     */
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

    /**
     * Retrieves originating laboratories for a given resource.
     *
     * @param mysqli $connection The database connection.
     * @param int $resource_id The ID of the resource.
     * @return array An array of originating laboratories with their details and affiliations.
     */
    function getOriginatingLaboratories($connection, $resource_id)
    {
        $laboratories = [];
        $stmt = $connection->prepare("
        SELECT ol.*
        FROM Originating_Laboratory ol
        JOIN Resource_has_Originating_Laboratory rhol ON ol.Originating_Laboratory_id = rhol.Originating_Laboratory_originating_laboratory_id
        WHERE rhol.Resource_resource_id = ?
    ");
        $stmt->bind_param('i', $resource_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $laboratory = [
                'laboratoryname' => $row['laboratoryname'],
                'labId' => $row['labId'],
                'Affiliations' => $this->getOriginatingLaboratoryAffiliations($connection, $row['originating_laboratory_id'])
            ];
            $laboratories[] = $laboratory;
        }
        return $laboratories;
    }

    /**
     * Retrieves affiliations for a given originating laboratory.
     *
     * @param mysqli $connection The database connection.
     * @param int $originating_laboratory_id The ID of the originating laboratory.
     * @return array An array of affiliations for the originating laboratory.
     */
    function getOriginatingLaboratoryAffiliations($connection, $originating_laboratory_id)
    {
        $affiliations = [];
        $stmt = $connection->prepare("
        SELECT a.*
        FROM Affiliation a
        JOIN Originating_Laboratory_has_Affiliation olha ON a.affiliation_id = olha.Affiliation_affiliation_id
        WHERE olha.Originating_Laboratory_originating_laboratory_id = ?
    ");
        $stmt->bind_param('i', $originating_laboratory_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $affiliations[] = $row;
        }
        return $affiliations;
    }

    /**
     * Generates an XML representation of a resource and saves it to a xml file without any scheme.
     *
     * @param mysqli $connection The database connection.
     * @param int $id The ID of the resource.
     * @return string The XML representation of the resource as a string.
     * @throws Exception If the resource is not found.
     */
    function getResourceAsXml($connection, $id)
    {
        $stmt = $connection->prepare('SELECT * FROM Resource WHERE resource_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $resource = $result->fetch_assoc();

        if (!$resource) {
            throw new Exception("Resource nicht gefunden!");
        }

        $xml = new SimpleXMLElement('<Resource/>');
        $xml->addChild('currentDate', date('Y-m-d'));

        // Resource Information
        $xml->addChild('doi', htmlspecialchars($resource['doi'] ?? ''));
        $xml->addChild('version', htmlspecialchars($resource['version'] ?? ''));
        $xml->addChild('year', htmlspecialchars($resource['year']));
        $xml->addChild('dateCreated', htmlspecialchars($resource['dateCreated']));
        $xml->addChild('dateEmbargoUntil', htmlspecialchars($resource['dateEmbargoUntil'] ?? ''));

        // Rights
        $rights = $this->getRelatedData($connection, 'Rights', 'rights_id', $resource['Rights_rights_id']);
        if ($rights) {
            $rightsXml = $xml->addChild('Rights');
            foreach ($rights as $key => $value) {
                $rightsXml->addChild($key, htmlspecialchars($value ?? ''));
            }
        }

        // Resource Type
        $resourceType = $this->getRelatedData($connection, 'Resource_Type', 'resource_name_id', $resource['Resource_Type_resource_name_id']);
        $resourceTypeXml = $xml->addChild('ResourceType');
        foreach ($resourceType as $key => $value) {
            $resourceTypeXml->addChild($key, htmlspecialchars($value ?? ''));
        }

        // Language
        $language = $this->getRelatedData($connection, 'Language', 'language_id', $resource['Language_language_id']);
        $languageXml = $xml->addChild('Language');
        foreach ($language as $key => $value) {
            $languageXml->addChild($key, htmlspecialchars($value ?? ''));
        }

        // Titles
        $titles = $this->getTitles($connection, $id);
        $titlesXml = $xml->addChild('Titles');
        foreach ($titles as $title) {
            $titleXml = $titlesXml->addChild('Title');
            $titleXml->addChild('text', htmlspecialchars($title['text'] ?? ''));
            $titleXml->addChild('type', htmlspecialchars($title['title_type_name'] ?? ''));
        }

        // Authors
        $authors = $this->getAuthors($connection, $id);
        $authorsXml = $xml->addChild('Authors');
        foreach ($authors as $author) {
            $authorXml = $authorsXml->addChild('Author');
            $authorXml->addChild('familyname', htmlspecialchars($author['familyname'] ?? ''));
            $authorXml->addChild('givenname', htmlspecialchars($author['givenname'] ?? ''));
            if (isset($author['orcid']) && $author['orcid'] !== '') {
                $authorXml->addChild('orcid', htmlspecialchars($author['orcid']));
            }
            if (isset($author['Affiliations'])) {
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
        $descriptions = $this->getDescriptions($connection, $id);
        $descriptionsXml = $xml->addChild('Descriptions');
        foreach ($descriptions as $description) {
            $descriptionXml = $descriptionsXml->addChild('Description');
            $descriptionXml->addChild('type', htmlspecialchars($description['type']));
            $descriptionXml->addChild('description', htmlspecialchars($description['description']));
        }

        // Contributors
        $contributors = $this->getContributors($connection, $id);
        $contributorsXml = $xml->addChild('Contributors');

        // Contributor Persons
        $personsXml = $contributorsXml->addChild('Persons');
        foreach ($contributors['persons'] as $person) {
            $personXml = $personsXml->addChild('Person');
            $personXml->addChild('familyname', htmlspecialchars($person['familyname'] ?? ''));
            $personXml->addChild('givenname', htmlspecialchars($person['givenname'] ?? ''));
            if (isset($person['orcid']) && $person['orcid'] !== '') {
                $personXml->addChild('orcid', htmlspecialchars($person['orcid']));
            }
            if (isset($person['Affiliations'])) {
                $affiliationsXml = $personXml->addChild('Affiliations');
                foreach ($person['Affiliations'] as $affiliation) {
                    $affiliationXml = $affiliationsXml->addChild('Affiliation');
                    foreach ($affiliation as $key => $value) {
                        $affiliationXml->addChild($key, htmlspecialchars($value ?? ''));
                    }
                }
            }
            if (isset($person['Roles'])) {
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
            if (isset($institution['Affiliations'])) {
                $affiliationsXml = $institutionXml->addChild('Affiliations');
                foreach ($institution['Affiliations'] as $affiliation) {
                    $affiliationXml = $affiliationsXml->addChild('Affiliation');
                    foreach ($affiliation as $key => $value) {
                        $affiliationXml->addChild($key, htmlspecialchars($value ?? ''));
                    }
                }
            }
            if (isset($institution['Roles'])) {
                $rolesXml = $institutionXml->addChild('Roles');
                foreach ($institution['Roles'] as $role) {
                    $roleXml = $rolesXml->addChild('Role');
                    $roleXml->addChild('name', htmlspecialchars($role['name'] ?? ''));
                }
            }
        }

        // Contact Persons
        $contactPersons = $this->getContactPersons($connection, $id);
        $contactPersonsXml = $xml->addChild('ContactPersons');
        foreach ($contactPersons as $contactPerson) {
            $contactPersonXml = $contactPersonsXml->addChild('ContactPerson');
            $contactPersonXml->addChild('familyname', htmlspecialchars($contactPerson['familyname'] ?? ''));
            $contactPersonXml->addChild('givenname', htmlspecialchars($contactPerson['givenname'] ?? ''));
            $contactPersonXml->addChild('position', htmlspecialchars($contactPerson['position'] ?? ''));
            $contactPersonXml->addChild('email', htmlspecialchars($contactPerson['email'] ?? ''));
            $contactPersonXml->addChild('website', htmlspecialchars($contactPerson['website'] ?? ''));
            if (isset($contactPerson['Affiliations'])) {
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
        $relatedWorks = $this->getRelatedWorks($connection, $id);
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
        $spatialTemporalCoverages = $this->getSpatialTemporalCoverage($connection, $id);
        $coveragesXml = $xml->addChild('SpatialTemporalCoverages');
        foreach ($spatialTemporalCoverages as $coverage) {
            $coverageXml = $coveragesXml->addChild('SpatialTemporalCoverage');
            foreach ($coverage as $key => $value) {
                if ($key === 'dateTimeStart' || $key === 'dateTimeEnd') {
                    $datetime = new DateTime($value);
                    $value = $datetime->format('Y-m-d\TH:i:s');
                }
                // Überprüfen, ob Max-Werte vorhanden sind und nicht leer
                if (in_array($key, ['latitudeMax', 'longitudeMax'])) {
                    if (!empty($value)) {
                        $coverageXml->addChild($key, htmlspecialchars($value));
                    }
                } else {
                    $coverageXml->addChild($key, htmlspecialchars($value ?? ''));
                }
            }
        }

        // Thesaurus Keywords
        $thesaurusKeywords = $this->getThesaurusKeywords($connection, $id);
        $keywordsXml = $xml->addChild('ThesaurusKeywords');
        foreach ($thesaurusKeywords as $keyword) {
            $keywordXml = $keywordsXml->addChild('Keyword');
            foreach ($keyword as $key => $value) {
                $keywordXml->addChild($key, htmlspecialchars($value ?? ''));
            }
        }

        // Free Keywords
        $freeKeywords = $this->getFreeKeywords($connection, $id);
        $freeKeywordsXml = $xml->addChild('FreeKeywords');
        foreach ($freeKeywords as $keyword) {
            $keywordXml = $freeKeywordsXml->addChild('Keyword');
            $keywordXml->addChild('free_keywords_id', htmlspecialchars($keyword['free_keywords_id']));
            $keywordXml->addChild('free_keyword', htmlspecialchars($keyword['free_keyword']));
            $keywordXml->addChild('isCurated', htmlspecialchars($keyword['isCurated']));
        }

        // Funding References
        $fundingReferences = $this->getFundingReferences($connection, $id);
        $fundingReferencesXml = $xml->addChild('FundingReferences');
        foreach ($fundingReferences as $reference) {
            $referenceXml = $fundingReferencesXml->addChild('FundingReference');
            foreach ($reference as $key => $value) {
                $referenceXml->addChild($key, htmlspecialchars($value ?? ''));
            }
        }

        // Originating Laboratory
        $originatingLaboratories = $this->getOriginatingLaboratories($connection, $id);
        $originatingLaboratoriesXml = $xml->addChild('OriginatingLaboratories');
        foreach ($originatingLaboratories as $laboratory) {
            $laboratoryXml = $originatingLaboratoriesXml->addChild('OriginatingLaboratory');
            $laboratoryXml->addChild('laboratoryname', htmlspecialchars($laboratory['laboratoryname']));
            $laboratoryXml->addChild('labId', htmlspecialchars($laboratory['labId'] ?? ''));
            if (isset($laboratory['Affiliations'])) {
                $affiliationsXml = $laboratoryXml->addChild('Affiliations');
                foreach ($laboratory['Affiliations'] as $affiliation) {
                    $affiliationXml = $affiliationsXml->addChild('Affiliation');
                    foreach ($affiliation as $key => $value) {
                        $affiliationXml->addChild($key, htmlspecialchars($value ?? ''));
                    }
                }
            }
        }

        // XML hübsch formatieren
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        // XML-Datei speichern
        $baseDir = realpath(dirname(dirname(dirname(__DIR__))));
        $outputDir = $baseDir . '/xml';

        // Debug-Ausgaben
        error_log("Base Directory: " . $baseDir);
        error_log("Output Directory: " . $outputDir);

        // Verzeichnis erstellen falls nicht vorhanden
        if (!file_exists($outputDir)) {
            if (!mkdir($outputDir, 0777, true)) {
                error_log("Fehler beim Erstellen des Verzeichnisses: " . error_get_last()['message']);
                throw new Exception("Konnte XML-Verzeichnis nicht erstellen");
            }
        }

        // Berechtigungen setzen
        chmod($outputDir, 0777);

        // Beim Speichern der XML-Datei
        $outputFile = $outputDir . "/resource_$id.xml";
        error_log("Versuche Datei zu speichern: " . $outputFile);

        if (!@$dom->save($outputFile)) {
            error_log("Fehler beim Speichern: " . error_get_last()['message']);
            throw new Exception("Konnte XML-Datei nicht speichern: " . error_get_last()['message']);
        }

        // DB-Verbindung schließen
        $stmt->close();

        return $xml->asXML();
    }

    function transformAndSaveOrDownloadXml($id, $format, $download = false)
    {
        $baseDir = realpath(dirname(dirname(dirname(__DIR__))));

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

        $inputXmlPath = $baseDir . "/xml/resource_$id.xml";
        $xsltPath = $baseDir . "/schemas/XSLT/" . $formatInfo[$format]['xsltFile'];
        $outputXmlPath = $baseDir . "/xml/" . $formatInfo[$format]['outputPrefix'] . "_resource_$id.xml";

        // Debug-Ausgaben
        error_log("Base Directory: " . $baseDir);
        error_log("Input XML Path: " . $inputXmlPath);
        error_log("XSLT Path: " . $xsltPath);
        error_log("Output XML Path: " . $outputXmlPath);

        // FreestyleXML temporär erstellen
        $this->getResourceAsXml($GLOBALS['connection'], $id);

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

    public function exportResourceDownload($vars)
    {
        return $this->handleExport($vars, true);
    }

    public function exportResource($vars)
    {
        return $this->handleExport($vars, false);
    }

    private function handleExport($vars, $download)
    {
        $id = intval($vars['id']);
        $scheme = strtolower($vars['scheme']);

        // Debug-Ausgabe
        error_log("Download parameter: " . ($download ? 'true' : 'false'));

        // Überprüfen Sie die gültigen Schema-Formate
        $validSchemes = ['datacite', 'iso', 'dif'];
        if (!in_array($scheme, $validSchemes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid metadata scheme. Supported schemes are: ' . implode(', ', $validSchemes)]);
            return;
        }

        try {
            $result = $this->transformAndSaveOrDownloadXml($id, $scheme, $download);

            if ($download) {
                // Stelle sicher, dass keine Ausgabe vor den Headern erfolgt ist
                if (ob_get_level())
                    ob_end_clean();

                $filename = "dataset_{$id}_{$scheme}.xml";

                // Binary Transfer
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($result));
                header('Content-Transfer-Encoding: binary');
                header('Connection: close');

                echo $result;
                flush();
                exit();
            } else {
                header('Content-Type: application/xml; charset=utf-8');
                echo $result;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }
    public function exportAllDownload($vars)
    {
        return $this->handleExportAll($vars, true);
    }

    public function exportAll($vars)
    {
        return $this->handleExportAll($vars, false);
    }

    private function handleExportAll($vars, $download)
    {
        $id = intval($vars['id']);

        try {
            // Hole alle drei XML-Formate
            $dataciteXml = $this->transformAndSaveOrDownloadXml($id, 'datacite', false);
            $isoXml = $this->transformAndSaveOrDownloadXml($id, 'iso', false);
            $difXml = $this->transformAndSaveOrDownloadXml($id, 'dif', false);

            // Entferne XML-Deklarationen aus den einzelnen XMLs
            $dataciteXml = preg_replace('/<\?xml[^>]+\?>/', '', $dataciteXml);
            $isoXml = preg_replace('/<\?xml[^>]+\?>/', '', $isoXml);
            $difXml = preg_replace('/<\?xml[^>]+\?>/', '', $difXml);

            // Erstelle das kombinierte XML
            $combinedXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<envelope>
        $dataciteXml

        $isoXml

        $difXml
</envelope>
XML;

            if ($download) {
                // Stelle sicher, dass keine Ausgabe vor den Headern erfolgt ist
                if (ob_get_level())
                    ob_end_clean();

                $filename = "dataset_{$id}_all.xml";

                // Binary Transfer
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($combinedXml));
                header('Content-Transfer-Encoding: binary');
                header('Connection: close');

                echo $combinedXml;
                flush();
                exit();
            } else {
                header('Content-Type: application/xml; charset=utf-8');
                echo $combinedXml;
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }
}