<?php
/**
 * Parses affiliation data from JSON string into an array of affiliation names
 * 
 * @param string|null $affiliation_data JSON string containing affiliation data in format [{"value": "affiliation name"}, ...]
 * @return array Array of affiliation names, empty array if input is invalid or empty
 */
function parseAffiliationData($affiliation_data)
{
    if (empty($affiliation_data)) {
        return [];
    }

    $affiliations = json_decode($affiliation_data, true);
    if (!$affiliations) {
        return [];
    }

    return array_map(function ($aff) {
        return $aff['value'];
    }, $affiliations);
}

/**
 * Parses ROR IDs from a comma-separated string into an array
 * Extracts the ID part from full ROR URLs if present
 * 
 * @param string|null $rorId_data Comma-separated string of ROR IDs (can be full URLs or just IDs)
 * @return array Array of ROR IDs (without URL prefix), null values for empty entries
 */
function parseRorIds($rorId_data)
{
    if (empty($rorId_data)) {
        return [];
    }

    $rorIds = explode(',', $rorId_data);
    return array_map(function ($rorId) {
        $rorId = trim($rorId);
        if (strpos($rorId, 'https://ror.org/') === 0) {
            $rorId = substr($rorId, strlen('https://ror.org/'));
        }
        return $rorId ?: null;
    }, $rorIds);
}
