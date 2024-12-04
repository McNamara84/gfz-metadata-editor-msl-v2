<?php
require_once 'save_affiliations.php';
/**
 * Saves the contributor information into the database.
 *
 * @param mysqli $connection  The database connection.
 * @param array  $postData    The POST data from the form.
 * @param int    $resource_id The ID of the associated resource.
 *
 * @return void
 */
function saveContributors($connection, $postData, $resource_id)
{
    $valid_roles = getValidRoles($connection);
    saveContributorPersons($connection, $postData, $resource_id, $valid_roles);

    // Only save institutions if corresponding data is available
    if (
        isset($postData['cbOrganisationName']) &&
        is_array($postData['cbOrganisationName']) &&
        !empty($postData['cbOrganisationName'][0])
    ) {
        saveContributorInstitutions($connection, $postData, $resource_id, $valid_roles);
    }
}

/**
 * Retrieves valid roles from the database.
 *
 * @param mysqli $connection The database connection.
 * @return array An array with role names as keys and role IDs as values.
 */
function getValidRoles($connection)
{
    $valid_roles = [];
    $stmt = $connection->prepare("SELECT role_id, name FROM Role");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $valid_roles[$row['name']] = $row['role_id'];
    }
    $stmt->close();
    return $valid_roles;
}

/**
 * Saves the contributor persons into the database.
 *
 * @param mysqli $connection  The database connection.
 * @param array  $postData    The POST data from the form.
 * @param int    $resource_id The ID of the associated resource.
 * @param array  $valid_roles An array of valid roles.
 *
 * @return void
 */
function saveContributorPersons($connection, $postData, $resource_id, $valid_roles)
{
    if (
        isset($postData['cbPersonLastname'], $postData['cbPersonFirstname'], $postData['cbORCID'], $postData['cbAffiliation'], $postData['cbPersonRoles']) &&
        is_array($postData['cbPersonLastname']) && is_array($postData['cbPersonFirstname']) && is_array($postData['cbORCID']) &&
        is_array($postData['cbAffiliation']) && is_array($postData['cbPersonRoles'])
    ) {
        $cbPersonLastnames = $postData['cbPersonLastname'];
        $cbPersonFirstnames = $postData['cbPersonFirstname'];
        $cbORCIDs = $postData['cbORCID'];
        $cbAffiliations = $postData['cbAffiliation'];
        $cbPersonRoles = $postData['cbPersonRoles'];
        $cbRorIds = $postData['cbpRorIds'] ?? [];

        $len = count($cbPersonLastnames);
        for ($i = 0; $i < $len; $i++) {
            // Check if the last name is provided
            if (empty(trim($cbPersonLastnames[$i]))) {
                continue; // Skip this record if the last name is missing
            }

            $contributor_person_id = saveOrUpdateContributorPerson($connection, $cbPersonLastnames[$i], $cbPersonFirstnames[$i], $cbORCIDs[$i]);
            linkResourceToContributorPerson($connection, $resource_id, $contributor_person_id);

            // Only process non-empty affiliations
            $affiliations = parseAffiliationData($cbAffiliations[$i]);
            if (!empty($affiliations)) {
                saveContributorPersonAffiliation($connection, $contributor_person_id, $cbAffiliations[$i], $cbRorIds[$i] ?? null);
            }

            saveContributorPersonRoles($connection, $contributor_person_id, $cbPersonRoles[$i], $valid_roles);
        }
    }
}

/**
 * Saves or updates a contributor person in the database.
 *
 * @param mysqli $connection The database connection.
 * @param string $lastname   The last name of the person.
 * @param string $firstname  The first name of the person.
 * @param string $orcid      The ORCID of the person.
 *
 * @return int The ID of the saved or updated contributor person.
 */
