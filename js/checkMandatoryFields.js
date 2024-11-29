function checkMandatoryFields() {
    // First name and last name fields (Contributing Person(s))
    var firstname = document.getElementById('input-contributor-firstname').value.trim();
    var lastname = document.getElementById('input-contributor-lastname').value.trim();

    // Role and ORCID fields (Contributing Person(s))
    var roleField = document.getElementById('input-contributor-personrole');
    var orcidField = document.getElementById('input-contributor-orcid');

    // Organization name and role fields (Contributing Institution(s))
    var OrganisationName = document.getElementById('input-contributor-name').value.trim();
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