/**
 * Populates a dropdown field for roles based on the specified role type.
 * Fetches a list of roles from an API and dynamically updates the available options
 * for the specified input field.
 * 
 * @param {string} roletype - Specifies the type of roles to display:
 *                            "person" for person roles,
 *                            "institution" for institution roles, or
 *                            "both" for both types of roles.
 * @param {string} inputSelector - The CSS selector of the input field to configure as a dropdown.
 */

function setupRolesDropdown(roletype, inputSelector) {
  // Fetch roles based on the specified role type from the API
  $.getJSON("./api.php?action=getRoles&type=" + roletype, function (data) {
    // Extract role names from the API data
    var roleNames = data.map(function (role) {
      return role.name;
    });

    // Select the input field based on the CSS selector
    var inputElement = document.querySelector(inputSelector);

    // Check if the input field exists and if a Tagify instance is already attached
    if (inputElement && inputElement.tagify) {
      // Update the Tagify instance's whitelist with the new role names
      inputElement.tagify.settings.whitelist = roleNames;
      // Display the dropdown menu
      inputElement.tagify.dropdown.show.call(inputElement.tagify);
    } else if (inputElement) { // If no Tagify instance exists
      // Create a new Tagify instance and configure it with the role names
      var tagify = new Tagify(inputElement, {
        whitelist: roleNames,        // Role names as selectable options
        enforceWhitelist: true,      // Only allow tags from the whitelist
        maxTags: 10,                 // Maximum number of tags allowed
        dropdown: {
          maxItems: 20,              // Maximum number of items shown in the dropdown
          classname: "tags-look",    // CSS class for dropdown styling
          enabled: 0,                // Dropdown open behavior
          closeOnSelect: false       // Keep dropdown open after selection
        }
      });
    }
  });
}

// Calls to configure specific role fields
// setupRolesDropdown("person", "#inputRoleAuthor");
setupRolesDropdown("person", "#inputContributorsPerRole");
setupRolesDropdown("institution", "#inputContributorOrgaRole");
setupRolesDropdown("both", "#inputContributorsPerRole");
setupRolesDropdown("both", "#inputContributorOrgaRole");
