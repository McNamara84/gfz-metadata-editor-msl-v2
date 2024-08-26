$(document).ready(function () {
  "use strict";

  // Wende benutzerdefinierte Bootstrap-Validierungsstile auf Formulare mit der Klasse '.needs-validation' an
  $(".needs-validation").each(function () {
    $(this).on("submit", function (event) {
      // Überprüfe, ob der Submit-Button 'cheatValidation' ist
      if (event.originalEvent.submitter && event.originalEvent.submitter.id === "cheatValidation") {
        // Wenn 'cheatValidation', überspringe die Validierung
        return true;
      }

      if (!this.checkValidity()) {
        event.preventDefault(); // Verhindert die Formularübermittlung
        event.stopPropagation(); // Stoppt die Weiterleitung des Events
      }
      $(this).addClass("was-validated"); // Fügt die Klasse 'was-validated' hinzu für Validierungsfeedback
    });
  });
});
