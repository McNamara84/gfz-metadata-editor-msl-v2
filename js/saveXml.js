function handleSaveAs() {
  // XML-Vorlage aus der Datei laden
  $.ajax({
    url: "metadata_template.xml",
    dataType: "xml",
    success: function (xmlTemplate) {
      // XML-Objekt aus der Vorlage erstellen
      var xmlDoc = $(xmlTemplate).clone();

      function formatXML(xml) {
        var formatted = "";
        var reg = /(>)(<)(\/*)/g;
        xml = xml.replace(reg, "$1\r\n$2$3");
        var pad = 0;
        $.each(xml.split("\r\n"), function (index, node) {
          var indent = 0;
          if (node.match(/.+<\/\w[^>]*>$/)) {
            indent = 0;
          } else if (node.match(/^<\/\w/)) {
            if (pad !== 0) {
              pad -= 1;
            }
          } else if (node.match(/^<\w[^>]*[^\/]>.*$/)) {
            indent = 1;
          } else {
            indent = 0;
          }

          var padding = "";
          for (var i = 0; i < pad; i++) {
            padding += "  ";
          }

          formatted += padding + node + "\r\n";
          pad += indent;
        });

        return formatted;
      }

      // Funktionen zum Einfügen von Werten in XML-Nodes
      function setXmlValue(xmlDoc, selector, value, attributes) {
        var node = xmlDoc.find(selector);
        if (node.length > 0) {
          node.text(value);
          if (attributes) {
            $.each(attributes, function (key, value) {
              node.attr(key, value);
            });
          }
        }
      }

      function appendXmlElement(xmlDoc, parentSelector, elementName, value, attributes) {
        var parentNode = xmlDoc.find(parentSelector);
        if (parentNode.length > 0) {
          var newElement = xmlDoc[0].createElement(elementName);
          newElement.textContent = value;
          if (attributes) {
            $.each(attributes, function (key, value) {
              newElement.setAttribute(key, value);
            });
          }
          parentNode[0].appendChild(newElement);
        }
      }

      // Werte aus dem Formular in das XML-Objekt einfügen
      /////////////////////////FORMGROUP RESOURCE INFORMATION///////////////////////////////////////////////////
      // DOI
      var doi = $("#inputDOI").val();
      setXmlValue(xmlDoc, 'identifier[identifierType="DOI"]', doi);

      // XML-Element gco:CharacterString in gmd:fileIdentifier hinzufügen mit Inhalt doi
      appendXmlElement(xmlDoc, "gmd\\:fileIdentifier", "gco:CharacterString", "doi:" + doi);
      setXmlValue(xmlDoc, "gmd\\:linkage gmd\\:URL", "http://dx.doi.org/doi:" + doi);
      setXmlValue(xmlDoc, "dif\\:Entry_ID", doi);

      // Year
      var year = $("#inputPublicationYear").val();
      setXmlValue(xmlDoc, "publicationYear", year);
      setXmlValue(xmlDoc, "dif\\:Dataset_Release_Date", year);

      // Resource Type
      setXmlValue(xmlDoc, "resourceType", "", { resourceTypeGeneral: $("#inputResourceType").find("option:selected").text() });

      // Version
      var version = $("#inputVersion").val();
      setXmlValue(xmlDoc, "version", version);

      // Language of dataset
      var language = $("#inputLanguageDataset").find("option:selected").text();
      // Language speichern, aber als Sprachcode und nicht ausgeschrieben
      if (language === "English") {
        setXmlValue(xmlDoc, "language", "en");
        setXmlValue(xmlDoc, "gmd\\:LanguageCode", "en");
        // Attribut codeListValue für XML-Element gmd:LanguageCode auf "en" für Englisch setzen
        xmlDoc.find("gmd\\:LanguageCode").attr("codeListValue", "en");
      } else if (language === "German") {
        setXmlValue(xmlDoc, "language", "de");
        setXmlValue(xmlDoc, "gmd\\:LanguageCode", "de");
        xmlDoc.find("gmd\\:LanguageCode").attr("codeListValue", "de");
      } else if (language === "French") {
        setXmlValue(xmlDoc, "language", "fr");
        setXmlValue(xmlDoc, "gmd\\:LanguageCode", "fr");
        xmlDoc.find("gmd\\:LanguageCode").attr("codeListValue", "fr");
      }

      //////////////////////// FORMGROUP RIGHTS ///////////////////////////////////////
      //TODO: Speicherung der Abkürzung für die Rechte und nicht ausgeschrieben
      var rights = $("#inputRights").find("option:selected").text();
      // Nur Wert innerhalb der Klammern in rights speichern und den Rest entfernen
      rights = rights.replace(/ \(([^)]+)\)/, "$1");
      setXmlValue(xmlDoc, "rights", rights);

      /////////////////////// TITLE ////////////////////////////////////////////
      // Titel-Elemente hinzufügen
      var mainTitle = "";
      $('input[name="title[]"]').each(function (index) {
        var titleType = $(this).closest(".row").find('select[name="titleType[]"]').val();
        var titleText = $(this).val();

        if (titleType === "1") {
          mainTitle = titleText;
        }

        appendXmlElement(xmlDoc, "titles", "title", titleText, { titleType: titleType });
      });

      // dif:Entry_Title und dif:Dataset_Title mit dem Main Title befüllen
      setXmlValue(xmlDoc, "dif\\:Entry_Title", mainTitle);
      setXmlValue(xmlDoc, "dif\\:Dataset_Title", mainTitle);

      ///////////////////// FORMGROUP AUTHOR //////////////////////////////////////////
      // Array zum Speichern der Creator-Namen
      var datasetCreators = [];

      // Creator-Elemente hinzufügen
      $(".row[data-creator-row]").each(function () {
        var familyName = $(this).find('input[name="familynames[]"]').val();
        var givenName = $(this).find('input[name="givennames[]"]').val();
        var orcid = $(this).find('input[name="orcids[]"]').val();
        var creatorName = familyName + ", " + givenName;

        // Neues XML-Element creator erstellen
        var creator = $("<creator></creator>");
        // Neues XML-Element creatorName erstellen, mit der Variable creatorName befüllen und als Kind-Element von creator hinzufügen
        creator.append($("<creatorName></creatorName>").text(creatorName));
        creator.append($("<givenName></givenName>").text(givenName));
        creator.append($("<familyName></familyName>").text(familyName));
        creator.append($('<nameIdentifier nameIdentifierScheme="ORCID"></nameIdentifier>').text(orcid));

        // Durch alle Werte aus affiliation[] iterieren
        xmlOrganisationName = $("<gmd:organisationName></gmd:organisationName>");
        $('input[name="affiliation[]"]').each(function () {
          var affiliationJSON = $(this).val();

          // Überprüfen, ob der Wert nicht leer ist
          if (affiliationJSON.trim() !== "") {
            try {
              var affiliations = JSON.parse(affiliationJSON);
              var rorId = $("#hiddenAuthorRorId").val();

              affiliations.forEach(function (affiliationObj) {
                var affiliation = affiliationObj.value;
                if (rorId) {
                  var affiliationXML =
                    '<affiliation affiliationIdentifier="' +
                    rorId +
                    '" affiliationIdentifierScheme="ROR" schemeURI="https://ror.org">' +
                    affiliation +
                    "</affiliation>";
                  creator.append($(affiliationXML));
                } else {
                  var affiliationXML = "<affiliation>" + affiliation + "</affiliation>";
                  creator.append($(affiliationXML));
                }
                // Neues XML-Element <gco:CharacterString> erstellen und mit dem Wert der Affiliation befüllen
                affiliationCharacterString = $("<gco:CharacterString></gco:CharacterString>").text(affiliation);
                // Neues XML-Element <gmd:organisationName> erstellen und mit affiliationCharacterString befüllen
                xmlOrganisationName.append(affiliationCharacterString);
              });
            } catch (e) {
              console.error("Invalid JSON input: ", affiliationJSON, e);
            }
          }
        });

        // Fertiges creator-Element in das XML-Objekt creators einfügen
        var creators = xmlDoc.find("creators");
        creators.append(creator);

        // Neues Element gmd:citedResponsibleParty erstellen
        var citedResponsibleParty = $("<gmd:citedResponsibleParty></gmd:citedResponsibleParty>").attr("xlink:href", "http://orcid.org/" + orcid);
        var responsibleParty = $("<gmd:CI_ResponsibleParty></gmd:CI_ResponsibleParty>");
        responsibleParty.append($("<gmd:individualName><gco:CharacterString>" + creatorName + "</gco:CharacterString></gmd:individualName>"));
        responsibleParty.append($(xmlOrganisationName));

        // TODO: Welche Role laut DataCite mappt zu welchem RoleCode?
        responsibleParty.append(
          $(
            '<gmd:role><gmd:CI_RoleCode codeList="http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml#CI_RoleCode" codeListValue="author">author</gmd:CI_RoleCode></gmd:role>'
          )
        );

        citedResponsibleParty.append(responsibleParty);

        // Element gmd:CI_Citation finden
        var citation = xmlDoc.find("gmd\\:CI_Citation");
        // Neues Element gmd:citedResponsibleParty einfügen
        citation.append(citedResponsibleParty);

        // Creator-Namen zum Array hinzufügen
        datasetCreators.push(creatorName);
      });

      // Dataset_Creator-Element befüllen
      var datasetCreatorString = datasetCreators.join("; ");
      setXmlValue(xmlDoc, "dif\\:Dataset_Creator", datasetCreatorString);

      ///////////////////// FORMGROUP CONTACT PERSON ///////////////
      // Elemente hinzufügen
      $(".row[contact-person-row]").each(function () {
        // Eingabefelder aus Form Group "Contact Persons" empfangen und als Variable speichern
        var cpLastname = $(this).find('input[name="cpLastname[]"]').val();
        var cpFirstname = $(this).find('input[name="cpFirstname[]"]').val();
        var cpPosition = $(this).find('input[name="cpPosition[]"]').val();
        var cpOnlineResource = $(this).find('input[name="cpOnlineResource[]"]').val();
        var cpMail = $(this).find('input[name="cpEmail[]"]').val();

        // Neues XML-Element <gmd:pointOfContact> erstellen
        var ccontactP = $("<gmd:pointOfContact></gmd:pointOfContact>");
        // Neues XML-Element <gmd:CI_ResponsibleParty> erstellen
        var responsibleParty = $("<gmd:CI_ResponsibleParty></gmd:CI_ResponsibleParty>");

        // Neues Element <gmd:individualName> erstellen
        var individualName = $("<gmd:individualName></gmd:individualName>");
        // Neues Element <gco:CharacterString> erstellen mit dem Inhalt Nachname, Vorname
        individualName.append($("<gco:CharacterString></gco:CharacterString>").text(cpLastname + ", " + cpFirstname));
        // Element <gmd:individualName> in <gmd:CI_ResponsibleParty> einfügen
        responsibleParty.append(individualName);

        // Neues Element <gmd:positionName> erstellen
        var positionName = $("<gmd:positionName></gmd:positionName>");
        // Neues Element <gmd:contactInfo> erstellen
        var contactInfo = $("<gmd:contactInfo></gmd:contactInfo>");
        // Neues Element <gmd:CI_Contact> erstellen
        var ciContact = $("<gmd:CI_Contact></gmd:CI_Contact>");
        // Neues Element <gmd:address> erstellen
        var address = $("<gmd:address></gmd:address>");

        //Neues Element <gmd:onlineResource> erstellen
        var onlineResource = $("<gmd:onlineResource></gmd:onlineResource");
        //Neues Element <gmd:CI_OnlineResource> erstellen
        var ciOnlineResource = $("<gmd:CI_OnlineResource></gmd:CI_OnlineResource");
        //Neues Element <gmd:linkage> erstellen
        var linkage = $("<gmd:linkage></gmd:linkage>");
        //Neues Element <gmd:URL> erstellen und befüllen mit Wert auf Formular
        var url = $("<gmd:URL></gmd:URL>").text(cpOnlineResource);
        //Elemente zur Webseite/URL /OnlineResource schachteln
        onlineResource.append(ciOnlineResource.append(linkage.append($(url))));

        // Neues Element <gmd:CI_Address> erstellen
        var ciAddress = $("<gmd:CI_Address></gmd:CI_Address>");
        // Neues Element <gmd:electronicMailAddress> erstellen
        var email = $("<gmd:electronicMailAddress></gmd:electronicMailAddress>");
        // Neues Element <gco:CharacterString> erstellen mit dem Inhalt der E-Mail-Adresse
        email.append($("<gco:CharacterString></gco:CharacterString>").text(cpMail));
        // Neues Element <gco:CharacterString> erstellen mit dem Inhalt der Position
        positionName.append($("<gco:CharacterString></gco:CharacterString>").text(cpPosition));

        // Elemente schachteln
        ciAddress.append(email);
        address.append(ciAddress);
        ciContact.append(address);
        //onlineResource ist Gschwisterknoten zu address. Struktur von onlineResource wurde oben angelegt
        ciContact.append(onlineResource);
        contactInfo.append(ciContact);
        responsibleParty.append(positionName);
        responsibleParty.append(contactInfo);
        ccontactP.append(responsibleParty);
        // Element <gmd:hierarchyLevelName> finden und das Element ccontactP danach einfügen
        xmlDoc.find("gmd\\:hierarchyLevelName").after(ccontactP);
      });

      ////////////////////// FORMGROUP CONTRIBUTORS ////////////////////////////////
      // Neues XML-Element <contributors> erstellen und in Variable contributorS speichern
      var contributors = $("<contributors></contributors>");
      $(".row[contributor-person-row]").each(function () {
        // Eingabefelder aus Form Group "Contributor Persons" empfangen und als Variable zwischenspeichern
        var cbPersonLastname = $(this).find('input[name="cbPersonLastname[]"]').val();
        var cbPersonFirstname = $(this).find('input[name="cbPersonFirstname[]"]').val();
        var cbPersonFullname = cbPersonLastname + ", " + cbPersonFirstname;
        var cbPersonRoles = $(this).find('select[name="cbPersonRoles[]"]').val(); // Mehrfachauswahlfeld-Werte als Array
        var cbPersonORCID = $(this).find('input[name="cbORCID[]"]').val();
        var cbPersonAffiliation = $(this).find('input[name="cbAffiliation[]"]').val();

        // Iteriere über jede ausgewählte Rolle und erstelle separate <contributor> Einträge
        $(this)
          .find('select[name="cbPersonRoles[]"] option:selected')
          .each(function (index, element) {
            var cbPersonRoleText = $(element).text(); // Text der ausgewählten Option
            // Neues XML-Element <contributor> erstellen und in Variable contributor speichern
            var contributor = $('<contributor contributorType="' + cbPersonRoleText + '"></contributor>');
            // Neues XML-Element <contributorName> erstellen, cbPersonFullname als Text einfügen und in <contributor> einfügen
            contributor.append($("<contributorName></contributorName>").text(cbPersonFullname));
            // Neues XML-Element <givenName> erstellen, cbPersonFirstname als Text einfügen und in <contributor> einfügen
            contributor.append($("<givenName></givenName>").text(cbPersonFirstname));
            // Neues XML-Element <familyName> erstellen, cbPersonLastname als Text einfügen und in <contributor> einfügen
            contributor.append($("<familyName></familyName>").text(cbPersonLastname));
            // Neues XML-Element <nameIdentifier> erstellen, cbPersonORCID als Text einfügen und in <contributor> einfügen
            // ACHTUNG: Nur Orcids erlaubt
            contributor.append($("<nameIdentifier nameIdentifierScheme='ORCID'></nameIdentifier>").text(cbPersonORCID));
            // Neues XML-Element <affiliation> erstellen, cbPersonAffiliation als Text einfügen und in <contributor> einfügen
            // TODO: Affiliation korrekt speichern laut Schema bzw. alter Version des MDEs
            contributor.append($("<affiliation></affiliation>").text(cbPersonAffiliation));
            // XML-Element <contributor> hinzufügen (hinten) zu <contributors> einfügen
            contributors.append(contributor);
          });
      });

      // Contributor-Organisation-Elemente hinzufügen
      $("#contributorGroup .row[contributors-row]").each(function () {
        var cbOrganisationName = $(this).find('input[name="cbOrganisationName[]"]').val(); // Organisation Name
        $(this)
          .find('select[name="cbOrganisationRoles[]"] option:selected')
          .each(function (index, element) {
            var cbOrganisationRoleText = $(element).text(); // Text der ausgewählten Option

            // Neues XML-Element <contributor> erstellen und in Variable contributor speichern
            var contributor = $('<contributor contributorType="' + cbOrganisationRoleText + '"></contributor>');
            // Neues XML-Element <contributorName> erstellen, cbOrganisationName als Text einfügen und in <contributor> einfügen
            contributor.append($("<contributorName></contributorName>").text(cbOrganisationName));
            // XML-Element <contributor> hinzufügen (hinten) zu <contributors> einfügen
            contributors.append(contributor);
          });
      });

      // XML-Element <contributors> nach Element <publicationYear> in xmlDoc einfügen
      xmlDoc.find("publicationYear").after(contributors);

      ///////////////////////FORMGROUP DESCRIPTION/////////////////////////////////////
      //DATACITE SCHEMA
      //Neues Element descriptions erstellen
      var descriptionS = $("<descriptions></descriptions>");

      //Variable Abstract anlegen und Input aus Formular darin speichern
      var abstract = $("#inputAbstract").val();
      //Element description mit Attribut "Abstract" anlegen und Inputwert darin speichern:
      var descriptionabstract = $('<description descriptionType="Abstract"></description>').text(abstract);
      //Element description (descriptionType: Abstract) an descriptionS anhängen
      descriptionS.append(descriptionabstract);

      // Eingabewert des Felds Methods in Variable speichern
      var methods = $("#inputMethods").val();
      // Methods speichern, in Element description speichern und an descriptionS anhängen
      var descriptionMethods = $('<description descriptionType="Methods"></description>').text(methods);
      descriptionS.append(descriptionMethods);

      //Variable technicalInfo anlegen und Input aus Formular darin speichern
      var technicalInfo = $("#inputTechnicalInfo").val();
      //Element description mit Attribut "technicalInfo" anlegen und Inputwert darin speichern:
      var descriptionTechnicalInfo = $('<description descriptionType="TechnicalInfo"></description>').text(technicalInfo);
      //Element description mit descriptionType "technicalInfo" an descriptionS anhängen
      descriptionS.append(descriptionTechnicalInfo);

      //Variable other anlegen und Input aus Formular speichern
      var other = $("#inputOther").val();
      //Element description mit Attribut "Other" anlegen und Inputwert darin speichern:
      var descriptionOther = $('<description descriptionType="Other"></description>').text(other);
      //Element description mit descriptionType "other" an descriptionS anhängen
      descriptionS.append(descriptionOther);

      // Element <rightsList> finden und das Element descriptionS danach einfügen
      xmlDoc.find("rightsList").after(descriptionS);

      //ISO SCHEMA
      var allDescriptions = abstract + "\n\n" + methods + "\n\n" + technicalInfo + "\n\n" + other;
      // Element <gco:CharacterString> anlegen und mit allen Descriptions befüllen
      var characterString = $("<gco:CharacterString></gco:CharacterString>").text(allDescriptions);
      //characterString an Element <gmd:abstract> anhängen
      xmlDoc.find("gmd\\:abstract").append(characterString);

      //NASA SCHEMA
      //nur das Abstract als Unterelement von Summary speichern:
      var summary = $("<dif:Summary></dif:Summary>");
      // Element anlegen und Inhalt des Feldes Abstract darin speichern (variable wurde oben für DATACITE Schema angelegt)
      var abstractNASA = $("<dif:Abstract></dif:Abstract>").text(abstract);
      //Abstract an Summary anhängen:
      summary.append(abstractNASA);
      // Finde dif:Data_CENTER element und Summary dahinter hängen
      xmlDoc.find("dif\\:Data_Center").after(summary);

      ///////////////////////FORMGROUP THESAURUS KEYWORDS/////////////////////////////////////
      //TODO !

      //////////////////// FORMGROUP TEMPORAL AND SPACIAL COVERAGE //////////////////////////
      // Temporal and spatial coverage übernehmen aus Eingabefeldern
      // XML-Element <geoLocations> erstellen
      var geoLocations = $("<geoLocations></geoLocations>");
      // Schleife über alle tsc-Zeilen
      $("[tsc-row]").each(function () {
        var tscRow = $(this);
        var tscRowId = tscRow.attr("tsc-row-id");

        // Temporal and spatial coverage übernehmen aus Eingabefeldern
        var tscDescription = tscRow.find("#tscDescription").val();
        var tscLatitudeMin = tscRow.find("#tscLatitudeMin").val();
        var tscLatitudeMax = tscRow.find("#tscLatitudeMax").val();
        var tscLongitudeMin = tscRow.find("#tscLongitudeMin").val();
        var tscLongitudeMax = tscRow.find("#tscLongitudeMax").val();
        var tscDateStart = tscRow.find("#tscDateStart").val();
        var tscTimeStart = tscRow.find("#tscTimeStart").val();
        var tscDateEnd = tscRow.find("#tscDateEnd").val();
        var tscTimeEnd = tscRow.find("#tscTimeEnd").val();
        // Zeitzone aus tscTimezone auslesen, aber nur ab dem 5. Zeichen bis zum 9. Zeichen
        var tscTimezone = tscRow.find("#tscTimezone").val().substring(3, 9);

        // Überprüfen, ob alle vier Koordinateneingabefelder ausgefüllt sind
        if (tscLatitudeMin != "" && tscLatitudeMax != "" && tscLongitudeMin != "" && tscLongitudeMax != "") {
          // Rechteck im DataCite-Schema speichern
          // XML-Element <westBoundLongitude> erstellen und mit tscLongitudeMin befüllen
          var westBoundLongitudeElement = $("<westBoundLongitude></westBoundLongitude>").text(tscLongitudeMin);
          // XML-Element <eastBoundLongitude> erstellen und mit tscLongitudeMax befüllen
          var eastBoundLongitudeElement = $("<eastBoundLongitude></eastBoundLongitude>").text(tscLongitudeMax);
          // XML-Element <<southBoundLatitude>> erstellen und mit tscLatitudeMin befüllen
          var southBoundLatitudeElement = $("<southBoundLatitude></southBoundLatitude>").text(tscLatitudeMin);
          // XML-Element <northBoundLatitude> erstellen und mit tscLatitudeMax befüllen
          var northBoundLatitudeElement = $("<northBoundLatitude></northBoundLatitude>").text(tscLatitudeMax);
          // XML-Element <geoLocationBox> erstellen und die vier Elemente einfügen
          var geoLocationBox = $("<geoLocationBox></geoLocationBox>")
            .append(westBoundLongitudeElement)
            .append(eastBoundLongitudeElement)
            .append(southBoundLatitudeElement)
            .append(northBoundLatitudeElement);
          // XML-Element <geoLocationPlace> erstellen und mit tscDescription befüllen
          var geoLocationPlace = $("<geoLocationPlace></geoLocationPlace>").text(tscDescription);
          // XML-Element <geoLocation> erstellen und geoLocationBox einfügen
          var geoLocation = $("<geoLocation></geoLocation>").append(geoLocationBox);
          // geoLocationPlace an geoLocation anhängen
          geoLocation.append(geoLocationPlace);
          // geoLocation an geoLocations anhängen
          geoLocations.append(geoLocation);

          // Rechteck im ISO-Schema speichern
          // XML-Element <gco:CharacterString> anlegen und mit tscDescription befüllen
          var tscDescriptionElement = $("<gco:CharacterString></gco:CharacterString>").text(tscDescription);
          // XML-Element <gmd:description> erstellen und tscDescriptionElement einfügen
          var tscDescriptionNode = $("<gmd:description></gmd:description>").append(tscDescriptionElement);
          // XML-Element <gmd:EX_Extent> erstellen und tscDescriptionNode einfügen
          var tscExtentNode = $("<gmd:EX_Extent></gmd:EX_Extent>").append(tscDescriptionNode);
          // XML-Element <gco:Decimal> erstellen und mit tscLongitudeMin befüllen
          var tscLongitudeMinElement = $("<gco:Decimal></gco:Decimal>").text(tscLongitudeMin);
          // XML-Element <gmd:westBoundLongitude> erstellen und tscLongitudeMinElement einfügen
          var tscLongitudeMinNode = $("<gmd:westBoundLongitude></gmd:westBoundLongitude>").append(tscLongitudeMinElement);
          // XML-Element <gmd:EX_GeographicBoundingBox> erstellen und tscLongitudeMinNode einfügen
          var tscExGeographicBoundingBox = $("<gmd:EX_GeographicBoundingBox></gmd:EX_GeographicBoundingBox>").append(tscLongitudeMinNode);
          // XML-Element <gco:Decimal> erstellen und mit tscLongitudeMax befüllen
          var tscLongitudeMaxElement = $("<gco:Decimal></gco:Decimal>").text(tscLongitudeMax);
          // XML-Element <gmd:eastBoundLongitude> erstellen und tscLongitudeMaxElement einfügen
          var tscLongitudeMaxNode = $("<gmd:eastBoundLongitude></gmd:eastBoundLongitude>").append(tscLongitudeMaxElement);
          // tscLongitudeMaxNode an tscExGeographicBoundingBox anhängen
          tscExGeographicBoundingBox.append(tscLongitudeMaxNode);
          // XML-Element <gco:Decimal> erstellen und mit tscLatitudeMin befüllen
          var tscLatitudeMinElement = $("<gco:Decimal></gco:Decimal>").text(tscLatitudeMin);
          // XML-Element <gmd:southBoundLatitude> erstellen und tscLatitudeMinElement einfügen
          var tscLatitudeMinNode = $("<gmd:southBoundLatitude></gmd:southBoundLatitude>").append(tscLatitudeMinElement);
          // tscLatitudeMinNode an tscExGeographicBoundingBox anhängen
          tscExGeographicBoundingBox.append(tscLatitudeMinNode);
          // XML-Element <gco:Decimal> erstellen und mit tscLatitudeMax befüllen
          var tscLatitudeMaxElement = $("<gco:Decimal></gco:Decimal>").text(tscLatitudeMax);
          // XML-Element <gmd:northBoundLatitude> erstellen und tscLatitudeMaxElement einfügen
          var tscLatitudeMaxNode = $("<gmd:northBoundLatitude></gmd:northBoundLatitude>").append(tscLatitudeMaxElement);
          // tscLatitudeMaxNode an tscExGeographicBoundingBox anhängen
          tscExGeographicBoundingBox.append(tscLatitudeMaxNode);
          // XML-Element <gmd:geographicElement> erstellen und tscExGeographicBoundingBox einfügen
          var tscGeographicElement = $("<gmd:geographicElement></gmd:geographicElement>").append(tscExGeographicBoundingBox);
          // XML-Element <gml:beginPosition> erstellen und Daten im Format z. B. 1234-06-07T11:22:33-02:00 einfügen
          var tscBeginPositionElement = $("<gml:beginPosition></gml:beginPosition>").text(tscDateStart + "T" + tscTimeStart + tscTimezone);
          // XML-Element <gml:endPosition> erstellen und Daten im Format z. B. 1234-06-07T11:22:33-02:00 einfügen
          var tscEndPositionElement = $("<gml:endPosition></gml:endPosition>").text(tscDateEnd + "T" + tscTimeEnd + tscTimezone);
          // XML-Element <gml:TimePeriod gml:id="ext-885"> erstellen und tscBeginPositionElement und tscEndPositionElement einfügen
          var tscTimePeriod = $('<gml:TimePeriod gml:id="ext-885"></gml:TimePeriod>').append(tscBeginPositionElement).append(tscEndPositionElement);
          // XML-Element <gmd:extent> erstellen und tscTimePeriod einfügen
          var tscTimeNode = $("<gmd:extent></gmd:extent>").append(tscTimePeriod);
          // XML-Element <gmd:EX_TemporalExtent> erstellen und tscTimeNode einfügen
          var tscTemporalExtent = $("<gmd:EX_TemporalExtent></gmd:EX_TemporalExtent>").append(tscTimeNode);
          // XML-Element <gmd:temporalElement> erstellen und tscTemporalExtent einfügen
          var tscTemporalElement = $("<gmd:temporalElement></gmd:temporalElement>").append(tscTemporalExtent);
          // XML-Element tscLongitudeMinElementNode an tscExtentNode anhängen
          tscExtentNode.append(tscGeographicElement).append(tscTemporalElement);
          // XML-Element <gmd:extent> erstellen und tscExtentNode einfügen
          var tscNode = $("<gmd:extent></gmd:extent>").append(tscExtentNode);
          // XML-Element </gmd:language> in xmlDoc finden und tscNode dahinter einfügen
          xmlDoc.find("gmd\\:language").after(tscNode);
          // Rechteck im DIF-Schema speichern
          // XML-Element <dif:southernmost_latitude> erstellen und mit tscLatitudeMin befüllen
          var tscLatitudeMinElement = $("<dif:southernmost_latitude></dif:southernmost_latitude>").text(tscLatitudeMin);
          // XML-Element <dif:Northernmost_Latitude> erstellen und mit tscLatitudeMax befüllen
          var tscLatitudeMaxElement = $("<dif:Northernmost_Latitude></dif:Northernmost_Latitude>").text(tscLatitudeMax);
          // XML-Element <dif:Westernmost_Longitude> erstellen und mit tscLongitudeMin befüllen
          var tscLongitudeMinElement = $("<dif:Westernmost_Longitude></dif:Westernmost_Longitude>").text(tscLongitudeMin);
          // XML-Element <dif:Easternmost_Longitude> erstellen und mit tscLongitudeMax befüllen
          var tscLongitudeMaxElement = $("<dif:Easternmost_Longitude></dif:Easternmost_Longitude>").text(tscLongitudeMax);
          // XML-Elemente <dif:Spatial_Coverage> erstellen und die vier Elemente einfügen
          var tscSpatialCoverage = $("<dif:Spatial_Coverage></dif:Spatial_Coverage>")
            .append(tscLatitudeMinElement)
            .append(tscLatitudeMaxElement)
            .append(tscLongitudeMinElement)
            .append(tscLongitudeMaxElement);
          // </dif:ISO_Topic_Category> in xmlDoc finden und tscSpatialCoverage dahinter einfügen
          xmlDoc.find("dif\\:ISO_Topic_Category").after(tscSpatialCoverage);
        } else if (tscLatitudeMin != "" && tscLongitudeMin != "") {
          // Einzelner Marker im DataCite-Schema speichern
          // XML-Element <pointLongitude> erstellen und mit tscLongitudeMin befüllen
          var pointLongitudeElement = $("<pointLongitude></pointLongitude>").text(tscLongitudeMin);
          // XML-Element <pointLatitude> erstellen und mit tscLatitudeMin befüllen
          var pointLatitudeElement = $("<pointLatitude></pointLatitude>").text(tscLatitudeMin);
          // XML-Element <geoLocationPoint> erstellen und die beiden Elemente einfügen
          var geoLocationPoint = $("<geoLocationPoint></geoLocationPoint>").append(pointLongitudeElement).append(pointLatitudeElement);
          // XML-Element <geoLocation> erstellen und geoLocationPoint einfügen
          var geoLocation = $("<geoLocation></geoLocation>").append(geoLocationPoint);
          // XML-Element <geoLocationPlace> erstellen und mit tscDescription befüllen
          var geoLocationPlace = $("<geoLocationPlace></geoLocationPlace>").text(tscDescription);
          // geoLocationPlace in geoLocation einfügen
          geoLocation.append(geoLocationPlace);
          // geoLocation in geoLocations einfügen
          geoLocations.append(geoLocation);

          // Einzelner Marker im ISO-Schema speichern
          // XML-Element <gco:CharacterString> anlegen und mit tscDescription befüllen
          var tscDescriptionElement = $("<gco:CharacterString></gco:CharacterString>").text(tscDescription);
          // XML-Element <gmd:description> erstellen und tscDescriptionElement einfügen
          var tscDescriptionNode = $("<gmd:description></gmd:description>").append(tscDescriptionElement);
          // XML-Element <gmd:EX_Extent> erstellen und tscDescriptionNode einfügen
          var tscExtentNode = $("<gmd:EX_Extent></gmd:EX_Extent>").append(tscDescriptionNode);
          // XML-Element <gco:Decimal> erstellen und mit tscLongitudeMin befüllen
          var tscLongitudeMinElement = $("<gco:Decimal></gco:Decimal>").text(tscLongitudeMin);
          // XML-Element <gmd:westBoundLongitude> erstellen und tscLongitudeMinElement einfügen
          var tscLongitudeMinNode = $("<gmd:westBoundLongitude></gmd:westBoundLongitude>").append(tscLongitudeMinElement);
          // XML-Element <gmd:EX_GeographicBoundingBox> erstellen und tscLongitudeMinNode einfügen
          var tscExGeographicBoundingBox = $("<gmd:EX_GeographicBoundingBox></gmd:EX_GeographicBoundingBox>").append(tscLongitudeMinNode);
          // XML-Element <gco:Decimal> erstellen und mit tscLatitudeMin befüllen
          var tscLatitudeMinElement = $("<gco:Decimal></gco:Decimal>").text(tscLatitudeMin);
          // XML-Element <gmd:southBoundLatitude> erstellen und tscLatitudeMinElement einfügen
          var tscLatitudeMinNode = $("<gmd:southBoundLatitude></gmd:southBoundLatitude>").append(tscLatitudeMinElement);
          // tscLatitudeMinNode an tscExGeographicBoundingBox anhängen
          tscExGeographicBoundingBox.append(tscLatitudeMinNode);
          // XML-Element <gmd:geographicElement> erstellen und tscExGeographicBoundingBox einfügen
          var tscGeographicElement = $("<gmd:geographicElement></gmd:geographicElement>").append(tscExGeographicBoundingBox);
          // XML-Element <gml:beginPosition> erstellen und Daten im Format z. B. 1234-06-07T11:22:33-02:00 einfügen
          var tscBeginPositionElement = $("<gml:beginPosition></gml:beginPosition>").text(tscDateStart + "T" + tscTimeStart + tscTimezone);
          // XML-Element <gml:endPosition> erstellen und Daten im Format z. B. 1234-06-07T11:22:33-02:00 einfügen
          var tscEndPositionElement = $("<gml:endPosition></gml:endPosition>").text(tscDateEnd + "T" + tscTimeEnd + tscTimezone);
          // XML-Element <gml:TimePeriod gml:id="ext-885"> erstellen und tscBeginPositionElement und tscEndPositionElement einfügen
          var tscTimePeriod = $('<gml:TimePeriod gml:id="ext-885"></gml:TimePeriod>').append(tscBeginPositionElement).append(tscEndPositionElement);
          // XML-Element <gmd:extent> erstellen und tscTimePeriod einfügen
          var tscTimeNode = $("<gmd:extent></gmd:extent>").append(tscTimePeriod);
          // XML-Element <gmd:EX_TemporalExtent> erstellen und tscTimeNode einfügen
          var tscTemporalExtent = $("<gmd:EX_TemporalExtent></gmd:EX_TemporalExtent>").append(tscTimeNode);
          // XML-Element <gmd:temporalElement> erstellen und tscTemporalExtent einfügen
          var tscTemporalElement = $("<gmd:temporalElement></gmd:temporalElement>").append(tscTemporalExtent);
          // XML-Element tscLongitudeMinElementNode an tscExtentNode anhängen
          tscExtentNode.append(tscGeographicElement).append(tscTemporalElement);
          // XML-Element <gmd:extent> erstellen und tscExtentNode einfügen
          var tscNode = $("<gmd:extent></gmd:extent>").append(tscExtentNode);
          // XML-Element </gmd:language> in xmlDoc finden und tscNode dahinter einfügen
          xmlDoc.find("gmd\\:language").after(tscNode);

          // Einzelner Marker im DIF-Schema speichern
          // XML-Element <dif:Southernmost_Latitude> erstellen und mit tscLatitudeMin befüllen
          var tscLatitudeMinElement = $("<dif:Southernmost_Latitude></dif:Southernmost_Latitude>").text(tscLatitudeMin);
          // XML-Element <dif:Westernmost_Longitude> erstellen und mit tscLongitudeMin befüllen
          var tscLongitudeMinElement = $("<dif:Westernmost_Longitude></dif:Westernmost_Longitude>").text(tscLongitudeMin);
          // XMKL-Elemente <dif:Spatial_Coverage> erstellen und die beiden Elemente einfügen
          var tscSpatialCoverage = $("<dif:Spatial_Coverage></dif:Spatial_Coverage>").append(tscLatitudeMinElement).append(tscLongitudeMinElement);
          // <dif:ISO_Topic_Category> in xmlDoc finden und tscSpatialCoverage dahinter einfügen
          xmlDoc.find("dif\\:ISO_Topic_Category").after(tscSpatialCoverage);
        }
        // XML-Element <date dateType="Collected"> erstellen und mit z. B. 1234-06-07T11:22:33/2345-07-08T22:33:44 befüllen
        var dateElement = $("<date dateType='Collected'></date>").text(tscDateStart + "T" + tscTimeStart + "/" + tscDateEnd + "T" + tscTimeEnd);
        // <publicationYear> in xmlDoc finden und dateElement dahinter einfügen
        xmlDoc.find("publicationYear").after(dateElement);
      });
      // <rightsList> finden und das Element <geoLocations> danach einfügen
      xmlDoc.find("rightsList").after(geoLocations);

      //////////////////////// FORMGROUP DATES /////////////////////////////////////////
      // Neues XML-Element <dates> erstellen und in Variable dates speichern
      var dateCreated = $("#inputDateCreated").val();
      var dateEmbargo = $("#inputDateEmbargo").val();

      // Dates in DataCite-Schema speichern
      //Neues XML-Element <date dateType="Available"> erstellen und dateEmbargo darin speichern
      var dateAvailableElement = $('<date dateType="Available"></date>').text(dateEmbargo);
      // Neues XML-Element <date dateType="Created"> erstellen und dateCreated darin speichern
      var dateCreatedElement = $('<date dateType="Created"></date>').text(dateCreated);
      // XML-Element <dates> erstellen und die drei date-Elemente darin einfügen
      var dates = $("<dates></dates>").append(dateAvailableElement, dateCreatedElement);
      // XML-Element <dates> nach Element <publicationYear> in xmlDoc einfügen
      xmlDoc.find("publicationYear").after(dates);

      // Dates in ISO-Schema speichern
      // XML-Element <gco:Date> erstellen und dateCreated darin speichern
      var dateCreatedISO = $("<gco:Date></gco:Date>").text(dateCreated);
      // XML-Element <gmd:date> erstellen und das Element dateCreatedISO darin einfügen
      var dateISO = $("<gmd:date></<gmd:date>").append(dateCreatedISO);
      // XML-Element <gmd:CI_Date> in XmlDoc finden und das Element dateISO als erstes Element einfügen
      xmlDoc.find("gmd\\:CI_Date").prepend(dateISO);

      /////////////////////////////////////////////////////////////////////////////////////////////////////

      // XML-Dokument in einen String konvertieren
      var xmlString = new XMLSerializer().serializeToString(xmlDoc[0]);
      // XML-String formatieren
      var formattedXmlString = formatXML(xmlString);
      // Blob-Objekt mit dem formatierten XML-String erstellen
      var blob = new Blob([formattedXmlString], { type: "application/xml" });
      // Link-Element erstellen und den Download triggern
      var link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      // TODO: Dateiname dynamisch generieren oder Nutzer:in eingeben lassen
      link.download = "metadata.xml";
      link.click();
    },
    error: function () {
      console.error("Fehler beim Laden der XML-Vorlage.");
    },
  });
}
