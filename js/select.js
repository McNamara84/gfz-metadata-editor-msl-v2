$(document).ready(function () {
  $.getJSON("json/timezones.json", function (data) {
    var timezoneSelect = $("#tscTimezone");
    $.each(data, function (index, timezone) {
      var label = timezone.label;
      var utcOffset = label.substring(3, 9); // Extrahiert "+01:00" oder "-04:00"
      var offsetValue = parseFloat(utcOffset.replace(":", ".")); // Konvertiert zu Kommazahl

      var option = $("<option></option>").attr("value", offsetValue).text(label);
      timezoneSelect.append(option);
    });
  }).fail(function () {
    console.error("Fehler beim Laden der Zeitzonen. API-Call getTimezones (siehe Dokumentation) ausgeführt?");
  });
  // Choosen initialisieren für Roles-Auswahlfelder
  $(".chosen-select").chosen({});

  // select-Feld mit ID inputRights befüllen mit option-Elementen, die durch API-Aufruf erstellt werden
  function setupLicenseDropdown(isSoftware) {
    $("#inputRights").empty();

    var resourceType = isSoftware ? "software" : "all";
    $.getJSON("./api.php?action=getLicenses&resourcetype=" + resourceType, function (data) {
      var defaultOptionSet = false;

      $.each(data, function (key, val) {
        var option = $("<option>", {
          value: val.rightsIdentifier,
          text: val.text + " (" + val.rightsIdentifier + ")",
        });

        if (val.rightsIdentifier === "CC-BY-4.0") {
          option.prop("selected", true);
          defaultOptionSet = true;
        }

        $("#inputRights").append(option);
      });

      // Default-Option CC-BY-4.0, auch wenn nicht in Datenbank vorhanden
      if (!defaultOptionSet) {
        var defaultOption = $("<option>", {
          value: "CC-BY-4.0",
          text: "Creative Commons Attribution 4.0 International (CC-BY-4.0)",
          selected: true,
        });
        $("#inputRights").prepend(defaultOption);
      }

      // Trigger change event to ensure any listeners are notified
      $("#inputRights").trigger("change");
    });
  }

  function setupRolesDropdown(roletype, id) {
    if (roletype == "person") {
      $.getJSON("./api.php?action=getRoles&type=person", function (data) {
        $.each(data, function (key, val) {
          $(id).append("<option>" + val.name + "</option>");
        });
        $(".chosen-select").trigger("chosen:updated");
      });
    } else if (roletype == "institution") {
      $.getJSON("./api.php?action=getRoles&type=institution", function (data) {
        $.each(data, function (key, val) {
          $(id).append("<option>" + val.name + "</option>");
        });
        $(".chosen-select").trigger("chosen:updated");
      });
    } else {
      $.getJSON("./api.php?action=getRoles&type=both", function (data) {
        $.each(data, function (key, val) {
          $(id).append("<option>" + val.name + "</option>");
        });
        $(".chosen-select").trigger("chosen:updated");
      });
    }
  }

  setupRolesDropdown("person", "#inputRoleAuthor"); // Aufruf der Funktion um bestimmte Roles für inputRoleAuthor und inputContributorsPerRole anzuzeigen
  setupRolesDropdown("person", "#inputContributorsPerRole");
  setupRolesDropdown("institution", "#inputContributorOrgaRole"); // Aufruf der Funktion um bestimmte Roles für inputContributorOrgaRole anzuzeigen
  setupRolesDropdown("both", "#inputContributorsPerRole");
  setupRolesDropdown("both", "#inputContributorOrgaRole");

  setupLicenseDropdown(false);
  // Event Handler, der überwacht, ob Resource-Type geändert wird
  $("#inputResourceType").change(function () {
    var selectedResourceType = $("#inputResourceType option:selected").text().trim();

    // Prüfung ob "Software" ausgewählt wurde
    if (selectedResourceType === "Software") {
      setupLicenseDropdown(true);
    } else {
      setupLicenseDropdown(false);
    }
  });

  let fundersData = []; // Globale Variable zur Speicherung der Funder-Daten

  $.getJSON("json/funders.json", function (data) {
    fundersData = data; // Speichern der geladenen Daten in der globalen Variable
    $(".inputFunder").each(function () {
      setUpAutocompleteFunder(this);
    });
  }).fail(function () {
    console.error("Fehler beim Laden der funders.json");
  });

  function setUpAutocompleteFunder(inputElement) {
    $(inputElement)
      .autocomplete({
        source: function (request, response) {
          var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
          response(
            $.grep(fundersData, function (item) {
              return matcher.test(item.name);
            })
          );
        },
        minLength: 2,
        select: function (event, ui) {
          $(this).val(ui.item.name);
          $(this).siblings(".inputFunderId").val(ui.item.crossRefId);
          $(this).siblings(".inputFunderIdTyp").val("crossref");
          return false;
        },
        position: { my: "left bottom", at: "left top", collision: "flip" }, // Neue Zeile
      })
      .autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>")
          .append("<div>" + item.name + "</div>")
          .appendTo(ul);
      };
  }

  // Relation-Dropdownfeld befüllen
  $.ajax({
    url: "api.php",
    data: { action: "getRelations" },
    dataType: "json",
    success: function (data) {
      var select = $("#inputRelation");
      select.empty();

      //Platzhalter-Option
      select.append(
        $("<option>", {
          value: "",
          text: "Choose...",
        })
      );
      $.each(data, function (i, relation) {
        select.append(
          $("<option>", {
            value: relation.relation_id,
            text: relation.name,
          })
        );
      });
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.error("Fehler beim Laden der Relationen:", textStatus, errorThrown);
    },
  });

  function updateValidationPattern(selectElement) {
    var selectedType = $(selectElement).find("option:selected").text();
    var inputIdentifier = $(selectElement).closest(".row").find('input[name^="rIdentifier"]');

    $.ajax({
      url: "api/v2/validation/patterns/" + encodeURIComponent(selectedType),
      method: "GET",
      dataType: "json",
      success: function (response) {
        if (response && response.pattern) {
          var pattern = response.pattern;

          // Entferne Anführungszeichen am Anfang und Ende, falls vorhanden
          pattern = pattern.replace(/^"|"$/g, "");

          // Entferne den Modifikator am Ende, falls vorhanden
          pattern = pattern.replace(/\/[a-z]*$/, "");

          // Setze das pattern-Attribut des Eingabefelds
          inputIdentifier.attr("pattern", pattern);
        } else {
          inputIdentifier.removeAttr("pattern");
        }
      },
      error: function (xhr, status, error) {
        inputIdentifier.removeAttr("pattern");
      },
    });
  }

  // Event-Listener für Änderungen im Identifier Type Select-Feld
  $(document).on("change", 'select[name^="rIdentifierType"]', function () {
    updateValidationPattern(this);
  });

  // Event-Listener für neu hinzugefügte Felder
  $(document).on("click", ".addRelatedWork", function () {
    setTimeout(function () {
      $('select[name^="rIdentifierType"]:last').trigger("change");
    }, 100);
  });

  // Initial für bereits vorhandene Felder ausführen
  $('select[name^="rIdentifierType"]').each(function () {
    updateValidationPattern(this);
  });
});

