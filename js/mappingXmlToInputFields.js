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
  }
};

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
 * Loads XML data into form fields according to mapping configuration
 * @param {Document} xmlDoc - The parsed XML document
 */
function loadXmlToForm(xmlDoc) {
  // Define namespace resolver
  const nsResolver = xmlDoc.createNSResolver(xmlDoc.documentElement);
  const defaultNS = xmlDoc.documentElement.getAttribute('xmlns');

  function resolver(prefix) {
    if (prefix === 'ns') {
      return defaultNS;
    }
    return nsResolver.lookupNamespaceURI(prefix);
  }

  // Handle standard mappings
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
      // Get either the attribute value or text content based on the mapping configuration
      const value = config.attribute === 'textContent'
        ? xmlNode.textContent
        : xmlNode.getAttribute(config.attribute);

      console.log(`Found value for ${xmlPath}:`, value); // Debug output

      const transformedValue = config.transform ? config.transform(value) : value;
      console.log(`Transformed value:`, transformedValue); // Debug output

      $(config.selector).val(transformedValue);
    }
  }

  // Handle titles separately
  const titleNodes = xmlDoc.evaluate(
    '/ns:resource/ns:titles/ns:title',
    xmlDoc,
    resolver,
    XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
    null
  );

  // Process each title
  for (let i = 0; i < titleNodes.snapshotLength; i++) {
    const titleNode = titleNodes.snapshotItem(i);
    const titleText = titleNode.textContent;
    const titleType = titleNode.getAttribute('titleType');

    if (i === 0) {
      // First title goes into the main title field
      $('#input-resourceinformation-title').val(titleText);
    } else {
      // For additional titles, we need to add new rows
      $('#button-resourceinformation-addtitle').click(); // Click the + button to add new row

      // Get the newly created row
      const titleInputs = $('input[name="title[]"]');
      const titleTypeSelects = $('select[name="titleType[]"]');

      // Set values in the last row
      $(titleInputs[titleInputs.length - 1]).val(titleText);
      $(titleTypeSelects[titleTypeSelects.length - 1]).val(mapTitleType(titleType));
    }
  }
}