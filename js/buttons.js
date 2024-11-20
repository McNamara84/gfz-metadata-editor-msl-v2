$(document).ready(function () {
  // Feedback versenden
  $("#sendFeedback").click(function (event) {
    event.preventDefault();
    var feedbackForm = $("#feedbackForm");
    var feedbackData = feedbackForm.serialize();

    // Button ändern, wenn Senden in Arbeit
    $("#sendFeedback")
      .prop("disabled", true)
      .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');

    $.ajax({
      url: "send_feedback_mail.php",
      type: "POST",
      data: feedbackData,
      success: function (response) {
        // Formular ausblenden
        feedbackForm.hide();

        // Erfolgsmeldung und Danke-Nachricht anzeigen
        $("#thankYouMessage").show();
        $("#feedbackStatus").html('<div class="alert alert-success">Feedback sent successfully!</div>');

        // Modal schließen nach 3 Sekunden
        setTimeout(function () {
          $("#feedbackModal").modal("hide");
        }, 3000);
      },
      error: function (xhr, status, error) {
        // Fehlermeldung zeigen
        $("#feedbackStatus").html('<div class="alert alert-danger">Error when sending feedback: ' + error + "</div>");
        // Senden-Button aktivieren
        $("#sendFeedback").prop("disabled", false).html("Senden");
      },
      complete: function () {
        // Modal zurücksetzen, wenn es geschlossen wird
        $("#feedbackModal").on("hidden.bs.modal", function () {
          feedbackForm[0].reset();
          feedbackForm.show();
          $("#thankYouMessage").hide();
          $("#feedbackStatus").html("");
          $("#sendFeedback").prop("disabled", false).html("Senden");
        });
      }
    });
  });

  // Optional: Formular zurücksetzen, wenn das Modal geöffnet wird
  $('#feedbackModal').on('show.bs.modal', function () {
    $("#feedbackForm")[0].reset();
    $("#feedbackForm").show();
    $("#thankYouMessage").hide();
    $("#feedbackStatus").html("");
    $("#sendFeedback").prop("disabled", false).html("Senden");
  });
  // Tooltip initialisieren
  $('[data-bs-toggle="tooltip"]').tooltip();

  //////////////////////////// ADD AND REMOVE BUTTONS ///////////////////////////////////////////////////////////////
  //Remove  Button anlegen, der in Formgroups Authors, Contact Persons, Contributors genutzt wird
  var removeButton = '<button type="button" class="btn btn-danger removeButton" style="width: 36px">-</button>';
  var optionTitleTypeHTML = $("#titleType").html();
  var titlesNumber = 1;
  // Variable für den Haupttiteltyp
  var mainTitleType = "";
  $("#addTitle").click(function () {
    // Referenz auf den Button speichern
    var $addTitleBtn = $(this);

    if (titlesNumber < maxTitles) {
      // Vorbereitung der neuen Titelzeile durch Klonen und Zurücksetzen der Eingabefelder
      var newTitleRow = $addTitleBtn.closest(".row").clone();
      // Hilfe-Buttons entfernen
      deleteHelpButtonFromClonedRows(newTitleRow);
      $(newTitleRow).find("input").val("");

      // Adjust classes for layout of new row
      newTitleRow.find(".col-12.col-sm-12.col-md-11.col-lg-11")
        .removeClass("col-md-11 col-lg-11")
        .addClass("col-md-8 col-lg-8");

      // Sichtbarkeit des Dropdowns steuern
      if (titlesNumber === 0) {
        // Beim ersten Titel, Dropdown ausblenden
        $("#titleTypeContainer").show();
      } else {
        // Bei weiteren Titeln, Dropdown sichtbar machen
        $(newTitleRow).find("#titleTypeContainer").show();
      }

      if (titlesNumber === 1) {
        mainTitleType = $(newTitleRow).find("select").val(); // Haupttiteltyp erfassen
      }

      // Optionen für den Titeltyp setzen und Haupttiteltyp entfernen
      var $select = $(newTitleRow).find("select");
      $select.html(optionTitleTypeHTML);
      $select.find("option[value='" + mainTitleType + "']").remove(); // Haupttiteltyp entfernen
      $select.val(""); // Auswahl zurücksetzen

      // Hinzufügen des Löschbuttons
      var removeBtn = $("<button/>", {
        text: "-",
        type: "button",
        class: "btn btn-danger removeTitle",
      }).css("width", "36px");

      // Event-Handler für den Löschbutton
      removeBtn.click(function () {
        $(this).closest(".row").remove();
        titlesNumber--;

        // Reaktivieren des Hinzufüge-Buttons
        if (titlesNumber < maxTitles) {
          $addTitleBtn.prop("disabled", false);
        }
      });

      // Ersetzen des Hinzufügen-Buttons durch den Löschbutton im geklonten Element
      $(newTitleRow).find(".addTitle").replaceWith(removeBtn);

      // Hinzufügen der neuen Titelzeile zum DOM
      $addTitleBtn.closest(".row").parent().append(newTitleRow);
      titlesNumber++;

      // Wenn die maximale Anzahl an Titeln erreicht ist, Button addTitle deaktivieren
      if (titlesNumber == maxTitles) {
        $addTitleBtn.prop("disabled", true);
      }
    } else {
      console.log("Maximale Anzahl an Titeln erreicht: " + maxTitles);
    }
  });

  $("#addAuthor").click(function () {
    var authorGroup = $("#authorGroup");
    // Erste Zeile, die später als Vorlage dient
    var firstAuthorLine = authorGroup.children().first();

    // Klonen der Vorlage
    var newAuthorRow = firstAuthorLine.clone();

    // Einträge in den input-Fields löschen und valid/invalid feedback entfernen:
    newAuthorRow.find("input").val("").removeClass("is-invalid is-valid");
    newAuthorRow.find(".invalid-feedback, .valid-feedback").hide();

    // Eindeutige IDs für geklonte Input Elemente einführen:
    var uniqueSuffix = new Date().getTime();
    newAuthorRow.find("#inputAuthorAffiliation").attr("id", "inputAuthorAffiliation" + uniqueSuffix);
    newAuthorRow.find("#hiddenAuthorRorId").attr("id", "hiddenAuthorRorId" + uniqueSuffix);

    // altes TagifyElement in der neuen Zeile entfernen (wird weiter unten in autocompleteAffiliation wieder intitialisiert)
    newAuthorRow.find(".tagify").remove();

    // Plus Button mit Minus Button ersetzen
    newAuthorRow.find(".addAuthor").replaceWith(removeButton);

    // Hilfe-Buttons entfernen
    deleteHelpButtonFromClonedRows(newAuthorRow);

    // Neue AuthorLine zum DOM hinzufügen
    authorGroup.append(newAuthorRow);

    // Tagify auf neues AuthorAffiliations Feld anwenden
    autocompleteAffiliations("inputAuthorAffiliation" + uniqueSuffix, "hiddenAuthorRorId" + uniqueSuffix);

    // Event-Handler für RemoveButton
    newAuthorRow.on("click", ".removeButton", function () {
      $(this).closest(".row").remove();
    });
  });

  // Bei Klick auf Button mit ID addCP die Zeile mit dem Attribut contact-person-row klonen, TODO: Code-Doppelungen vermeiden und in Funktion auslagern
  $("#addCP").click(function () {
    var CPGroup = $("#contactpersonsGroup");

    // Erste Zeile, die später als Vorlage dient
    var firstCPLine = CPGroup.children().first();

    // Klonen der Vorlage
    var newCPRow = firstCPLine.clone();

    // Einträge in den input-Fields löschen und valid/invalid feedback entfernen:
    newCPRow.find("input").val("").removeClass("is-invalid is-valid");
    newCPRow.find(".invalid-feedback, .valid-feedback").hide();

    var uniqueSuffix = new Date().getTime();
    newCPRow.find("#inputCPAffiliation").attr("id", "inputCPAffiliation" + uniqueSuffix);
    newCPRow.find("#hiddenCPRorId").attr("id", "hiddenCPRorId" + uniqueSuffix);

    // altes TagifyElement in der neuen Zeile entfernen (wird weiter unten in autocompleteAffiliation wieder intitialisiert)
    newCPRow.find(".tagify").remove();

    // Plus Button mit Minus Button ersetzen
    newCPRow.find(".addCP").replaceWith(removeButton);

    // Hilfe-Buttons entfernen
    deleteHelpButtonFromClonedRows(newCPRow);

    CPGroup.append(newCPRow);

    //Autocomplete im Feld Affiliation in allen Zeilen ermöglichen:
    autocompleteAffiliations("inputCPAffiliation" + uniqueSuffix, "hiddenCPRorId" + uniqueSuffix);

    newCPRow.on("click", ".removeButton", function () {
      $(this).closest(".row").remove();
    });
  });

  $("#addContributorPerson").click(function () {
    var contributorGroup = $("#contributorsGroup");
    // Die erste Kontributorenzeile, die als Vorlage dient
    var firstContributorRow = contributorGroup.children().first();

    // Klonen der Vorlage
    var newContributorRow = firstContributorRow.clone();

    // Zurücksetzen der Werte und Validierungsfeedbacks im geklonten Element
    newContributorRow.find("input").val("").removeClass("is-invalid is-valid");
    newContributorRow.find(".tagify").remove();
    newContributorRow.find(".invalid-feedback, .valid-feedback").hide();
    // Hilfe-Buttons entfernen
    deleteHelpButtonFromClonedRows(newContributorRow);

    // Überschrift ausblenden für geklonte Zeile(n)
    newContributorRow.find("label.row-label").hide();

    var uniqueSuffix = new Date().getTime();
    newContributorRow.find("#inputContributorAffiliation").attr("id", "inputContributorAffiliation" + uniqueSuffix);
    newContributorRow.find("#hiddenContributorRorId").attr("id", "hiddenContributorRorId" + uniqueSuffix);

    // Roles-Feld eine neue ID geben
    newContributorRow.find("#inputContributorsPerRole").attr("id", "inputContributorsPerRole" + uniqueSuffix);

    // Plus Button mit Minus Button ersetzen
    newContributorRow.find(".addContributorPerson").replaceWith(removeButton);

    // Neue AuthorLine zum DOM hinzufügen
    contributorGroup.append(newContributorRow);

    // Tagify auf neues AuthorAffiliations Feld anwenden
    autocompleteAffiliations("inputContributorAffiliation" + uniqueSuffix, "hiddenContributorRorId" + uniqueSuffix);

    // Tagify für das neue Roles-Feld initialisieren
    setupRolesDropdown(["person", "both"], "#inputContributorsPerRole" + uniqueSuffix);

    // Event-Handler für RemoveButton
    newContributorRow.on("click", ".removeButton", function () {
      $(this).closest(".row").remove();
    });
  });

  $("#addContributor").click(function () {
    var contributorGroup = $("#contributorOrganisationGroup");
    // Die erste Kontributorenzeile, die als Vorlage dient
    var firstContributorRow = contributorGroup.children().first();

    // Klonen der Vorlage
    var newContributorRow = firstContributorRow.clone();

    // Zurücksetzen der Werte und Validierungsfeedbacks im geklonten Element
    newContributorRow.find("input").val("").removeClass("is-invalid is-valid");
    newContributorRow.find(".tagify").remove();;
    newContributorRow.find(".invalid-feedback, .valid-feedback").hide();

    // Hilfe-Buttons entfernen
    deleteHelpButtonFromClonedRows(newContributorRow);

    // Überschrift ausblenden für geklonte Zeile(n)
    newContributorRow.find("label.row-label").hide();

    var uniqueSuffix = new Date().getTime();
    newContributorRow.find("#inputOrganisationAffiliation").attr("id", "inputOrganisationAffiliation" + uniqueSuffix);
    newContributorRow.find("#hiddenOrganisationRorId").attr("id", "hiddenOrganisationRorId" + uniqueSuffix);

    // Roles-Feld eine neue ID geben
    newContributorRow.find("#inputContributorOrgaRole").attr("id", "inputContributorOrgaRole" + uniqueSuffix);


    // Plus Button mit Minus Button ersetzen
    newContributorRow.find(".addContributor").replaceWith(removeButton);


    // Neue AuthorLine zum DOM hinzufügen
    contributorGroup.append(newContributorRow);

    // Tagify auf neues AuthorAffiliations Feld anwenden
    autocompleteAffiliations("inputOrganisationAffiliation" + uniqueSuffix, "hiddenOrganisationRorId" + uniqueSuffix);

    // Tagify für das neue Roles-Feld initialisieren
    setupRolesDropdown(["institution", "both"], "#inputContributorOrgaRole" + uniqueSuffix);


    // Event-Handler für RemoveButton
    newContributorRow.on("click", ".removeButton", function () {
      $(this).closest(".row").remove();
    });
  });

  $("#tscAddButton").click(function () {
    var tscGroup = $("#tscGroup");
    // Die erste TSCzeile, die als Vorlage dient
    var firsttscLine = tscGroup.children().first();
    // Klonen der Vorlage
    var newtscLine = firsttscLine.clone();
    // Zurücksetzen der Werte und Validierungsfeedbacks im geklonten Element
    newtscLine.find("input").val("").removeClass("is-invalid is-valid");
    newtscLine.find("select").val("").removeClass("is-invalid is-valid");
    newtscLine.find(".invalid-feedback, .valid-feedback").hide();
    // Hilfe-Buttons entfernen
    deleteHelpButtonFromClonedRows(newtscLine);

    // Inkrementieren des Attributs tsc-row-id um 1 in newtscLine
    var newtscLineId = parseInt(newtscLine.attr("tsc-row-id")) + 1;
    newtscLine.attr("tsc-row-id", newtscLineId);


    // Plus Button mit Minus Button ersetzen
    newtscLine.find("#tscAddButton").replaceWith(removeButton);

    // Neue tscLine zum DOM hinzufügen
    tscGroup.append(newtscLine);

    // Event-Handler für RemoveButton
    newtscLine.on("click", ".removeButton", function () {
      $(this).closest(".row").remove();
    });
  });

  $("#addRelatedWork").click(function () {
    var relatedworkGroup = $("#relatedworkGroup");
    // Erste Zeile, die später als Vorlage dient
    var firstRelatedWorkLine = relatedworkGroup.children().first();

    // Klonen der Vorlage
    var newRelatedWorkRow = firstRelatedWorkLine.clone();

    // Einträge in den input-Fields löschen und valid/invalid feedback entfernen:
    newRelatedWorkRow.find("input").val("").removeClass("is-invalid");
    newRelatedWorkRow.find(".invalid-feedback").hide();

    //Hilfebuttons in geklonter Zeile löschen
    deleteHelpButtonFromClonedRows(newRelatedWorkRow);

    // Plus Button mit Minus Button ersetzen
    newRelatedWorkRow.find("#addRelatedWork").replaceWith(removeButton);

    // Neue RelatedWorkLine zum DOM hinzufügen
    relatedworkGroup.append(newRelatedWorkRow);

    // Event-Handler für RemoveButton
    newRelatedWorkRow.on("click", ".removeButton", function () {
      $(this).closest(".row").remove();
    });
  });

  $("#addFundingReference").click(function () {
    var fundingreferenceGroup = $("#fundingreferenceGroup");
    // Erste Zeile, die später als Vorlage dient
    var firstFundingReferenceLine = fundingreferenceGroup.children().first();

    // Klonen der Vorlage
    var newFundingReferenceRow = firstFundingReferenceLine.clone();

    // Einträge in den input-Fields löschen und valid/invalid feedback entfernen:
    newFundingReferenceRow.find("input").val("").removeClass("is-invalid");
    newFundingReferenceRow.find(".invalid-feedback").hide();

    // Plus Button mit Minus Button ersetzen
    newFundingReferenceRow.find(".addFundingReference").replaceWith(removeButton);

    // Neue AuthorLine zum DOM hinzufügen
    fundingreferenceGroup.append(newFundingReferenceRow);
    // Hilfe-Buttons in geclonter Zeile entfernen
    deleteHelpButtonFromClonedRows(newFundingReferenceRow);

    // Event-Handler für RemoveButton
    newFundingReferenceRow.on("click", ".removeButton", function () {
      $(this).closest(".row").remove();
    });
    // Autocomplete für das neue Eingabefeld initialisieren
    setUpAutocompleteFunder(newFundingReferenceRow.find(".inputFunder"));

    setUpAutocompleteFunder();
  });

  //TODO: Intitalisierung von Tagify auslagern, ähnlich wie bei affiliations?

  var labData;

  $.getJSON("json/msl-labs.json", function (data) {
    labData = data;
    var firstRow = $("#laboratoryGroup .row").first();
    initializeTagify(firstRow, data);
  });
  var rowCounter = 1;

  $("#addLaboratory").click(function () {
    var laboratoryGroup = $("#laboratoryGroup");
    var firstOriginatingLaboratoryLine = laboratoryGroup.children().first();

    var newOriginatingLaboratoryRow = firstOriginatingLaboratoryLine.clone();

    // Einträge in den input-Fields löschen und valid/invalid feedback entfernen:
    newOriginatingLaboratoryRow.find("input").val("").removeClass("is-invalid is-valid");
    newOriginatingLaboratoryRow.find(".invalid-feedback, .valid-feedback").hide();

    // Alte Tagify-Elemente entfernen
    newOriginatingLaboratoryRow.find(".tagify").remove();

    // IDs aktualisieren
    rowCounter++;
    newOriginatingLaboratoryRow.find("[id]").each(function () {
      var oldId = $(this).attr("id");
      var newId = oldId + "_" + rowCounter;
      $(this).attr("id", newId);
    });

    // Plus Button mit Minus Button ersetzen
    newOriginatingLaboratoryRow.find(".addLaboratory").replaceWith(removeButton);

    // Neue LaboratoryLine zum DOM hinzufügen
    laboratoryGroup.append(newOriginatingLaboratoryRow);
    // Hilfe-Buttons in geklonter Zeile entfernen
    deleteHelpButtonFromClonedRows(newOriginatingLaboratoryRow);

    // Tagify für die neue Zeile initialisieren
    initializeTagify(newOriginatingLaboratoryRow, labData);

    // Event-Handler für RemoveButton
    newOriginatingLaboratoryRow.on("click", ".removeButton", function () {
      $(this).closest(".row").remove();
    });
  });

  function initializeTagify(row, data) {
    var inputName = row.find('input[name="laboratoryName[]"]')[0];
    var inputAffiliation = row.find('input[name="laboratoryAffiliation[]"]')[0];
    var hiddenRorId = row.find('input[name="laboratoryRorIds[]"]')[0];
    var hiddenLabId = row.find('input[name="LabId[]"]')[0];

    function findLabByName(name) {
      return data.find((lab) => lab.name === name);
    }

    var tagifyName = new Tagify(inputName, {
      whitelist: data.map((item) => item.name),
      enforceWhitelist: true,
      maxTags: 1,
      dropdown: {
        maxItems: 20,
        closeOnSelect: true,
        highlightFirst: true,
      },
      delimiters: null,
      mode: "select",
    });

    var tagifyAffiliation = new Tagify(inputAffiliation, {
      whitelist: data.map((item) => item.affiliation),
      enforceWhitelist: true,
      maxTags: 1,
      dropdown: {
        maxItems: 20,
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
        hiddenLabId.value = lab.id;
        tagifyAffiliation.setReadOnly(true);
      } else {
        tagifyAffiliation.removeAllTags();
        hiddenRorId.value = "";
        hiddenLabId.value = "";
        tagifyAffiliation.setReadOnly(false);
      }
    });

    tagifyName.on("remove", function () {
      tagifyAffiliation.removeAllTags();
      hiddenRorId.value = "";
      hiddenLabId.value = "";
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

    return { tagifyName, tagifyAffiliation };
  }

  /////////////////////////////// HELP BUTTONS /////////////////////////////////////////////////////////////////

  function deleteHelpButtonFromClonedRows(row, roundCornersClass = "input-right-with-round-corners") {
    row.find("span.input-group-text:has(i.bi-question-circle-fill)").each(function () {
      $(this).remove();
    });

    row.find(".input-with-help").removeClass("input-right-no-round-corners").addClass(roundCornersClass);
  }

  let hoverCount = 0;
  let timer = null;

  function resetHoverCount() {
    hoverCount = 0;
  }

  $("#buttonHelp, #bd-theme").hover(function () {
    hoverCount++;

    if (hoverCount === 30) {
      window.open("doc/egg.html", "Egg", "width=650,height=450,scrollbars=no,resizable=no,location=no");
      resetHoverCount();
    }

    clearTimeout(timer);
    timer = setTimeout(resetHoverCount, 1000); // Setze den Timer auf 1 Sekunde (1000 ms)
  });

  //
  // Prüfen, ob Einstellung bereits gespeichert wurde
  if (localStorage.getItem("inputGroupTextVisible") === "false") {
    $(".input-group-text").hide();
  }

  // Event-Handler zum Einblenden der Elemente
  $("#buttonHelpOn").click(function () {
    $(".input-group-text").show();
    localStorage.setItem("inputGroupTextVisible", "true");
  });

  // Event-Handler zum Ausblenden der Elemente
  $("#buttonHelpOff").click(function () {
    $(".input-group-text").hide();
    localStorage.setItem("inputGroupTextVisible", "false");
  });

  //////////////////////////////// LANGUAGE BUTTONS ///////////////////////////////////////////////////////////

  // Funktion zum Setzen der Sprache in localStorage und neu laden der Seite
  function setLanguage(language) {
    localStorage.setItem("userLanguage", language);
    window.location.href = "?lang=" + language;
  }

  // Funktion zum automatischen Setzen der Sprache basierend auf Browsereinstellungen
  function setAutoLanguage() {
    var userLang = navigator.language || navigator.userLanguage;
    userLang = userLang.substring(0, 2);
    if (userLang !== "en" && userLang !== "de") {
      userLang = "en"; // Standard auf Englisch, wenn die Sprache nicht unterstützt wird
    }
    localStorage.setItem("userLanguage", userLang);
    window.location.href = "?lang=" + userLang;
  }

  // Überprüfen, ob eine Sprache in localStorage gesetzt ist, wenn nicht, auf Englisch setzen
  if (!localStorage.getItem("userLanguage")) {
    localStorage.setItem("userLanguage", "en");
  }

  // Event-Handler für Klicks auf die Sprach-Auswahl-Buttons
  $("[data-bs-language-value]").click(function (event) {
    event.preventDefault();
    var language = $(this).data("bs-language-value");
    if (language === "auto") {
      setAutoLanguage();
    } else {
      setLanguage(language);
    }
  });

  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));
});
