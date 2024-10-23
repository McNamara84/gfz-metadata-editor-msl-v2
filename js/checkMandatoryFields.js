function checkMandatoryFields() {
    var firstname = document.getElementById('inputContributorFirstname').value.trim();
    var lastname = document.getElementById('inputContributorLastname').value.trim();

    // Role and ORCID fields
    var roleField = document.getElementById('inputContributorsPerRole');
    var orcidField = document.getElementById('inputContributorORCID');

    if (firstname !== '' || lastname !== '') {
        // Wenn Vorname oder Nachname eingegeben wurde, mache Role und ORCID erforderlich
        roleField.setAttribute('required', 'required');
        orcidField.setAttribute('required', 'required');
    } else {
        // Wenn beide leer sind, entferne die Erforderlichkeit von Role und ORCID
        roleField.removeAttribute('required');
        orcidField.removeAttribute('required');
    }
}