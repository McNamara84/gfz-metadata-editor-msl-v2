function checkMandatoryFields() {
    // "FG" Contributer(s)
    //Person
    var contributorOrcid = document.getElementById('input-contributor-orcid');
    var contributorlastname = document.getElementById('input-contributor-lastname');
    var contributorfirstname = document.getElementById('input-contributor-firstname');
    var contributorrole = document.getElementById('input-contributor-personrole');
    var contributorAffiliation = document.getElementById('input-contributor-personaffiliation');

    // Check if any Contributors (Person) fields are filled
    if (contributorOrcid.value.trim() !== '' || contributorlastname.value.trim() !== '' || contributorfirstname.value.trim() !== '' || contributorrole.value.trim() !== '' || contributorAffiliation.value.trim() !== '') {
        // Set 'required' for Contributor Lastname, Contributor Firstname and ContributorPerson Role fields
        contributorlastname.setAttribute('required', 'required');
        contributorfirstname.setAttribute('required', 'required');
        contributorrole.setAttribute('required', 'required');
    } else {
        // Remove 'required' from Contributor Lastname, Contributor Firstname and ContributorPerson Role fields
        contributorlastname.removeAttribute('required');
        contributorfirstname.removeAttribute('required');
        contributorrole.removeAttribute('required');
    }

    //Institution
    var contributorOrganisationName = document.getElementById('input-contributor-name');
    var contributorOrganisationrole = document.getElementById('input-contributor-organisationrole');
    var contributorOrganisationAffiliation = document.getElementById('input-contributor-organisationaffiliation');

    // Check if any Contributors (Institution) fields are filled
    if (contributorOrganisationName.value.trim() !== '' || contributorOrganisationrole.value.trim() !== '' || contributorOrganisationAffiliation.value.trim() !== '') {
        // Set 'required' for Contributor Organisation name and ContributorInstitution Role fields
        contributorOrganisationName.setAttribute('required', 'required');
        contributorOrganisationrole.setAttribute('required', 'required');

    } else {
        // Remove 'required' from Contributor Organisation name and ContributorInstitution Role fields
        contributorOrganisationName.removeAttribute('required');
        contributorOrganisationrole.removeAttribute('required');

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
        // Set 'required' for Contact Person Lastname, Contact Person Firstname and Contact Person Email Address fields
        contactpersonLastname.setAttribute('required', 'required');
        contactpersonFirstname.setAttribute('required', 'required');
        contactpersonEmail.setAttribute('required', 'required');
    } else {
        // Remove 'required' from Contributors Lastname, Contact Person Firstname and Contact Email Address fields
        contactpersonLastname.removeAttribute('required');
        contactpersonFirstname.removeAttribute('required');
        contactpersonEmail.removeAttribute('required');
    }

    // "FG" Funding Reference
    var funder = document.getElementById('input-funder');
    var grantNumber = document.getElementById('input-grantnumber');
    var grantName = document.getElementById('input-grantname');

    //Check if gramt number or grant name is filled
    if (grantNumber.value.trim() !== '' || grantName.value.trim() !== '') {
        // Set 'required' for funder field
        funder.setAttribute('required', 'required');
    } else {
        // Remove 'required' from funder field
        funder.removeAttribute('required');
    }
}