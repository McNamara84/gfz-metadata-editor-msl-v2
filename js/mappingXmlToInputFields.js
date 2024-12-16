/**
 * Mapping configuration for XML elements to form input fields
 * @constant {Object}
 */
const XML_MAPPING = {
  // Resource Information
  'identifier': {
    selector: '#input-resourceinformation-doi',
    attribute: 'textContent'
  },
  'publicationYear': {
    selector: '#input-resourceinformation-publicationyear',
    attribute: 'textContent'
  },
  'version': {
    selector: '#input-resourceinformation-version',
    attribute: 'textContent'
  },
  'resourceType': {
    selector: '#input-resourceinformation-resourcetype',
    attribute: 'resourceTypeGeneral',
    transform: (value) => {
      // Map ResourceTypeGeneral to select option values TODO: Write API Endpoint
      const typeMap = {
        'Audiovisual': '1',
        'Book': '2',
        'BookChapter': '3',
        'Collection': '4',
        'ComputationalNotebook': '5',
        'ConferencePaper': '6',
        'ConferenceProceeding': '7',
        'DataPaper': '8',
        'Dataset': '9',
        'Dissertation': '10',
        'Event': '11',
        'Image': '12',
        'Instrument': '13',
        'InteractiveResource': '14',
        'Journal': '15',
        'JournalArticle': '16',
        'Model': '17',
        'OutputManagementPlan': '18',
        'PeerReview': '19',
        'PhysicalObject': '20',
        'Preprint': '21',
        'Report': '22',
        'Service': '23',
        'Software': '24',
        'Sound': '25',
        'Standard': '26',
        'StudyRegistration': '27',
        'Text': '28',
        'Workflow': '29',
        'Other': '30'
      };
      return typeMap[value] || '30'; // Default to 'Other' if type not found
    }
  },
  // Rights
  'rightsList/ns:rights': {  // Beachte das ns: vor rights
    selector: '#input-rights-license',
    attribute: 'rightsIdentifier',
    transform: (value) => {
      return licenseMapping[value] || '1';
    }
  }
};

/**
 * Extracts license identifier from various formats
 * @param {Element} rightsNode - The XML rights element
 * @returns {string} The normalized license identifier
 */
function extractLicenseIdentifier(rightsNode) {
  // Try to get identifier from rightsIdentifier attribute first
  let identifier = rightsNode.getAttribute('rightsIdentifier');

  if (!identifier) {
    // Try to extract from rightsURI
    const uri = rightsNode.getAttribute('rightsURI');
    if (uri) {
      // Extract identifier from SPDX URL (e.g. "https://spdx.org/licenses/CC0-1.0.html" -> "CC0-1.0")
      const match = uri.match(/licenses\/([^/.]+)/);
      if (match) {
        identifier = match[1];
      }
    }
  }

  if (!identifier) {
    // Use text content as last resort
    identifier = rightsNode.textContent.trim();
  }

  return identifier;
}

/**
 * Creates a license mapping from API data
 * @returns {Promise<Object>} A promise that resolves to the license mapping
 */
async function createLicenseMapping() {
  try {
    const response = await $.getJSON('./api/v2/vocabs/licenses/all');
    const mapping = {};

    response.forEach(license => {
      mapping[license.rightsIdentifier] = license.rights_id.toString();
    });

    return mapping;
  } catch (error) {
    console.error('Error creating license mapping:', error);
    return {
      'CC-BY-4.0': '1',
      'CC0-1.0': '2',
      'GPL-3.0-or-later': '3',
      'MIT': '4',
      'Apache-2.0': '5',
      'EUPL-1.2': '6'
    };
  }
}

/**
* Maps title type to select option value
* @param {string} titleType - The type of the title from XML
* @returns {string} The corresponding select option value
*/
function mapTitleType(titleType) {
  const typeMap = {
    undefined: '1', // Main Title
    'AlternativeTitle': '2',
    'Subtitle': '3',
    'TranslatedTitle': '4'
  };
  return typeMap[titleType] || '1';
}

