function checkMandatoryFields() {
    // "FG" Contributer(s)
    //Person
    var firstname = document.getElementById('input-contributor-firstname').value.trim();
    var lastname = document.getElementById('input-contributor-lastname').value.trim();
    var roleField = document.getElementById('input-contributor-personrole');
    //Institution
    var OrganisationName = document.getElementById('input-contributor-name').value.trim();
    var institutionRoleField = document.getElementById('input-contributor-organisationrole');

    // Check if first name or last name is filled
    if (firstname !== '' || lastname !== '') {
        // Set 'required' for role field
        roleField.setAttribute('required', 'required');
    } else {
        // Remove 'required' from role field
        roleField.removeAttribute('required');
    }

    // Check if organization name is filled
    if (OrganisationName !== '') {
        // Set 'required' for institution role field
        institutionRoleField.setAttribute('required', 'required');
    } else {
        // Remove 'required' from institution role field
        institutionRoleField.removeAttribute('required');
    }

    // "FG" Contact person(s)
    var contactpersonLastname = document.getElementById('input-contactperson-lastname');
    var contactpersonFirstname = document.getElementById('input-contactperson-firstname');
    var contactpersonPosition = document.getElementById('input-contactperson-position');
    var contactpersonEmail = document.getElementById('input-contactperson-email');
    var contactpersonWebsite = document.getElementById('input-contactperson-website');
    var contactpersonAffiliation = document.getElementById('input-contactperson-affiliation');

    // Check if any contact person fields are filled
    if (contactpersonLastname.value.trim() !== '' || contactpersonFirstname.value.trim() !== '' || contactpersonPosition.value.trim() !== '' || contactpersonEmail.value.trim() !== '' || contactpersonWebsite.value.trim() !== '' || contactpersonAffiliation.value.trim() !== '') {
        // Set 'required' for Contributors Lastname, Contact Person Firstname and Contact Email Address fields
        contactpersonLastname.setAttribute('required', 'required');
        contactpersonFirstname.setAttribute('required', 'required');
        contactpersonEmail.setAttribute('required', 'required');
    } else {
        // Remove 'required' from Contributors Lastname, Contact Person Firstname and Contact Email Address fields
        contactpersonLastname.removeAttribute('required');
        contactpersonFirstname.removeAttribute('required');
        contactpersonEmail.removeAttribute('required');
    }
}