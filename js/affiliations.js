function autocompleteAffiliations(inputFieldId, hiddenFieldId) {
  var inputElement = $("#" + inputFieldId);
  var hiddenField = $("#" + hiddenFieldId);

  $.getJSON("json/affiliations.json", function (data) {
    var tagify = new Tagify(inputElement[0], {
      enforceWhitelist: false,
      whitelist: data.map((item) => item.name),
      dropdown: {
        maxItems: 20,
        classname: "affiliation",
        enabled: 3,
        closeOnSelect: true,
      },
      editTags: false,
      keepInvalidTags: true, // auch Tags erlaubt, die nicht auf Whitelist stehen
      autoComplete: {
        enabled: true,
      },
    });

    function closeDropdown() {
      tagify.dropdown.hide.call(tagify.dropdown);
    }

    tagify.on("add", function (e) {
      const selectedName = e.detail.data.value;
      const selectedItem = data.find((item) => item.name === selectedName);
      if (selectedItem) {
        hiddenField.val(selectedItem.id);
      }

      var isOnWhitelist = tagify.whitelist.some((item) => item === selectedName);
      if (!isOnWhitelist) {
        closeDropdown();
      }
    });

    tagify.on("input", function (e) {
      tagify.DOM.input.style.width = (e.detail.value.length + 1) * 8 + "px";
    });

    // Tags ausm neuen Input feld entfernen, wenn neues Inputfeld...
    if (inputFieldId !== ("inputAuthorAffiliation" || "inputCPAffiliation" || "inputContributorAffiliation" || "inputOrganisationAffiliation")) {
      tagify.removeAllTags();
    }
  });
}

$(document).ready(function () {
  autocompleteAffiliations("inputAuthorAffiliation", "hiddenAuthorRorId");
  autocompleteAffiliations("inputCPAffiliation", "hiddenCPRorId");
  autocompleteAffiliations("inputContributorAffiliation", "hiddenContributorRorId");
  autocompleteAffiliations("inputOrganisationAffiliation", "hiddenOrganisationRorId");
});
