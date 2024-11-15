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
  setupAutocomplete("#inputAuthorAffiliation", "#hiddenAuthorRorId");
  setupAutocomplete("#inputCPAffiliation", "#hiddenCPRorId");
});

$('#authorGroup').on('blur', 'input[name="orcids[]"]', function () {
  const orcidInput = $(this);
  const row = orcidInput.closest('[data-creator-row]');
  const orcid = orcidInput.val();

  if (orcid.match(/^\d{4}-\d{4}-\d{4}-(\d{4}|\d{3}X)$/)) {
    fetch(`https://pub.orcid.org/v3.0/${orcid}/record`, {
      headers: {
        'Accept': 'application/vnd.orcid+json'
      }
    })
      .then(response => response.json())
      .then(data => {
        // Namen befüllen
        const familyName = data.person?.name?.['family-name']?.value || '';
        const givenName = data.person?.name?.['given-names']?.value || '';
        row.find('input[name="familynames[]"]').val(familyName);
        row.find('input[name="givennames[]"]').val(givenName);

        // Affiliations und ROR IDs sammeln
        const affiliationSet = new Set(); // Set für unique Affiliations
        const rorIds = new Set();

        // Helper Funktion zum Verarbeiten von Affiliations
        const processAffiliation = (affiliation) => {
          if (affiliation?.organization) {
            const orgName = affiliation.organization.name;
            const disambiguated = affiliation.organization['disambiguated-organization'];
            if (disambiguated &&
              disambiguated['disambiguation-source'] === 'ROR' &&
              disambiguated['disambiguated-organization-identifier']) {
              const rorId = disambiguated['disambiguated-organization-identifier'];
              if (orgName) {
                affiliationSet.add(orgName); // Verwendet Set statt Array
                rorIds.add(rorId.startsWith('https://ror.org/') ? rorId : `https://ror.org/${rorId}`);
              }
            }
          }
        };

        // Employments verarbeiten
        const employments = data['activities-summary']?.employments?.['affiliation-group'] || [];
        employments.forEach(group => {
          const employment = group.summaries?.[0]?.['employment-summary'];
          processAffiliation(employment);
        });

        // Educations verarbeiten
        const educations = data['activities-summary']?.educations?.['affiliation-group'] || [];
        educations.forEach(group => {
          const education = group.summaries?.[0]?.['education-summary'];
          processAffiliation(education);
        });

        // Set in Array von Objekten umwandeln
        const affiliationObjects = Array.from(affiliationSet).map(name => ({ value: name }));

        console.log('Gesammelte Affiliations:', affiliationObjects);
        console.log('Gesammelte ROR IDs:', Array.from(rorIds));

        // Tagify Instanz finden und Tags setzen
        const affiliationInput = row.find('input[id^="inputAuthorAffiliation"]')[0];
        if (affiliationInput.tagify) {
          affiliationInput.tagify.removeAllTags();
          if (affiliationObjects.length > 0) {
            affiliationInput.tagify.addTags(affiliationObjects);
          }
        }

        // Hidden ROR ID Feld befüllen
        const rorIdsArray = Array.from(rorIds);
        if (rorIdsArray.length > 0) {
          row.find('input[id^="hiddenAuthorRorId"]').val(rorIdsArray.join(','));
        }
      })
      .catch(error => {
        console.error('Error fetching ORCID data:', error);
      });
  }
});
