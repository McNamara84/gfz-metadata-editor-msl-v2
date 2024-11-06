/**
 * The "setupRolesDropdown" function configures a dropdown field for role selection.
 * It fetches a list of roles based on the specified role type from an API
 * and dynamically updates the available options in the dropdown for the defined input field.
 *
 * @param {string} roletype - Specifies the type of roles to display:
 *                            "person" for person roles,
 *                            "institution" for institution roles, or
 *                            "both" for both types of roles.
 * @param {string} inputSelector - The CSS selector of the input field to be configured as a dropdown.
 */

function setupRolesDropdown(roletype, inputSelector) {
  // Fetch roles from the API based on the specified role type (e.g., "person", "institution", or "both")
  $.getJSON("./api.php?action=getRoles&type=" + roletype, function (data) {
    // Extract role names from the API data
    var roleNames = data.map(function (role) {
      return role.name;
    });

    // Select the input field based on the provided CSS selector using jQuery
    var inputElement = $(inputSelector);

    // Check if the input field exists and if a Tagify instance (for managing tags) is already attached
    if (inputElement.length && inputElement[0].tagify) {
      // Update the Tagify instance's whitelist with the new role names
      inputElement[0].tagify.settings.whitelist = roleNames;
      // Display the dropdown menu
      inputElement[0].tagify.dropdown.show.call(inputElement[0].tagify);
    }
    // If the input field exists and no Tagify instance is attached yet
    else if (inputElement.length && !inputElement[0].isTagified) {
      // Create a new Tagify instance and configure it with the role names
      var tagify = new Tagify(inputElement[0], {
        whitelist: roleNames,        // Sets the selectable options (role names)
        enforceWhitelist: true,      // Only allow tags from the whitelist
        maxTags: 10,                 // Maximum number of allowed tags
        dropdown: {
          maxItems: 20,              // Maximum number of items shown in the dropdown
          classname: "tags-look",    // CSS class for dropdown styling
          enabled: 0,                // Dropdown opening behavior (default)
          closeOnSelect: false       // Keep dropdown open after selecting a tag
        }
      });
      inputElement[0].isTagified = true;
    }
  });
}

// Calling the function to configure specific input fields for role selection
$(document).ready(function () {
  // setupRolesDropdown("person", "#inputRoleAuthor"); // Example for a person role field
  setupRolesDropdown("person", "#inputContributorsPerRole"); // Person roles
  setupRolesDropdown("institution", "#inputContributorOrgaRole"); // Institution roles
  setupRolesDropdown("both", "#inputContributorsPerRole"); // Both types of roles
  setupRolesDropdown("both", "#inputContributorOrgaRole"); // Both types of roles
});
