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
      const value = xmlNode.textContent;
      const transformedValue = config.transform ? config.transform(value) : value;
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