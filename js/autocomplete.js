$(document).ready(function () {
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
  var labData;
  $.getJSON("json/msl-labs.json", function (data) {
    labData = data;
    var firstRow = $("#laboratoryGroup .row").first();
    initializeTagify(firstRow, data);
  });
  setupAutocomplete("#inputAuthorAffiliation", "#hiddenAuthorRorId");
  setupAutocomplete("#inputCPAffiliation", "#hiddenCPRorId");
});
