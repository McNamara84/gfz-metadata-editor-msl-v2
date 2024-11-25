<?php
/**
 * Script to load an XML file and transform the content to the input fields of the HTML form.
 * 
 */

 /**
     * WIP: Load the XML file and transform the content to the input fields of the HTML form.
     *
     * TODO: @param string $xmlFile Path to the XML file
     * @return string HTML output
     */
 function load_xml_file() {
    // Path to XSLT file
    $xsltFile = '/schemas/XSLT/MappingFileToForm.xsl';
    // Generate HTML string
    $htmlFile = 'header.html';
    // Load HTML to string
    $htmlString = file_get_contents($htmlFile);
    // Next HTML file (Resource Information)
    $htmlFile = '/formgroups/resourceInformation.html';
    // Add Resource Information to HTML string
    $htmlString .= file_get_contents($htmlFile);


    // Create XSLT processor
    $xslt = new XSLTProcessor();

    
    return $htmlString;
 }

 load_xml_file();