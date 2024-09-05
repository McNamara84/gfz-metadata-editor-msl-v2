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
    var input = document.querySelector('input[name="laboratoryName[]"]');
    var tagify = new Tagify(input, {
      whitelist: data.map((item) => item.name),
      dropdown: {
        enabled: 1,
        maxItems: 5,
        position: "text",
        closeOnSelect: true,
        highlightFirst: true,
      },
      enforceWhitelist: false,
      maxTags: 1,
      keepInvalidTags: false,
      backspace: "edit",
      placeholder: "Select or enter a laboratory name",
      editTags: false,
    });

    // Event-Listener für das Hinzufügen eines Tags
    tagify.on("add", function (e) {
      // Entfernt alle anderen Tags, wenn ein neuer hinzugefügt wird
      if (tagify.value.length > 1) {
        tagify.removeAllTags();
        tagify.addTags(e.detail.data);
      }
    });

    // Event-Listener für Benutzereingaben
    tagify.on("input", function (e) {
      var value = e.detail.value;
      if (value && tagify.value.length > 0) {
        // Wenn bereits ein Tag existiert, ersetze ihn
        tagify.removeAllTags();
        tagify.addTags(value);
      }
    });
  });
});