function saveOrUpdateContributorPerson($connection, $lastname, $firstname, $orcid)
{
    $stmt = $connection->prepare("SELECT contributor_person_id FROM Contributor_Person WHERE orcid = ?");
    $stmt->bind_param("s", $orcid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $contributor_person_id = $row['contributor_person_id'];
        $stmt = $connection->prepare("UPDATE Contributor_Person SET familyname = ?, givenname = ? WHERE contributor_person_id = ?");
        $stmt->bind_param("ssi", $lastname, $firstname, $contributor_person_id);
    } else {
        $stmt = $connection->prepare("INSERT INTO Contributor_Person (familyname, givenname, orcid) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $lastname, $firstname, $orcid);
    }
    $stmt->execute();
    $contributor_person_id = $stmt->insert_id ?: $contributor_person_id;
    $stmt->close();

    return $contributor_person_id;
}

/**
 * Links a resource with a contributor person.
 *
 * @param mysqli $connection            The database connection.
 * @param int    $resource_id           The ID of the resource.
 * @param int    $contributor_person_id The ID of the contributor person.
 *
 * @return void
 */
function linkResourceToContributorPerson($connection, $resource_id, $contributor_person_id)
{
    $stmt = $connection->prepare("INSERT IGNORE INTO Resource_has_Contributor_Person (Resource_resource_id, Contributor_Person_contributor_person_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $resource_id, $contributor_person_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Saves the affiliation of a contributor person.
 *
 * @param mysqli      $connection            The database connection.
 * @param int         $contributor_person_id The ID of the contributor person.
 * @param string      $affiliation_name      The affiliation name.
 * @param string|null $rorId                 The ROR ID data.
 *
 * @return void
 */
function saveContributorPersonAffiliation($connection, $contributor_person_id, $affiliation_data, $rorId_data)
{
    $affiliationNames = parseAffiliationData($affiliation_data);
    $rorIds = parseRorIds($rorId_data);

    $length = count($affiliationNames);

    for ($i = 0; $i < $length; $i++) {
        $affiliationName = $affiliationNames[$i];
        if (empty($affiliationName)) {
            continue; // Skip empty affiliations
        }

        $rorId = isset($rorIds[$i]) ? str_replace("https://ror.org/", "", $rorIds[$i]) : null;

        // Check if affiliation already exists
        $stmt = $connection->prepare("SELECT affiliation_id FROM Affiliation WHERE name = ?");
        $stmt->bind_param("s", $affiliationName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing affiliation
            $row = $result->fetch_assoc();
            $affiliation_id = $row['affiliation_id'];

            if ($rorId !== null) {
                $updateStmt = $connection->prepare("UPDATE Affiliation SET rorId = ? WHERE affiliation_id = ?");
                $updateStmt->bind_param("si", $rorId, $affiliation_id);
                $updateStmt->execute();
                $updateStmt->close();
            }
        } else {
            // Create new affiliation
            $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?)");
            $stmt->bind_param("ss", $affiliationName, $rorId);
            $stmt->execute();
            $affiliation_id = $stmt->insert_id;
        }
        $stmt->close();

        // Link contributor person with affiliation
        $stmt = $connection->prepare("INSERT IGNORE INTO Contributor_Person_has_Affiliation 
            (Contributor_Person_contributor_person_id, Affiliation_affiliation_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $contributor_person_id, $affiliation_id);
        $stmt->execute();
        $stmt->close();
    }
}


/**
 * Saves roles of a Contributor Person.
 *
 * @param mysqli $connection            The database connection.
 * @param int $contributor_person_id    The ID of the contributor person.
 * @param array|string $roles           The roles of the person.
 * @param array $valid_roles            Array with valid roles.
 *
 * @return void
 */
function saveContributorPersonRoles($connection, $contributor_person_id, $roles, $valid_roles)
{
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    // Delete extisting roles
    $stmt = $connection->prepare("DELETE FROM Contributor_Person_has_Role WHERE Contributor_Person_contributor_person_id = ?");
    $stmt->bind_param("i", $contributor_person_id);
    $stmt->execute();
    $stmt->close();

    foreach ($roles as $role_name) {
        error_log("Processing role: $role_name");
        if (isset($valid_roles[$role_name])) {
            $role_id = $valid_roles[$role_name];
            error_log("Valid role found. Role ID: $role_id");
            $stmt = $connection->prepare("INSERT INTO Contributor_Person_has_Role (Contributor_Person_contributor_person_id, Role_role_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $contributor_person_id, $role_id);
            $stmt->execute();
            $stmt->close();
        } else {
            error_log("Ungültiger Rollenname für Contributor $contributor_person_id: $role_name");
        }
    }
}

/**
 * Saves the contributor institutions into the database.
 *
 * @param mysqli $connection    The database connection.
 * @param array $postData       The POST data from the form.
 * @param int $resource_id      The ID of the resource.
 * @param array $valid_roles    Array of valid roles.
 *
 * @return void
 */
function saveContributorInstitutions($connection, $postData, $resource_id, $valid_roles)
{
    if (
        isset($postData['cbOrganisationName'], $postData['cbOrganisationRoles'], $postData['OrganisationAffiliation']) &&
        is_array($postData['cbOrganisationName']) &&
        is_array($postData['cbOrganisationRoles']) &&
        is_array($postData['OrganisationAffiliation'])
    ) {
        $cbOrganisationNames = $postData['cbOrganisationName'];
        $cbOrganisationRoles = $postData['cbOrganisationRoles'];
        $cbOrganisationAffiliations = $postData['OrganisationAffiliation'];
        $cbOrganisationRorIds = $postData['hiddenOrganisationRorId'] ?? [];

        $len = count($cbOrganisationNames);
        for ($i = 0; $i < $len; $i++) {
            if (!empty(trim($cbOrganisationNames[$i])) && !empty($cbOrganisationRoles[$i])) {
                $contributor_institution_id = saveOrUpdateContributorInstitution($connection, $cbOrganisationNames[$i]);
                linkResourceToContributorInstitution($connection, $resource_id, $contributor_institution_id);
                if (!empty($cbOrganisationAffiliations[$i])) {
                    saveContributorInstitutionAffiliation($connection, $contributor_institution_id, $cbOrganisationAffiliations[$i], $cbOrganisationRorIds[$i] ?? null);
                }
                saveContributorInstitutionRoles($connection, $contributor_institution_id, $cbOrganisationRoles[$i], $valid_roles);
            }
        }
    }
}

/**
 * Saves or updates a Contributor Institution in the database.
 *
 * @param mysqli $connection    The Database Connection.
 * @param string $name          The Name of the Contributor Institution.
 *
 * @return int                  The ID of the saved or updated Contributor Institution.
 */
function saveOrUpdateContributorInstitution($connection, $name)
{
    $stmt = $connection->prepare("SELECT contributor_institution_id FROM Contributor_Institution WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $contributor_institution_id = $row['contributor_institution_id'];
    } else {
        $stmt = $connection->prepare("INSERT INTO Contributor_Institution (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $contributor_institution_id = $stmt->insert_id;
    }
    $stmt->close();

    return $contributor_institution_id;
}

/**
 * Links a Resource with a Contributor Institution.
 *
 * @param mysqli $connection                The Database Connection.
 * @param int $resource_id                  The ID of the Resource.
 * @param int $contributor_institution_id   The ID of the Contributor Institution.
 *
 * @return void
 */
function linkResourceToContributorInstitution($connection, $resource_id, $contributor_institution_id)
{
    $stmt = $connection->prepare("INSERT IGNORE INTO Resource_has_Contributor_Institution (Resource_resource_id, Contributor_Institution_contributor_institution_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $resource_id, $contributor_institution_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Saves the Affiliation of a Contributor Institution.
 *
 * @param mysqli      $connection                The database connection.
 * @param int         $contributor_institution_id The ID of the contributor institution.
 * @param string      $affiliation_data          The affiliation data.
 * @param string|null $rorId_data                The ROR ID data.
 *
 * @return void
 */
function saveContributorInstitutionAffiliation($connection, $contributor_institution_id, $affiliation_data, $rorId_data)
{
    $affiliationNames = parseAffiliationData($affiliation_data);
    $rorIds = parseRorIds($rorId_data);

    $length = count($affiliationNames);

    for ($i = 0; $i < $length; $i++) {
        $affiliationName = $affiliationNames[$i];
        if (empty($affiliationName)) {
            continue; // Skip empty affiliations
        }
        
        $rorId = isset($rorIds[$i]) ? str_replace("https://ror.org/", "", $rorIds[$i]) : null;

        // Check if affiliation already exists
        $stmt = $connection->prepare("SELECT affiliation_id FROM Affiliation WHERE name = ?");
        $stmt->bind_param("s", $affiliationName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing affiliation
            $row = $result->fetch_assoc();
            $affiliation_id = $row['affiliation_id'];

            if ($rorId !== null) {
                $updateStmt = $connection->prepare("UPDATE Affiliation SET rorId = ? WHERE affiliation_id = ?");
                $updateStmt->bind_param("si", $rorId, $affiliation_id);
                $updateStmt->execute();
                $updateStmt->close();
            }
        } else {
            // Create new affiliation
            $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?)");
            $stmt->bind_param("ss", $affiliationName, $rorId);
            $stmt->execute();
            $affiliation_id = $stmt->insert_id;
        }
        $stmt->close();

        // Link contributor institution with affiliation
        $stmt = $connection->prepare("INSERT IGNORE INTO Contributor_Institution_has_Affiliation 
            (Contributor_Institution_contributor_institution_id, Affiliation_affiliation_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $contributor_institution_id, $affiliation_id);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Saves roles of a Contributor Institution.
 *
 * @param mysqli $connection                The database connection.
 * @param int $contributor_institution_id   The ID of the contributor institution.
 * @param array|string $roles               The roles of the institution.
 * @param array $valid_roles                Array with valid roles.
 *
 * @return void
 */
function saveContributorInstitutionRoles($connection, $contributor_institution_id, $roles, $valid_roles)
{
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    $stmt = $connection->prepare("DELETE FROM Contributor_Institution_has_Role WHERE Contributor_Institution_contributor_institution_id = ?");
    $stmt->bind_param("i", $contributor_institution_id);
    $stmt->execute();
    $stmt->close();

    foreach ($roles as $role_name) {
        if (isset($valid_roles[$role_name])) {
            $role_id = $valid_roles[$role_name];
            $stmt = $connection->prepare("INSERT INTO Contributor_Institution_has_Role (Contributor_Institution_contributor_institution_id, Role_role_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $contributor_institution_id, $role_id);
            $stmt->execute();
            $stmt->close();
        } else {
            error_log("Ungültiger Rollenname für Contributor Institution $contributor_institution_id: $role_name");
        }
    }
}
