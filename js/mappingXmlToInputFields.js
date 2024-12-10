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
  /*'language': {
    selector: '#input-resourceinformation-language',
    attribute: 'textContent',
    transform: (value) => {
      // Transform language code to select option value
      const langMap = {
        'en': '1',
        'de': '2',
        'fr': '3'
      };
      return langMap[value] || '1';
    }
  },
  'titles/title': {
    selector: '#input-resourceinformation-title',
    attribute: 'textContent'
  }*/
};

/**
* Loads XML data into form fields according to mapping configuration
* @param {Document} xmlDoc - The parsed XML document
*/
function loadXmlToForm(xmlDoc) {
  for (const [xmlPath, config] of Object.entries(XML_MAPPING)) {
    const xmlElements = xmlDoc.evaluate(
      `//${xmlPath}`,
      xmlDoc,
      null,
      XPathResult.FIRST_ORDERED_NODE_TYPE,
      null
    );

    const xmlNode = xmlElements.singleNodeValue;
    if (xmlNode) {
      const value = xmlNode[config.attribute];
      const transformedValue = config.transform ? config.transform(value) : value;

      $(config.selector).val(transformedValue);
    }
  }
}