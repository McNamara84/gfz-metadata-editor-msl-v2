<?php
/**
 * Script to save dataset metadata and trigger XML download
 * 
 * This script handles the complete saving process of a dataset:
 * 1. Saves all form data to the database using specialized functions
 * 2. Redirects to the API endpoint to trigger the XML download
 * 
 * The script expects to be located in a 'save' subdirectory and
 * requires settings.php to be one level up in the main directory.
 * All form group specific save functions are expected to be in
 * the local 'formgroups' directory.
 * 
 */

// Include required files
require_once '../settings.php';
require_once 'formgroups/save_resourceinformation_and_rights.php';
require_once 'formgroups/save_authors.php';
require_once 'formgroups/save_contactperson.php';
require_once 'formgroups/save_freekeywords.php';
require_once 'formgroups/save_contributors.php';
require_once 'formgroups/save_descriptions.php';
require_once 'formgroups/save_thesauruskeywords.php';
require_once 'formgroups/save_spatialtemporalcoverage.php';
require_once 'formgroups/save_relatedwork.php';
require_once 'formgroups/save_fundingreferences.php';

$resource_id = saveResourceInformationAndRights($connection, $_POST);
saveAuthors($connection, $_POST, $resource_id);
saveContactPerson($connection, $_POST, $resource_id);
saveContributors($connection, $_POST, $resource_id);
saveDescriptions($connection, $_POST, $resource_id);
saveThesaurusKeywords($connection, $_POST, $resource_id);
saveFreeKeywords($connection, $_POST, $resource_id);
saveSpatialTemporalCoverage($connection, $_POST, $resource_id);
saveRelatedWork($connection, $_POST, $resource_id);
saveFundingReferences($connection, $_POST, $resource_id);

/**
 * Build and execute redirect to API endpoint
 * Constructs the correct URL for the API endpoint and triggers the XML download
 */
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$base_url = $protocol . $_SERVER['HTTP_HOST'];
$project_path = dirname(dirname($_SERVER['PHP_SELF']));
$url = $base_url . $project_path . "/api/v2/dataset/export/" . $resource_id . "/all/download";

header("Location: " . $url);
ob_end_flush();
exit();