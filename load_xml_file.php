<?php
/**
 * Script to load an XML file and transform the content to the input fields of the HTML form.
 * 
 */

require_once 'settings.php';

/**
 * WIP: Load the HTML files for the form.
 *
 * TODO: @param string $xmlFile Path to the XML file
 * @return string HTML output
 */
function load_html()
{
    // Array with paths to HTML files
    $htmlFiles = [
        'header.html',
        'formgroups/resourceInformation.html',
        'formgroups/rights.html',
        'formgroups/authors.html',
        'formgroups/contactpersons.html',
        'formgroups/originatingLaboratory.html',
        'formgroups/contributors.html',
        'formgroups/descriptions.html',
        'formgroups/mslKeywords.html',
        'formgroups/thesaurusKeywords.html',
        'formgroups/freeKeywords.html',
        'formgroups/dates.html',
        'formgroups/spatialtemporalcoverage.html',
        'formgroups/relatedwork.html',
        'formgroups/fundingreference.html',
        'footer.html'
    ];
    $combinedHtml = '';
    foreach ($htmlFiles as $file) {
        $combinedHtml .= file_get_contents($file) . "\n";
    }

    return $combinedHtml;
}

/**
 * WIP: Load the XML file and transform the content to the input fields of the HTML form.
 *
 * TODO: @param string $xmlFile Path to the XML file
 * @return string HTML output
 */
function load_xml_file()
{
    // Path to XSLT file
    $xsltFile = '/schemas/XSLT/MappingFileToForm.xsl';


    // Create XSLT processor
    $xslt = new XSLTProcessor();


    return true;
}


echo load_html();