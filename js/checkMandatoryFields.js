function checkMandatoryFields() {
    // First name and last name fields (Contributing Person(s))
    var firstname = document.getElementById('inputContributorFirstname').value.trim();
    var lastname = document.getElementById('inputContributorLastname').value.trim();

    // Role and ORCID fields (Contributing Person(s))
    var roleField = document.getElementById('inputContributorsPerRole');
    var orcidField = document.getElementById('inputContributorORCID');

    // Organization name and role fields (Contributing Institution(s))
    var OrganisationName = document.getElementById('inputOrganisationName').value.trim();
    var institutionRoleField = document.getElementById('inputContributorOrgaRole');

    // Check if first name or last name(Contributing Person(s)) is filled
    if (firstname !== '' || lastname !== '') {
        // Set 'required' for role and ORCID fields
        roleField.setAttribute('required', 'required');
        orcidField.setAttribute('required', 'required');
    } else {
        // Remove 'required' from role and ORCID fields
        roleField.removeAttribute('required');
        orcidField.removeAttribute('required');
    }

    // Check if organization name(Contributing Institution(s)) is filled
    if (OrganisationName !== '') {
        // Set 'required' for institution role field
        institutionRoleField.setAttribute('required', 'required');
    } else {
        // Remove 'required' from institution role field
        institutionRoleField.removeAttribute('required');
    }
}