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
      console.log('Found rightsIdentifier value:', value); // Debug
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

    console.log('API Response:', response);

    response.forEach(license => {
      mapping[license.rightsIdentifier] = license.rights_id.toString();
    });

    console.log('Created License Mapping:', mapping);
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

/**
 * Loads XML data into form fields according to mapping configuration
 * @param {Document} xmlDoc - The parsed XML document
 */
async function loadXmlToForm(xmlDoc) {
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
        console.log('Found rightsIdentifier value:', value);
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
    console.log('Evaluating XPath:', nsPath);

    const xmlElements = xmlDoc.evaluate(
      nsPath,
      xmlDoc,
      resolver,
      XPathResult.FIRST_ORDERED_NODE_TYPE,
      null
    );

    const xmlNode = xmlElements.singleNodeValue;
    if (xmlNode) {
      console.log('Found XML node:', xmlNode);

      const value = config.attribute === 'textContent'
        ? xmlNode.textContent
        : xmlNode.getAttribute(config.attribute);

      console.log('Extracted value:', value);

      const transformedValue = config.transform ? config.transform(value) : value;
      console.log('Transformed value:', transformedValue);

      $(config.selector).val(transformedValue);
      console.log('Set value for selector:', config.selector, transformedValue);
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

    console.log('Processing title:', { titleType, titleText, titleLang });

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
        tagify.addTags(affiliations);
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
          tagifyInput.tagify.addTags(affiliations);
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
      console.log('Skipping incomplete contact person:',
        contactPersonNode.getElementsByTagName('contributorName')[0]?.textContent);
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
}