<?php
///////////////////////////////////////////////////////////////////
// Description: French language file for the metadata editor     //
// Author: Matan Israel                                          //
// Version: 1.2                                                  //
// License: CC BY 4.0                                            //
///////////////////////////////////////////////////////////////////

$translations = [
    // Allgemeines
    'logotitle' => 'Metadata Editor 1.4',
    'choose' => 'Sélectionnez...',
    'valid' => 'valide',
    'PleaseChoose_invalid' => 'Veuillez sélectionner une option.',
    'lastname' => 'Nom',
    'firstname' => 'Prénom',
    'affiliation' => 'Affiliation',
    'role_label' => 'rôle(s)',
    'add' => '+',

    // Form Group Resource Information
    'resourceInformation' => 'Informations sur la ressource',
    'doi' => 'DOI',
    'doi_invalid' => 'Format 10.ORGANISATION/ID (i.e.: 10.57757/IUGG23-3081) ou laisser vide',
    'publicationYear' => 'Date de publication',
    'publicationYear_invalid' => 'Veuillez rentrer une année valide (4 chiffres).',
    'resourceType' => 'Type de ressource.',
    'version' => 'Version',
    'version_invalid' => 'Laisser vide en cas de doute',
    'languageofdataset' => 'Langue du jeu de données',
    'title' => 'Titre',
    'title_invalid' => 'Veuillez donner un titre',
    'titleType' => 'Type de titre',

    // Form Group Rights
    'rights' => 'Droits et licences',
    'rightsTitle' => 'Nom de la license',

    // Form Group Authors
    'authors' => 'Auteurs',
    'lastname_invalid' => 'Veuillez entrer un nom pour le créateur',
    'firstname_invalid' => 'Veuillez entrer le nom du créateur.',
    'role_tooltip' => 'Rôle du contributeur dans la création de la ressource..',
    'authorORCID' => 'ORCID',
    'authorORCID_tooltip' => 'Identifiez de manière unique une personne physique ou morale, selon différents schémas.',

    'authorORCID_invalid' => 'Donnez un ORCID valide (XXXX-XXXX-XXXX-XXX(X))',
    'affiliation_invalid' => 'Veuillez donner entrer une affiliation.',

    // Form Group Contact Persons
    'contactpersons' => 'Contacts',
    'contactpersons_tooltip' => 'La personne de contact pour l\'accès à l\'ensemble des données et pour les demandes de renseignements.',
    'CP_lastname_invalid' => 'Veuillez entrer le nom de la personne à contacter.',
    'CP_firstname_invalid' => 'Veuillez entrer le prénom de la personne à contacter.',
    'email' => 'Adresse email',
    'email_invalid' => 'L\'adresse email est invalide.',
    'position' => 'Position',
    'position_invalid' => 'Veuillez entrer la position de la personne.',
    'website' => 'Site internet',
    'website_invalid' => 'Veuillez entrer le site web de l\'organisation.',

    // From Group Contributors
    'contributors' => "Contributeurs",
    'contributors_lastname_invalid' => 'Veuillez entrer le nom du contributeur; seules les lettres sont acceptées.',
    'contributors_firstname_invalid' => 'Veuillez entrer le prénom du contributeur; seules les lettre sont acceptées.',
    'Organisation' => 'Nom de l\'organisation',
    'Organisation_invalid' => 'Veuillez entrer un nom valide.',
    'contributors_ORCID' => 'Contributeurs ORCID',
    'contributors_ORCID_invalid' => 'Veuillez entrer un ORCID valide (XXXX-XXXX-XXXX-XXX(X))',
    'contributorsPersonZeile' => 'Contributeurs (personnes)',
    'contributorsInstitutionZeile' => 'Contributeurs (institutions)',

    // Form Group Descriptions
    // TODO: Übersetzen
    'descriptions' => 'Descriptions',
    'd_abstract' => 'Abstract',
    'd_methods' => 'Methods',
    'd_technical_infos' => 'Technical Infos',
    'd_other' => 'Other',

    // Form Group General
    'grl' => 'General',
    'grl_Material' => 'Material',
    'grl_geo_age' => 'Geological age',
    'grl_Pore_fluid' => 'Pore fluid',
    'grl_geo_setting' => 'Geological setting',


    // Form Group Dates
    'dates' => 'Date',
    'dateCreated' => 'Date de création.',
    'dateCreated_invalid' => 'Veuillez entrer une date valide.',
    'embargoUntil' => 'Embargo jusqu\'à',
    'embargo_invalid' => 'Veuillez entrer une date valide.',

    // TODO: Form Group Spatial and Temporal Coverages

    // Form Group Thesaurus Keywords
    // TODO: Übersetzen
    'thesaurusKeywords' => 'Thesaurus Keywords',
    'thesaurus' => 'Thesaurus',
    'scienceKeywords' => 'NASA GCMD Science Keywords',
    'instruments' => 'NASA GCMD Instruments',
    'platforms' => 'NASA GCMD Platforms',
    'chrono' => 'International Chronostratigraphic Chart',
    'cgi' => 'CGI Simple Lithology',
    'Gemet' => 'GEMET Thesaurus',
    'thesaurusLabel' => 'Open thesaurus to choose keywords or start typing...',

    // TODO: Übersetzen
    // Free Keywords
    'freekeywords' => 'Free Keywords',
    'freekeywords_input' => 'Please enter keywords and separate them by a comma.',

    // TODO: Form Group Spatial and Temooral Coverages
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
    //TODO: Übersetzen
    'relatedwork' => 'Related work',
    'relation' => 'Relation',
    'r_identifier_type' => 'Identifier type',
    'r_Identifier' => 'Identifier',
    'r_Identifier_invalid' => 'Please enter a valid identifier.',

    //Formgroup Funding Reference
    //TODO: Übersetzen
    'funding_reference' => 'Funding Reference',
    'funder' => 'Funder',
    'grant_Number' => 'Grant Number',
    'grant_Name' => 'Grant Name',


    // Buttons
    'clear' => 'Effacer',
    'clear_tooltip' => 'Tout effacer.',
    'load' => 'Charger',
    'load_tooltip' => 'Charger les donnés et le contenu.',
    'saveAs' => 'Sauvegarder',
    'saveAs_tooltip' => 'Sauvegarder les données ou le contenut sous un nouveau nom.',
    'submit' => 'Transmettre',
    'submit_tooltip' => 'Transmettre les données actuelles.',
    // TDOD: Übersetzen
    'helpButtonon' => 'On',
    'helpButtonoff' => 'Off',
];