/**
 * Helper function to get text content of a node using XPath
 * @param {Node} contextNode - The context node to search from
 * @param {string} xpath - The XPath expression
 * @param {Document} xmlDoc - The XML document
 * @param {Function} resolver - The namespace resolver function
 * @returns {string} The text content of the matched node
 */
function getNodeText(contextNode, xpath, xmlDoc, resolver) {
  const node = xmlDoc.evaluate(
    xpath,
    contextNode,
    resolver,
    XPathResult.FIRST_ORDERED_NODE_TYPE,
    null
  ).singleNodeValue;

  return node ? node.textContent.trim() : '';
}

// Globale Variable für die Labor-Daten
let labData = [];

/**
 * Loads XML data into form fields according to mapping configuration
 * @param {Document} xmlDoc - The parsed XML document
 */
async function loadXmlToForm(xmlDoc) {
  console.log('Checking if laboratory field is Tagified:', $('input[name="laboratoryName[]"]')[0].tagify ? 'yes' : 'no');
  // Warte auf das Laden der Labordaten, falls noch nicht geschehen
  if (!labData || labData.length === 0) {
    try {
      labData = await $.getJSON("json/msl-labs.json");
    } catch (error) {
      console.error('Error loading laboratory data:', error);
      labData = [];
    }
  }
  // Erstelle das License-Mapping zuerst
  const licenseMapping = await createLicenseMapping();

  // Definiere das komplette XML_MAPPING mit dem erstellten licenseMapping
  const XML_MAPPING = {
    // Resource Information
    'identifier': {
      selector: '#input-resourceinformation-doi',
      attribute: 'textContent'
    },
    'publicationYear': {
      selector: '#input-resourceinformation-publicationyear',
      attribute: 'textContent'
    },
    'version': {
      selector: '#input-resourceinformation-version',
      attribute: 'textContent'
    },
    'resourceType': {
      selector: '#input-resourceinformation-resourcetype',
      attribute: 'resourceTypeGeneral',
      transform: (value) => {
        const typeMap = {
          'Audiovisual': '1',
          'Book': '2',
          'BookChapter': '3',
          'Collection': '4',
          'ComputationalNotebook': '5',
          'ConferencePaper': '6',
          'ConferenceProceeding': '7',
          'DataPaper': '8',
          'Dataset': '9',
          'Dissertation': '10',
          'Event': '11',
          'Image': '12',
          'Instrument': '13',
          'InteractiveResource': '14',
          'Journal': '15',
          'JournalArticle': '16',
          'Model': '17',
          'OutputManagementPlan': '18',
          'PeerReview': '19',
          'PhysicalObject': '20',
          'Preprint': '21',
          'Report': '22',
          'Service': '23',
          'Software': '24',
          'Sound': '25',
          'Standard': '26',
          'StudyRegistration': '27',
          'Text': '28',
          'Workflow': '29',
          'Other': '30'
        };
        return typeMap[value] || '30';
      }
    },
    // Rights
    'rightsList/ns:rights': {
      selector: '#input-rights-license',
      attribute: 'rightsIdentifier',
      transform: (value) => {
        return licenseMapping[value] || '1';
      }
    }
  };

  const nsResolver = xmlDoc.createNSResolver(xmlDoc.documentElement);
  const defaultNS = xmlDoc.documentElement.getAttribute('xmlns');

  function resolver(prefix) {
    if (prefix === 'ns') {
      return defaultNS;
    }
    return nsResolver.lookupNamespaceURI(prefix);
  }

  // Verarbeite zuerst die Standard-Mappings
  for (const [xmlPath, config] of Object.entries(XML_MAPPING)) {
    const nsPath = `/ns:resource/ns:${xmlPath}`;

    const xmlElements = xmlDoc.evaluate(
      nsPath,
      xmlDoc,
      resolver,
      XPathResult.FIRST_ORDERED_NODE_TYPE,
      null
    );

    const xmlNode = xmlElements.singleNodeValue;
    if (xmlNode) {

      const value = config.attribute === 'textContent'
        ? xmlNode.textContent
        : xmlNode.getAttribute(config.attribute);

      const transformedValue = config.transform ? config.transform(value) : value;

      $(config.selector).val(transformedValue);
    } else {
      console.log('No node found for path:', nsPath);
    }
  }

  // Verarbeite die Titel separat
  const titleNodes = xmlDoc.evaluate(
    '/ns:resource/ns:titles/ns:title',
    xmlDoc,
    resolver,
    XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
    null
  );

  // Reset titles
  $('input[name="title[]"]').closest('.row').not(':first').remove();
  $('input[name="title[]"]:first').val('');
  $('#input-resourceinformation-titletype').val('1');

  for (let i = 0; i < titleNodes.snapshotLength; i++) {
    const titleNode = titleNodes.snapshotItem(i);
    const titleType = titleNode.getAttribute('titleType');
    const titleText = titleNode.textContent;
    const titleLang = titleNode.getAttribute('xml:lang') || 'en';

    if (i === 0) {
      // First Title
      $('input[name="title[]"]:first').val(titleText);
      $('#input-resourceinformation-titletype').val(mapTitleType(titleType));
      if (titleType) {
        $('#container-resourceinformation-titletype').show();
      }
    } else {
      // Add Title - Clone new row
      $('#button-resourceinformation-addtitle').click();

      // Find last row
      const $lastRow = $('input[name="title[]"]').last().closest('.row');

      // Set values
      $lastRow.find('input[name="title[]"]').val(titleText);
      $lastRow.find('select[name="titleType[]"]').val(mapTitleType(titleType));
    }
  }
  // Processing Creators
  const creatorNodes = xmlDoc.evaluate(
    '/ns:resource/ns:creators/ns:creator',
    xmlDoc,
    resolver,
    XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
    null
  );

  // Reset existing authors
  $('#group-author .row[data-creator-row]').not(':first').remove();
  $('#group-author .row[data-creator-row]:first input').val('');

  for (let i = 0; i < creatorNodes.snapshotLength; i++) {
    const creatorNode = creatorNodes.snapshotItem(i);

    // Extract Creators
    const givenName = getNodeText(creatorNode, 'ns:givenName', xmlDoc, resolver);
    const familyName = getNodeText(creatorNode, 'ns:familyName', xmlDoc, resolver);
    const orcid = getNodeText(
      creatorNode,
      'ns:nameIdentifier[@nameIdentifierScheme="ORCID"]',
      xmlDoc,
      resolver
    ).replace('https://orcid.org/', '');

    // Extract Affiliations
    const affiliationNodes = xmlDoc.evaluate(
      'ns:affiliation',
      creatorNode,
      resolver,
      XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
      null
    );

    const affiliations = [];
    const rorIds = [];

    for (let j = 0; j < affiliationNodes.snapshotLength; j++) {
      const affNode = affiliationNodes.snapshotItem(j);
      const affiliationName = affNode.textContent;
      const rorId = affNode.getAttribute('affiliationIdentifier');

      if (affiliationName) {
        affiliations.push(affiliationName);
        if (rorId) {
          rorIds.push(rorId);
        }
      }
    }

    if (i === 0) {
      // Erster Author - existierende Zeile verwenden
      const firstRow = $('#group-author .row[data-creator-row]:first');
      firstRow.find('input[name="orcids[]"]').val(orcid);
      firstRow.find('input[name="familynames[]"]').val(familyName);
      firstRow.find('input[name="givennames[]"]').val(givenName);

      // Initialisiere Tagify für die erste Zeile
      const tagifyInput = firstRow.find('input[name="affiliation[]"]')[0];
      if (tagifyInput) {

        const tagify = new Tagify(tagifyInput);

        // Affiliations als Array von Objekten übergeben
        tagify.addTags(affiliations.map(a => ({ value: a })));

        firstRow.find('input[name="authorRorIds[]"]').val(rorIds.join(','));
      }
    } else {
      // Weitere Autoren - Button-Click simulieren
      $('#button-author-add').click();

      // Finde die neu hinzugefügte Zeile
      const newRow = $('#group-author .row[data-creator-row]').last();

      // Setze die Werte
      newRow.find('input[name="orcids[]"]').val(orcid);
      newRow.find('input[name="familynames[]"]').val(familyName);
      newRow.find('input[name="givennames[]"]').val(givenName);

      // Warte kurz, bis Tagify initialisiert ist
      setTimeout(() => {
        const tagifyInput = newRow.find('input[name="affiliation[]"]')[0];
        if (tagifyInput && tagifyInput.tagify) {

          // Affiliations als Array von Objekten übergeben
          tagifyInput.tagify.addTags(affiliations.map(a => ({ value: a })));

          newRow.find('input[name="authorRorIds[]"]').val(rorIds.join(','));
        }
      }, 100);
    }
  }
  // Process Contact Persons
  const contactPersonNodes = xmlDoc.evaluate(
    '/ns:resource/ns:contributors/ns:contributor[@contributorType="ContactPerson"]',
    xmlDoc,
    resolver,
    XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
    null
  );

  // Reset existing Contact Persons
  $('#group-contactperson .row[contact-person-row]').not(':first').remove();
  $('#group-contactperson .row[contact-person-row]:first input').val('');

  let validContactPersonCount = 0;

  for (let i = 0; i < contactPersonNodes.snapshotLength; i++) {
    const contactPersonNode = contactPersonNodes.snapshotItem(i);

    // Extract relevant data
    const givenName = getNodeText(contactPersonNode, 'ns:givenName', xmlDoc, resolver);
    const familyName = getNodeText(contactPersonNode, 'ns:familyName', xmlDoc, resolver);

    // Skip this contact person if either given name or family name is missing
    if (!givenName || !familyName) {
      continue;
    }

    // Extract additional data only if we have both names
    const position = getNodeText(contactPersonNode, 'ns:position', xmlDoc, resolver);
    const email = getNodeText(contactPersonNode, 'ns:email', xmlDoc, resolver);
    const website = getNodeText(contactPersonNode, 'ns:onlineResource', xmlDoc, resolver);
    const affiliation = getNodeText(contactPersonNode, 'ns:affiliation', xmlDoc, resolver);

    if (validContactPersonCount === 0) {
      // First valid Contact Person - use the existing row
      const firstRow = $('#group-contactperson .row[contact-person-row]:first');
      firstRow.find('input[name="cpFirstname[]"]').val(givenName);
      firstRow.find('input[name="cpLastname[]"]').val(familyName);
      firstRow.find('input[name="cpPosition[]"]').val(position);
      firstRow.find('input[name="cpEmail[]"]').val(email);
      firstRow.find('input[name="cpOnlineResource[]"]').val(website);
      firstRow.find('input[name="cpAffiliation[]"]').val(affiliation);
    } else {
      // Additional valid Contact Persons - clone a new row
      $('#button-contactperson-add').click();
      const newRow = $('#group-contactperson .row[contact-person-row]').last();
      newRow.find('input[name="cpFirstname[]"]').val(givenName);
      newRow.find('input[name="cpLastname[]"]').val(familyName);
      newRow.find('input[name="cpPosition[]"]').val(position);
      newRow.find('input[name="cpEmail[]"]').val(email);
      newRow.find('input[name="cpOnlineResource[]"]').val(website);
      newRow.find('input[name="cpAffiliation[]"]').val(affiliation);
    }

    validContactPersonCount++;
  }
  // Process Originating Laboratories
  console.log('Starting laboratory processing');

  const laboratoryNodes = xmlDoc.evaluate(
    '/ns:resource/ns:contributors/ns:contributor[@contributorType="HostingInstitution"]',
    xmlDoc,
    resolver,
    XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
    null
  );

  console.log(`Found ${laboratoryNodes.snapshotLength} laboratory nodes`);

  // Reset existing laboratories
  $('#group-originatinglaboratory .row[data-laboratory-row]').not(':first').remove();
  $('#group-originatinglaboratory .row[data-laboratory-row]:first input').val('');

  // Hilfsfunktion zum Finden des Labornamens anhand der ID
  function findLabNameById(labId) {
    if (!labData) {
      console.error('labData is not available');
      return null;
    }
    return labData.find(lab => lab.id === labId) || null;
  }

  // Funktion zum Setzen des Labornamens mit Tagify
  function setLabNameWithTagify(row, labId) {
    console.log('Setting lab name with Tagify for ID:', labId);

    // Prüfe ob labData verfügbar ist
    if (typeof labData === 'undefined') {
      console.error('labData is not available');
      return;
    }

    const inputName = row.find('input[name="laboratoryName[]"]')[0];
    console.log('Found name input element:', inputName);

    if (!inputName) {
      console.error('Input element not found');
      return;
    }

    const lab = findLabNameById(labId);
    console.log('Found lab:', lab);

    if (!lab) {
      console.error('Lab not found');
      return;
    }

    try {
      // Prüfe ob eine Tagify-Instanz existiert
      if (inputName.tagify) {
        console.log('Using existing Tagify instance');
        inputName.tagify.removeAllTags();
        inputName.tagify.addTags([lab.name]);
      } else {
        console.log('Creating new Tagify instance');

        // Erstelle neue Tagify direkt hier
        const tagify = new Tagify(inputName, {
          whitelist: labData.map(item => item.name),
          enforceWhitelist: true,
          maxTags: 1,
          dropdown: {
            maxItems: 20,
            closeOnSelect: true,
            highlightFirst: true
          },
          delimiters: null,
          mode: "select"
        });

        // Setze den Wert nach kurzer Verzögerung
        setTimeout(() => {
          console.log('Setting initial value after delay');
          tagify.removeAllTags();
          tagify.addTags([lab.name]);
        }, 100);
      }

      // Finde auch das Affiliation-Feld und setze es
      const inputAffiliation = row.find('input[name="laboratoryAffiliation[]"]')[0];
      if (inputAffiliation && inputAffiliation.tagify) {
        console.log('Setting affiliation');
        inputAffiliation.tagify.removeAllTags();
        inputAffiliation.tagify.addTags([lab.affiliation]);
      }

      // Setze hidden fields
      const hiddenRorId = row.find('input[name="laboratoryRorIds[]"]');
      const hiddenLabId = row.find('input[name="LabId[]"]');

      if (hiddenRorId.length) hiddenRorId.val(lab.ror_id || '');
      if (hiddenLabId.length) hiddenLabId.val(lab.id);

      console.log('Successfully set all lab fields');

    } catch (error) {
      console.error('Error in setLabNameWithTagify:', error);
      console.error('Error stack:', error.stack);
    }
  }


  for (let i = 0; i < laboratoryNodes.snapshotLength; i++) {
    console.log(`Processing laboratory ${i + 1} of ${laboratoryNodes.snapshotLength}`);

    const labNode = laboratoryNodes.snapshotItem(i);
    console.log('Laboratory node:', labNode);

    // Extract laboratory ID
    const labId = getNodeText(labNode, 'ns:nameIdentifier[@nameIdentifierScheme="labid"]', xmlDoc, resolver);
    console.log('Extracted lab ID:', labId);

    // Skip if no lab ID
    if (!labId) {
      console.log('Skipping laboratory with missing ID');
      continue;
    }

    if (i === 0) {
      console.log('Processing first laboratory');
      // First laboratory - use existing row
      const firstRow = $('#group-originatinglaboratory .row[data-laboratory-row]:first');
      console.log('First row element:', firstRow[0]);

      // Set lab ID in hidden field
      firstRow.find('input[name="LabId[]"]').val(labId);
      console.log('Set lab ID in hidden field:', labId);

      // Set lab name using Tagify
      setLabNameWithTagify(firstRow, labId);

    } else {
      console.log(`Processing additional laboratory ${i + 1}`);
      // Additional laboratories - clone new row
      $('#button-originatinglaboratory-add').click();

      // Find the newly added row
      const newRow = $('#group-originatinglaboratory .row[data-laboratory-row]').last();
      console.log('New row element:', newRow[0]);

      // Set lab ID
      newRow.find('input[name="LabId[]"]').val(labId);
      console.log('Set lab ID in hidden field:', labId);

      // Set lab name using Tagify
      setLabNameWithTagify(newRow, labId);
    }
  }
  // Helper function to get or create a new person row
  function getOrCreatePersonRow(index) {
    const container = $('#group-contributorperson');
    if (index === 0) {
      return container.find('[contributor-person-row]').first();
    }

    // Simulate click on add button to create new row
    $('#button-contributor-addperson').click();

    // Return the newly created row
    return container.find('.row').last();
  }

  // Helper function to get or create a new organization row
  function getOrCreateOrgRow(index) {
    const container = $('#group-contributororganisation');
    if (index === 0) {
      return container.find('[contributors-row]').first();
    }

    // Simulate click on add button to create new row
    $('#button-contributor-addorganisation').click();

    // Return the newly created row
    return container.find('.row').last();
  }
  // Process contributors
  const contributorsNode = xmlDoc.evaluate(
    '/ns:resource/ns:contributors',
    xmlDoc,
    resolver,
    XPathResult.FIRST_ORDERED_NODE_TYPE,
    null
  ).singleNodeValue;

  if (contributorsNode) {
    console.log('Found contributors node');

    // Get all contributors except ContactPerson and HostingInstitution
    const contributorNodes = xmlDoc.evaluate(
      'ns:contributor[not(@contributorType="ContactPerson") and not(@contributorType="HostingInstitution")]',
      contributorsNode,
      resolver,
      XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
      null
    );

    console.log(`Found ${contributorNodes.snapshotLength} additional contributors`);

    for (let i = 0; i < contributorNodes.snapshotLength; i++) {
      const contributor = contributorNodes.snapshotItem(i);
      const contributorType = contributor.getAttribute('contributorType');
      const nameType = getNodeText(contributor, 'ns:contributorName/@nameType', xmlDoc, resolver);
      const contributorName = getNodeText(contributor, 'ns:contributorName', xmlDoc, resolver);
      const givenName = getNodeText(contributor, 'ns:givenName', xmlDoc, resolver);
      const familyName = getNodeText(contributor, 'ns:familyName', xmlDoc, resolver);
      const orcid = getNodeText(contributor, 'ns:nameIdentifier[@nameIdentifierScheme="ORCID"]', xmlDoc, resolver);
      const affiliation = getNodeText(contributor, 'ns:affiliation', xmlDoc, resolver);

      // Determine if this is a person or institution
      const isPerson = nameType === 'Personal' || (givenName && familyName);

      if (isPerson) {
        // Handle person contributor
        const personRow = getOrCreatePersonRow(i);

        // Set ORCID if available
        if (orcid) {
          personRow.find('input[name="cbORCID[]"]').val(orcid);
        }

        // Set names
        personRow.find('input[name="cbPersonLastname[]"]').val(familyName);
        personRow.find('input[name="cbPersonFirstname[]"]').val(givenName);

        // Set role using Tagify
        const roleInput = personRow.find('input[name="cbPersonRoles[]"]')[0];
        if (roleInput && roleInput.tagify) {
          roleInput.tagify.addTags([{ value: contributorType }]);
        }

        // Set affiliation using Tagify
        const affiliationInput = personRow.find('input[name="cbAffiliation[]"]')[0];
        if (affiliationInput && affiliationInput.tagify) {
          affiliationInput.tagify.addTags("[affiliation]");
        }
      } else {
        // Handle organization contributor
        const orgRow = getOrCreateOrgRow(i);

        // Set organization name
        orgRow.find('input[name="cbOrganisationName[]"]').val(contributorName);

        // Set role using Tagify
        const roleInput = orgRow.find('input[name="cbOrganisationRoles[]"]')[0];
        if (roleInput && roleInput.tagify) {
          roleInput.tagify.addTags([{ value: contributorType }]);
        }

        // Set affiliation using Tagify
        const affiliationInput = orgRow.find('input[name="OrganisationAffiliation[]"]')[0];
        if (affiliationInput && affiliationInput.tagify) {
          affiliationInput.tagify.addTags([affiliation]);
        }
      }
    }
  }
}