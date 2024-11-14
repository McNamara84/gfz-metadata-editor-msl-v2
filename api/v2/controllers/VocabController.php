<?php
/**
 * VocabController.php
 *
 * This controller provides endpoints for fetching vocabularies via the API.
 *
 */

// Include settings.php so that variables are available
require_once __DIR__ . '/../../../settings.php';

/**
 * Class VocabController
 *
 * Handles vocabulary-related API requests.
 */
class VocabController
{
    /**
     * @var string The URL for MSL Labs data.
     */
    private $url;

    /**
     * @var string The base URL for MSL vocabularies.
     */
    private $mslVocabsUrl;

    /**
     * VocabController constructor.
     *
     * Initializes URLs using global variables.
     */
    public function __construct()
    {
        global $mslLabsUrl;
        global $mslVocabsUrl;
        $this->url = $mslLabsUrl;
        $this->mslVocabsUrl = $mslVocabsUrl;
    }

    /**
     * Retrieves relation data from the database and returns it as JSON.
     *
     * @return void
     */
    public function getRelations()
    {
        global $connection;
        $stmt = $connection->prepare('SELECT relation_id, name, description FROM Relation');

        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to prepare statement: ' . $connection->error]);
            return;
        }

        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to execute statement: ' . $stmt->error]);
            return;
        }

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $relations = [];
            while ($row = $result->fetch_assoc()) {
                $relations[] = [
                    'id' => $row['relation_id'],
                    'name' => $row['name'],
                    'description' => $row['description']
                ];
            }
            echo json_encode(['relations' => $relations]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No relations found']);
        }

        $stmt->close();
        exit();
    }

    /**
     * Fetches MSL Labs data from a remote URL, processes it, and returns the necessary fields.
     *
     * @return array Processed MSL Labs data.
     * @throws Exception If fetching or decoding the data fails.
     */
    public function fetchAndProcessMslLabs()
    {
        // Fetch data from the URL with a custom User-Agent
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: PHP Script'
            ]
        ];
        $context = stream_context_create($opts);
        $jsonData = file_get_contents($this->url, false, $context);

        if ($jsonData === false) {
            throw new Exception('Error fetching data from GitHub: ' . error_get_last()['message']);
        }

        // Correct character encoding
        $jsonData = mb_convert_encoding($jsonData, 'UTF-8', mb_detect_encoding($jsonData, 'UTF-8, ISO-8859-1', true));

        // Decode JSON data
        $labs = json_decode($jsonData, true);

        if ($labs === null) {
            throw new Exception('Error decoding JSON data: ' . json_last_error_msg());
        }

        // Process data and retain only necessary fields
        $processedLabs = array_map(function ($lab) {
            return [
                'id' => $lab['id'],
                'name' => $lab['lab_editor_name'],
                'affiliation' => $lab['affiliation']
            ];
        }, $labs);

        return $processedLabs;
    }

    /**
     * Retrieves the latest version number for a given vocabulary type.
     *
     * @param string $baseUrl The base URL for vocabularies.
     * @param string $type    The vocabulary type.
     * @return string|false The latest version string or false if not found.
     */
    private function getLatestVersion($baseUrl, $type)
    {
        $versions = [];
        for ($i = 1; $i <= 10; $i++) {
            $url = "{$baseUrl}{$type}/1.{$i}/{$type}_1-{$i}.json";
            $headers = @get_headers($url);
            if ($headers && strpos($headers[0], '200') !== false) {
                $versions[] = "1.{$i}";
            } else {
                break;
            }
        }
        return end($versions);
    }

    /**
     * Processes an individual item from the vocabulary data.
     *
     * @param array  $item      The item to process.
     * @param string $scheme    The scheme name.
     * @param string $schemeURI The scheme URI.
     * @return array The processed item.
     */
    private function processItem($item, $scheme, $schemeURI)
    {
        $newItem = [
            'id' => $item['uri'] ?? '',
            'text' => $item['label'] ?? $item['value'] ?? '',
            'language' => 'en',
            'scheme' => $scheme,
            'schemeURI' => $schemeURI,
            'description' => '',
            'children' => []
        ];

        if (isset($item['children']) && !empty($item['children'])) {
            foreach ($item['children'] as $child) {
                $newItem['children'][] = $this->processItem($child, $scheme, $schemeURI);
            }
        }
        return $newItem;
    }

    /**
     * Retrieves MSL vocabulary data for a specified type and saves it as JSON.
     *
     * @param array $vars An associative array of parameters.
     * @return void
     */
    public function getMslVocab($vars)
    {
        $type = $vars['type'] ?? $_GET['type'] ?? 'all';

        $types = [
            'analogue',
            'geochemistry',
            'geologicalage',
            'geologicalsetting',
            'materials',
            'microscopy',
            'paleomagnetism',
            'porefluids',
            'rockphysics'
        ];
        $jsonDir = __DIR__ . '/../../../json/';
        $combinedJsonFile = $jsonDir . 'msl-vocabularies.json';

        if (!file_exists($jsonDir)) {
            mkdir($jsonDir, 0755, true);
        }

        $results = [];
        $combinedData = [];

        if ($type == 'all') {
            foreach ($types as $t) {
                $latestVersion = $this->getLatestVersion($this->mslVocabsUrl, $t);
                if ($latestVersion) {
                    $url = "{$this->mslVocabsUrl}{$t}/{$latestVersion}/{$t}_" . str_replace('.', '-', $latestVersion) . ".json";
                    $jsonContent = $this->downloadContent($url);
                    if ($jsonContent !== false) {
                        $data = json_decode($jsonContent, true);
                        if (!empty($data)) {
                            $schemeURI = $data[0]['vocab_uri'] ?? '';
                            $scheme = 'EPOS WP16 ' . ucfirst($t);
                            $newRoot = [
                                'id' => $schemeURI,
                                'text' => ucfirst($t),
                                'language' => 'en',
                                'scheme' => $scheme,
                                'schemeURI' => $schemeURI,
                                'description' => '',
                                'children' => []
                            ];

                            foreach ($data as $item) {
                                $processedItem = $this->processItem($item, $scheme, $schemeURI);
                                $newRoot['children'][] = $processedItem;
                            }

                            $combinedData[] = $newRoot;
                            $results[$t] = "Updated to version {$latestVersion}";
                        } else {
                            $results[$t] = "No data found";
                        }
                    } else {
                        $results[$t] = "Failed to update";
                    }
                } else {
                    $results[$t] = "No version found";
                }
            }
        } elseif (in_array($type, $types)) {
            $latestVersion = $this->getLatestVersion($this->mslVocabsUrl, $type);
            if ($latestVersion) {
                $url = "{$this->mslVocabsUrl}{$type}/{$latestVersion}/{$type}_" . str_replace('.', '-', $latestVersion) . ".json";
                $jsonContent = $this->downloadContent($url);
                if ($jsonContent !== false) {
                    $data = json_decode($jsonContent, true);
                    if (!empty($data)) {
                        $schemeURI = $data[0]['vocab_uri'] ?? '';
                        $scheme = 'EPOS WP16 ' . ucfirst($type);
                        $newRoot = [
                            'id' => $schemeURI,
                            'text' => ucfirst($type),
                            'language' => 'en',
                            'scheme' => $scheme,
                            'schemeURI' => $schemeURI,
                            'description' => '',
                            'children' => []
                        ];

                        foreach ($data as $item) {
                            $processedItem = $this->processItem($item, $scheme, $schemeURI);
                            $newRoot['children'][] = $processedItem;
                        }

                        $combinedData[] = $newRoot;
                        $results[$type] = "Updated to version {$latestVersion}";
                    } else {
                        $results[$type] = "No data found";
                    }
                } else {
                    $results[$type] = "Failed to update";
                }
            } else {
                $results[$type] = "No version found";
            }
        } else {
            $results['error'] = "Invalid type specified";
        }

        // Save the combined data
        if (!empty($combinedData)) {
            file_put_contents($combinedJsonFile, json_encode($combinedData, JSON_PRETTY_PRINT));
        }

        header('Content-Type: application/json');
        echo json_encode([
            'message' => "Updating vocab for type: $type",
            'results' => $results
        ]);
    }

    /**
     * Downloads content from a given URL.
     *
     * @param string $url The URL to download content from.
     * @return string|false The content if successful, or false on failure.
     */
    private function downloadContent($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode == 200) ? $content : false;
    }

    /**
     * Retrieves GCMD Science Keywords from a local JSON file and returns them as JSON.
     *
     * @return void
     */
    public function getGcmdScienceKeywords()
    {
        try {
            $jsonPath = __DIR__ . '/../../../json/gcmdScienceKeywords.json';
            if (!file_exists($jsonPath)) {
                throw new Exception("Science Keywords file not found");
            }
            $json = file_get_contents($jsonPath);
            if ($json === false) {
                throw new Exception("Error reading Science Keywords file");
            }
            header('Content-Type: application/json');
            echo $json;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Updates the MSL Labs vocabulary by fetching and processing data, then saving it as JSON.
     *
     * @return void
     */
    public function updateMslLabs()
    {
        try {
            $mslLabs = $this->fetchAndProcessMslLabs();
            $jsonString = json_encode($mslLabs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            if ($jsonString === false) {
                throw new Exception('Error encoding data to JSON: ' . json_last_error_msg());
            }

            $result = file_put_contents(__DIR__ . '/../../../json/msl-labs.json', $jsonString);

            if ($result === false) {
                throw new Exception('Error saving JSON file: ' . error_get_last()['message']);
            }

            header('Content-Type: application/json');
            echo json_encode(['message' => 'MSL Labs vocabulary successfully updated']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Retrieves roles from the database based on the specified type and returns them as JSON.
     *
     * @param array $vars An associative array of parameters.
     * @return void
     */
    public function getRoles($vars)
    {
        global $connection;
        $type = $vars['type'] ?? $_GET['type'] ?? 'all';

        // SQL query based on the type
        if ($type == 'all') {
            $sql = 'SELECT * FROM Role';
        } elseif ($type == 'person') {
            $sql = 'SELECT * FROM Role WHERE forInstitutions = 0';
        } elseif ($type == 'institution') {
            $sql = 'SELECT * FROM Role WHERE forInstitutions = 1';
        } elseif ($type == 'both') {
            $sql = 'SELECT * FROM Role WHERE forInstitutions = 2';
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid roles type specified']);
            return;
        }

        if ($stmt = $connection->prepare($sql)) {
            $stmt->execute();
            $result = $stmt->get_result();
            $rolesList = $result->fetch_all(MYSQLI_ASSOC);

            if ($rolesList) {
                header('Content-Type: application/json');
                echo json_encode($rolesList);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'No roles found']);
            }

            $stmt->close();
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $connection->error]);
        }
    }

    /**
     * Updates timezone data by fetching it from an external API and saving it as JSON.
     *
     * @return void
     */
    public function updateTimezones()
    {
        global $apiKeyTimezone;

        try {
            // The TimeZoneDB API URL to fetch timezone data
            $apiUrl = 'http://api.timezonedb.com/v2.1/list-time-zone?key=' . urlencode($apiKeyTimezone) . '&format=json';

            // Fetch data from the external API
            $response = file_get_contents($apiUrl);
            if ($response === FALSE) {
                throw new Exception('Error fetching data from TimeZoneDB API.');
            }

            // Decode response into an array
            $data = json_decode($response, true);
            if ($data['status'] != 'OK') {
                throw new Exception('Error occurred: ' . $data['message']);
            }

            // Format timezones as UTC+X (Zone)
            $formattedTimezones = [];
            foreach ($data['zones'] as $zone) {
                $offsetHours = floor($zone['gmtOffset'] / 3600);
                $offsetMinutes = abs($zone['gmtOffset'] % 3600 / 60);
                $offset = sprintf('%+03d:%02d', $offsetHours, $offsetMinutes);
                $formattedTimezones[] = [
                    'value' => $zone['zoneName'],
                    'label' => sprintf('UTC%s (%s)', $offset, $zone['zoneName'])
                ];
            }

            // Cache data as a JSON string on the server
            $jsonDir = __DIR__ . '/../../../json/';
            if (!file_exists($jsonDir)) {
                mkdir($jsonDir, 0755, true);
            }
            $result = file_put_contents($jsonDir . 'timezones.json', json_encode($formattedTimezones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            if ($result === false) {
                throw new Exception('Error saving JSON file: ' . error_get_last()['message']);
            }

            header('Content-Type: application/json');
            echo json_encode([
                'message' => 'Timezones successfully updated',
                'timezones' => $formattedTimezones
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Retrieves licenses from the database, filtered by type, and returns them as JSON.
     *
     * @param bool $forSoftwareOnly If true, retrieves licenses only for software; otherwise, retrieves all licenses.
     * @return void
     */
    private function getLicensesByType($forSoftwareOnly = false)
    {
        try {
            $sql = $forSoftwareOnly
                ? 'SELECT * FROM Rights WHERE forSoftware = 1'
                : 'SELECT * FROM Rights';

            $result = $GLOBALS['connection']->query($sql);

            if (!$result) {
                throw new Exception("Database query failed");
            }

            $licenses = [];
            while ($row = $result->fetch_assoc()) {
                $licenses[] = $row;
            }

            if (empty($licenses)) {
                http_response_code(404);
                echo json_encode([
                    'error' => $forSoftwareOnly
                        ? 'No software licenses found'
                        : 'No licenses found'
                ]);
                return;
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($licenses);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Retrieves all licenses and returns them as JSON.
     *
     * @return void
     */
    public function getAllLicenses()
    {
        error_log("getAllLicenses called");
        try {
            error_log("Database connection status: " . ($GLOBALS['connection']->ping() ? "connected" : "not connected"));
            $this->getLicensesByType(false);
        } catch (Exception $e) {
            error_log("Error in getAllLicenses: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Retrieves software licenses and returns them as JSON.
     *
     * @return void
     */
    public function getSoftwareLicenses()
    {
        error_log("getSoftwareLicenses called");
        try {
            error_log("Database connection status: " . ($GLOBALS['connection']->ping() ? "connected" : "not connected"));
            $this->getLicensesByType(true);
        } catch (Exception $e) {
            error_log("Error in getSoftwareLicenses: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
