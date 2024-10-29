<?php
require_once './settings.php';
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
// RESOURCE INFORMATION and RIGHTS speichern
$resource_id = saveResourceInformationAndRights($connection, $_POST);
print $resource_id;
// AUTHORS
saveAuthors($connection, $_POST, $resource_id);
// CONTACT PERSONS
saveContactPerson($connection, $_POST, $resource_id);
// CONTRIBUTORS
saveContributors($connection, $_POST, $resource_id);
// DESCRIPTIONS
saveDescriptions($connection, $_POST, $resource_id);
// THESAURUS KEYWORDS
saveThesaurusKeywords($connection, $_POST, $resource_id);
// FREE KEYWORDS
saveFreeKeywords($connection, $_POST, $resource_id);
// STC (Spatial Temporal Coverage)
saveSpatialTemporalCoverage($connection, $_POST, $resource_id);
// RELATED WORK
saveRelatedWork($connection, $_POST, $resource_id);
// FUNDING REFERENCES
saveFundingReferences($connection, $_POST, $resource_id);

// API aufrufen und Datensatz als Datei mit allen 3 XML-Schemata herunterladen
$base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$url = $base_url . "api/v2/dataset/export/" . $resource_id . "/all/download";
header("Location: " . $url);
ob_end_flush();
exit();
