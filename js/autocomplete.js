$(document).ready(function () {
  setupAutocomplete("#inputAuthorAffiliation", "#hiddenAuthorRorId");
  setupAutocomplete("#inputCPAffiliation", "#hiddenCPRorId");

  function setupAutocomplete(inputSelector, hiddenInputSelector) {
    $(inputSelector).autocomplete({
      source: function (request, response) {
        $.ajax({
          url: "api.php?action=getKeywords&curationType=isCurated",
          dataType: "json",
          success: function (data) {
            var keywords = data.map(function (item) {
              return item.free_keyword;
            });
            response(keywords);
          },
        });
      },
      select: function (event, ui) {
        $(hiddenInputSelector).val(ui.item.value);
      },
    });
  }

  // Alle Optionen klonen und als Variable zwischenspeichern
  var allOptions = $("#inputRights option").clone();

  // Event Handler für Änderungen am Resource Type Select Element
  $("#inputResourceType").change(function () {
    var selectedResourceType = $("#inputResourceType option:selected").text().trim();

    // Prüfung ob "Software" ausgewählt wurde
    if (selectedResourceType === "Software") {
      $("#inputRights").empty();

      // Filtern der Optionen nach "MIT License" und "Apache License 2.0"
      allOptions.each(function () {
        var optionText = $(this).text().trim();

        if (optionText === "MIT License" || optionText === "Apache License 2.0") {
          $("#inputRights").append($(this).clone());
        }
      });
    } else {
      // Optionen zurücksetzen auf Klon der ursprünglichen Optionen
      $("#inputRights").empty().append(allOptions.clone());
    }
  });
  // Autocomplete mit Tagify für Labs
  $.getJSON("json/msl-labs.json", function (data) {
    var inputName = document.querySelector('input[name="laboratoryName[]"]');
    var inputAffiliation = document.querySelector('input[name="laboratoryAffiliation[]"]');
    var hiddenRorId = document.querySelector('input[name="laboratoryRorIds[]"]');

    function findLabByName(name) {
      return data.find((lab) => lab.name === name);
    }

    var tagifyName = new Tagify(inputName, {
      whitelist: data.map((item) => item.name),
      enforceWhitelist: false,
      maxTags: 1,
      dropdown: {
        enabled: 1, // Aktiviert das Dropdown
        maxItems: 5,
        position: "text",
        closeOnSelect: true,
        highlightFirst: true,
      },
      delimiters: null,
      mode: "select",
    });

    var tagifyAffiliation = new Tagify(inputAffiliation, {
      whitelist: data.map((item) => item.affiliation),
      enforceWhitelist: false,
      maxTags: 1,
      dropdown: {
        enabled: 1, // Aktiviert das Dropdown
        maxItems: 5,
        position: "text",
        closeOnSelect: true,
        highlightFirst: true,
      },
      delimiters: null,
      mode: "select",
    });

    tagifyName.on("add", function (e) {
      var labName = e.detail.data.value;
      var lab = findLabByName(labName);
      if (lab) {
        tagifyAffiliation.removeAllTags();
        tagifyAffiliation.addTags([lab.affiliation]);
        hiddenRorId.value = lab.ror_id || "";
        tagifyAffiliation.setReadOnly(true);
      } else {
        tagifyAffiliation.removeAllTags();
        hiddenRorId.value = "";
        tagifyAffiliation.setReadOnly(false);
      }
    });

    tagifyName.on("remove", function () {
      tagifyAffiliation.removeAllTags();
      hiddenRorId.value = "";
      tagifyAffiliation.setReadOnly(false);
    });

    tagifyName.on("input", function (e) {
      var value = e.detail.value;
      if (value) {
        var lab = findLabByName(value);
        if (!lab) {
          tagifyAffiliation.removeAllTags();
          hiddenRorId.value = "";
          tagifyAffiliation.setReadOnly(false);
        }
      }
    });

    tagifyAffiliation.on("input", function (e) {
      var value = e.detail.value;
      if (value && !tagifyAffiliation.state.readonly) {
        tagifyAffiliation.addTags([value]);
      }
    });
  });
});
