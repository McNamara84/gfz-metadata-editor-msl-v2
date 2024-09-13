<?php
$translations = [
    // Allgemeines
    'logotitle' => 'Metadata Editor 1.5',
    'choose' => 'Choose...',
    'valid' => 'Valid',
    'PleaseChoose_invalid' => 'Please choose',
    'lastname' => 'Last Name',
    'firstname' => 'First Name',
    'affiliation' => 'Affiliation(s)',
    'role_label' => 'Role(s)',
    'add' => '+',

    // Form Group Resource Information
    'resourceInformation' => 'Resource Information',
    'doi' => 'DOI',
    'doi_invalid' => 'Please use format 10.ORGANISATION/ID (i.e.: 10.57757/IUGG23-3081) or leave empty',
    'publicationYear' => 'Publication Year',
    'publicationYear_invalid' => 'Please provide a valid year (4 digits).',
    'resourceType' => 'Resource Type',
    'version' => 'Version',
    'version_invalid' => 'If unsure leave blank',
    'languageofdataset' => 'Language of dataset',
    'title' => 'Title',
    'title_invalid' => 'Please provide a title',
    'titleType' => 'Title Type',

    // Form Group Rights
    'rights' => 'Licenses and Rights',
    'rightsTitle' => 'License',

    // Form Group Authors
    'authors' => 'Author(s)',
    'lastname_invalid' => 'Please provide a lastname.',
    'firstname_invalid' => 'Please provide a firstname.',
    'authorORCID' => 'ORCID',
    'authorORCID_invalid' => 'Enter a valid ORCID in the format xxxx-xxxx-xxxx-xxxx',
    'affiliation_invalid' => 'Please provide an affiliation.',

    // Form Group Contactpersons
    'contactpersons' => 'Contact Person(s)',
    'CP_lastname_invalid' => 'Please provide The surname or last name of the contactperson.',
    'CP_firstname_invalid' => 'Please provide The personal or first name of the contactperson.',
    'email' => 'Email address',
    'email_invalid' => 'Email address is invalid.',
    'position' => 'Position',
    'position_invalid' => 'Please provide a position.',
    'website' => 'Website',
    'website_invalid' => 'Please provide a valid URL.',

    // From Group Contributors
    'contributors' => "Contributors",
    'contributors_lastname_invalid' => 'Please provide the last name of the contributor. Only letters are allowed.',
    'contributors_firstname_invalid' => 'Please provide the first name of the contributor. Only letters are allowed.',
    'Organisation' => 'Organisation name',
    'Organisation_invalid' => 'Please enter a valid organization name.',
    'contributors_ORCID' => 'Contributor ORCID',
    'contributors_ORCID_invalid' => 'Please enter a valid ORCID (XXXX-XXXX-XXXX-XXX(X))',
    'contributorsPersonZeile' => 'Contributing Person(s)',
    'contributorsInstitutionZeile' => 'Contributing Institution(s)',

    // From Group Spatial and temporal coverage

    // Form Group Descriptions
    'descriptions' => 'Descriptions',
    'd_abstract' => 'Abstract',
    'd_abstract_tooltip' => 'Please enter an abstract...',
    'd_methods' => 'Methods',
    'd_methods_tooltip' => 'Please describe the methods...',
    'd_technical_infos' => 'Technical Information',
    'd_technical_infos_tooltip' => 'Please enter technical information...',
    'd_other' => 'Other',
    'd_other_tooltip' => 'Please enter other information..',

    // Form Group General
    'grl' => 'General',
    'grl_Material' => 'Material',
    'grl_geo_age' => 'Geological age',
    'grl_Pore_fluid' => 'Pore fluid',
    'grl_geo_setting' => 'Geological setting',

    // Form Group Analogue modelling of geological processes
    'AMOGP' => 'Analogue modelling of geological processes',
    'AMOGP_M_S' => 'Modeled structure',
    'AMOGP_M_G_F' => 'Modeled geomorphological feature',
    'AMOGP_Apparatus' => 'Apparatus',
    'AMOGP_A_E' => 'Ancillary Equipment',
    'AMOGP_M_P' => 'measured property',
    'AMOGP_Software' => 'Software',

    // Form Group Geochemistry
    'Geochemistry' => 'Geochemistry',
    'Geochemistry_A_T' => 'Analysis technique',
    'Geochemistry_M_P' => 'Measured property',

    // Form Group Microsocopy And Tomography
    'MATomography' => 'Microsocopy And Tomography',
    'MATomography_A' => 'Apparatus',
    'MATomography_A_E' => 'Ancillary equipment',
    'MATomography_A_T' => 'Analysis technique',
    'MATomography_A_F' => 'Analyzed feature',
    'MATomography_I_P' => 'Inferred parameter',

    //Formgroup thesaurusKeywords
    // TODO: Translation
    'thesaurusKeywords' => 'Thesaurus Keywords',
    'thesaurus' => 'Thesaurus',
    'scienceKeywords' => 'NASA GCMD Science Keywords',
    'instruments' => 'NASA GCMD Instruments',
    'platforms' => 'NASA GCMD Platforms',
    'chrono' => 'International Chronostratigraphic Chart',
    'cgi' => 'CGI Simple Lithology',
    'Gemet' => 'GEMET Thesaurus',
    'thesaurusLabel' => 'Open thesaurus to choose keywords or start typing...',

    // Free Keywords
    'freekeywords' => 'Free Keywords',
    'freekeywords_input' => 'Please enter keywords and separate them by a comma.',

    // Form Group Dates
    'dates' => 'Dates',
    'dateCreated' => 'Date created',
    'dateCreated_invalid' => 'Please enter a valid date!',
    'embargoUntil' => 'Embargo until',
    'embargo_invalid' => 'Please enter a valid date!',

    // Form Group Spatial and Temooral Coverages
    'tsc_spatialTemporalCoverages' => 'Spatial and Temporal Coverages',
    'tsc_map' => 'Map',
    'tsc_latitude' => 'Latitude',
    'tsc_longitude' => 'Longitude',
    'tsc_description' => 'Description',
    'tsc_description_placeholder' => 'Please enter the description of the temporal and spatial coverage...',
    'tsc_start' => 'Start',
    'tsc_end' => 'End',
    'tsc_timezone' => 'Timezone',
    'tsc_map_info' => '(Please choose coordinates with rectangle or marker)',
    'tsc_cancel' => 'Cancel',
    'tsc_accept' => 'Accept',

    //Formgroup related Work
    'relatedwork' => 'Related work',
    'relation' => 'Relation',
    'r_identifier_type' => 'Identifier type',
    'r_Identifier' => 'Identifier',
    'r_Identifier_invalid' => 'Please enter a valid identifier.',

    //Formgroup Funding Reference
    'funding_reference' => 'Funding Reference',
    'funder' => 'Funder',
    'grant_Number' => 'Grant Number',
    'grant_Name' => 'Grant Name',

    // Buttons
    'clear' => 'Clear',
    'clear_tooltip' => 'Delete all entries or content.',
    'load' => 'Load',
    'load_tooltip' => 'Load saved data or content.',
    'saveAs' => 'Save',
    'saveAs_tooltip' => 'Save the current data or content under a new file name.',
    'submit' => 'Submit',
    'submit_tooltip' => 'Transmit the entered data or content.',
    'helpButtonon' => 'On',
    'helpButtonoff' => 'Off',

];