<?php
/**
 * Speichert die Contributor-Informationen in der Datenbank.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 *
 * @return void
 */
function saveContributors($connection, $postData, $resource_id)
{
    $valid_roles = getValidRoles($connection);
    saveContributorPersons($connection, $postData, $resource_id, $valid_roles);
    saveContributorInstitutions($connection, $postData, $resource_id, $valid_roles);
}

/**
 * Holt die gültigen Rollen aus der Datenbank.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @return array Ein Array mit Rollennamen als Schlüssel und Rollen-IDs als Werte.
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
 * Speichert die Contributor Personen in der Datenbank.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 * @param array $valid_roles Ein Array mit gültigen Rollen.
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
            $contributor_person_id = saveOrUpdateContributorPerson($connection, $cbPersonLastnames[$i], $cbPersonFirstnames[$i], $cbORCIDs[$i]);
            linkResourceToContributorPerson($connection, $resource_id, $contributor_person_id);
            if (!empty($cbAffiliations[$i])) {
                saveContributorPersonAffiliation($connection, $contributor_person_id, $cbAffiliations[$i], $cbRorIds[$i] ?? null);
            }
            saveContributorPersonRoles($connection, $contributor_person_id, $cbPersonRoles[$i], $valid_roles);
        }
    }
}

/**
 * Speichert oder aktualisiert eine Contributor Person in der Datenbank.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param string $lastname Der Nachname der Person.
 * @param string $firstname Der Vorname der Person.
 * @param string $orcid Die ORCID der Person.
 *
 * @return int Die ID der gespeicherten oder aktualisierten Contributor Person.
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
 * Verknüpft eine Resource mit einer Contributor Person.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $resource_id Die ID der Resource.
 * @param int $contributor_person_id Die ID der Contributor Person.
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
 * Speichert die Affiliation einer Contributor Person.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $contributor_person_id Die ID der Contributor Person.
 * @param string $affiliation_data Die Affiliationsdaten.
 * @param string|null $rorId_data Die ROR-ID-Daten.
 *
 * @return void
 */
function saveContributorPersonAffiliation($connection, $contributor_person_id, $affiliation_data, $rorId_data)
{
    $affiliation_name = parseAffiliationData($affiliation_data)[0];
    $rorId = $rorId_data ? parseAffiliationData($rorId_data)[0] : null;
    $rorId = $rorId ? str_replace("https://ror.org/", "", $rorId) : null;

    $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE 
                                  name = VALUES(name),
                                  rorId = COALESCE(VALUES(rorId), rorId)");
    $stmt->bind_param("ss", $affiliation_name, $rorId);
    $stmt->execute();
    $affiliation_id = $stmt->insert_id ?: $connection->insert_id;
    $stmt->close();

    $stmt = $connection->prepare("SELECT 1 FROM Contributor_Person_has_Affiliation 
                                  WHERE Contributor_Person_contributor_person_id = ? AND Affiliation_affiliation_id = ?");
    $stmt->bind_param("ii", $contributor_person_id, $affiliation_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $stmt = $connection->prepare("INSERT INTO Contributor_Person_has_Affiliation (Contributor_Person_contributor_person_id, Affiliation_affiliation_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $contributor_person_id, $affiliation_id);
        $stmt->execute();
    }
    $stmt->close();
}

/**
 * Speichert die Rollen einer Contributor Person.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $contributor_person_id Die ID der Contributor Person.
 * @param array|string $roles Die Rollen der Person.
 * @param array $valid_roles Ein Array mit gültigen Rollen.
 *
 * @return void
 */
function saveContributorPersonRoles($connection, $contributor_person_id, $roles, $valid_roles)
{
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    $stmt = $connection->prepare("DELETE FROM Contributor_Person_has_Role WHERE Contributor_Person_contributor_person_id = ?");
    $stmt->bind_param("i", $contributor_person_id);
    $stmt->execute();
    $stmt->close();

    foreach ($roles as $role_name) {
        if (isset($valid_roles[$role_name])) {
            $role_id = $valid_roles[$role_name];
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
 * Speichert die Contributor Institutionen in der Datenbank.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param array $postData Die POST-Daten aus dem Formular.
 * @param int $resource_id Die ID der zugehörigen Ressource.
 * @param array $valid_roles Ein Array mit gültigen Rollen.
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
            $contributor_institution_id = saveOrUpdateContributorInstitution($connection, $cbOrganisationNames[$i]);
            linkResourceToContributorInstitution($connection, $resource_id, $contributor_institution_id);
            if (!empty($cbOrganisationAffiliations[$i])) {
                saveContributorInstitutionAffiliation($connection, $contributor_institution_id, $cbOrganisationAffiliations[$i], $cbOrganisationRorIds[$i] ?? null);
            }
            saveContributorInstitutionRoles($connection, $contributor_institution_id, $cbOrganisationRoles[$i], $valid_roles);
        }
    }
}

/**
 * Speichert oder aktualisiert eine Contributor Institution in der Datenbank.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param string $name Der Name der Institution.
 *
 * @return int Die ID der gespeicherten oder aktualisierten Contributor Institution.
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
 * Verknüpft eine Resource mit einer Contributor Institution.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $resource_id Die ID der Resource.
 * @param int $contributor_institution_id Die ID der Contributor Institution.
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
 * Speichert die Affiliation einer Contributor Institution.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $contributor_institution_id Die ID der Contributor Institution.
 * @param string $affiliation_data Die Affiliationsdaten.
 * @param string|null $rorId_data Die ROR-ID-Daten.
 *
 * @return void
 */
function saveContributorInstitutionAffiliation($connection, $contributor_institution_id, $affiliation_data, $rorId_data)
{
    $affiliation_name = parseAffiliationData($affiliation_data)[0];
    $rorId = $rorId_data ? parseAffiliationData($rorId_data)[0] : null;
    $rorId = $rorId ? str_replace("https://ror.org/", "", $rorId) : null;

    $stmt = $connection->prepare("INSERT INTO Affiliation (name, rorId) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE 
                                  name = VALUES(name),
                                  rorId = COALESCE(VALUES(rorId), rorId)");
    $stmt->bind_param("ss", $affiliation_name, $rorId);
    $stmt->execute();
    $affiliation_id = $stmt->insert_id ?: $connection->insert_id;
    $stmt->close();

    $stmt = $connection->prepare("SELECT 1 FROM Contributor_Institution_has_Affiliation 
                                  WHERE Contributor_Institution_contributor_institution_id = ? AND Affiliation_affiliation_id = ?");
    $stmt->bind_param("ii", $contributor_institution_id, $affiliation_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $stmt = $connection->prepare("INSERT INTO Contributor_Institution_has_Affiliation (Contributor_Institution_contributor_institution_id, Affiliation_affiliation_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $contributor_institution_id, $affiliation_id);
        $stmt->execute();
    }
    $stmt->close();
}

/**
 * Speichert die Rollen einer Contributor Institution.
 *
 * @param mysqli $connection Die Datenbankverbindung.
 * @param int $contributor_institution_id Die ID der Contributor Institution.
 * @param array|string $roles Die Rollen der Institution.
 * @param array $valid_roles Ein Array mit gültigen Rollen.
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