//Funktion zum Befüllen des Dropdownmenüs von Identifiertypen
function setupIdentifierTypesDropdown(id) {
  $.getJSON("./api.php?action=getIdentifierTypes", function (data) {
    $.each(data, function (key, val) {
      $(id).append("<option>" + val.name + "</option>");
    });
    $(".chosen-select").trigger("chosen:updated");
  });
}

// Funktion zum Aktualisieren des Identifier Types
function updateIdentifierType(inputElement) {
  var identifier = $(inputElement).val();
  var selectElement = $(inputElement).closest(".row").find('select[name="rIdentifierType[]"]');

  if (identifier) {
    $.ajax({
      url: "api/v2/validation/identifiertypes",
      method: "GET",
      dataType: "json",
      success: function (response) {
        if (response && response.identifierTypes) {
          // Finde den passenden Identifier-Typ basierend auf dem Pattern
          const matchingType = response.identifierTypes.find(type => {
            const pattern = new RegExp(type.pattern);
            return pattern.test(identifier);
          });

          if (matchingType) {
            selectElement.val(matchingType.name);
            selectElement.trigger("change");
          } else {
            selectElement.val(""); // Setze auf leeren Wert zurück wenn kein Pattern matched
          }
        } else {
          selectElement.val(""); // Setze auf leeren Wert zurück wenn keine Types verfügbar
          console.warn("Keine Identifier-Typen in der Antwort gefunden");
        }
      },
      error: function (xhr, status, error) {
        console.error("Fehler beim Abrufen der Identifier-Typen:", status, error);
        selectElement.val(""); // Setze auf leeren Wert zurück im Fehlerfall
      },
    });
  } else {
    selectElement.val(""); // Setze auf leeren Wert zurück, wenn kein Identifier eingegeben wurde
  }
}

// Event-Listener für Änderungen im Identifier-Eingabefeld
$(document).on("blur", 'input[name="rIdentifier[]"]', function () {
  updateIdentifierType(this);
});

// Debounce-Funktion für die Eingabe
function debounce(func, wait) {
  var timeout;
  return function () {
    var context = this,
      args = arguments;
    clearTimeout(timeout);
    timeout = setTimeout(function () {
      func.apply(context, args);
    }, wait);
  };
}

// Event-Listener für die Eingabe mit Debounce
$(document).on(
  "input",
  'input[name="rIdentifier[]"]',
  debounce(function () {
    updateIdentifierType(this);
  }, 300)
);

// Event-Listener für neu hinzugefügte Felder
$(document).on("click", ".addRelatedWork", function () {
  // Klonen Sie die vorhandene Zeile
  //  var newRow = $(this).closest(".row").clone(true);

  // Leeren Sie die Eingabefelder in der neuen Zeile
  // newRow.find("input, select").val("");

  // Fügen Sie die neue Zeile hinzu
  // $("#relatedworkGroup").append(newRow);

  // Aktualisieren Sie die IDs und Namen der Elemente in der neuen Zeile
  updateIdsAndNames();
});

// Funktion zum Aktualisieren der IDs und Namen
function updateIdsAndNames() {
  $("#relatedworkGroup .row").each(function (index) {
    $(this)
      .find('select[name^="relation"]')
      .attr("id", "inputRelation" + index);
    $(this)
      .find('input[name^="rIdentifier"]')
      .attr("id", "inputRIdentifier" + index);
    $(this)
      .find('select[name^="rIdentifierType"]')
      .attr("id", "inputRIdentifierType" + index);
  });
}

// Initialisierung der Dropdowns für Identifier Types
setupIdentifierTypesDropdown("#inputRIdentifierType");
