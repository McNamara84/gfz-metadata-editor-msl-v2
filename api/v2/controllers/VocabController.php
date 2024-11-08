<?php
// settings.php einbinden damit Variablen verfügbar sind
require_once __DIR__ . '/../../../settings.php';
/**
 * Class VocabController
 *
 * This controller provides endpoints for the fetching of vocabulaires with the API.
 */
class VocabController
{
    private $url;
    private $mslVocabsUrl;

    public function __construct()
    {
        global $mslLabsUrl;
        global $mslVocabsUrl;
        $this->url = $mslLabsUrl;
        $this->mslVocabsUrl = $mslVocabsUrl;
    }

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

    public function fetchAndProcessMslLabs()
    {
        // Daten von der URL abrufen mit User-Agent
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: PHP Script'
            ]
        ];
        $context = stream_context_create($opts);
        $jsonData = file_get_contents($this->url, false, $context);

        if ($jsonData === false) {
            throw new Exception('Fehler beim Abrufen der Daten von GitHub: ' . error_get_last()['message']);
        }

        // Zeichenkodierung korrigieren
        $jsonData = mb_convert_encoding($jsonData, 'UTF-8', mb_detect_encoding($jsonData, 'UTF-8, ISO-8859-1', true));

        // JSON-Daten decodieren
        $labs = json_decode($jsonData, true);

        if ($labs === null) {
            throw new Exception('Fehler beim Decodieren der JSON-Daten: ' . json_last_error_msg());
        }

        // Daten verarbeiten und nur benötigte Felder behalten
        $processedLabs = array_map(function ($lab) {
            return [
                'id' => $lab['id'],
                'name' => $lab['lab_editor_name'],
                'affiliation' => $lab['affiliation']
            ];
        }, $labs);

        return $processedLabs;
    }
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

    public function getMslVocab($vars)
    {
        $type = $vars['type'] ?? $_GET['type'] ?? 'all';

        $types = ['analogue', 'geochemistry', 'geologicalage', 'geologicalsetting', 'materials', 'microscopy', 'paleomagnetism', 'porefluids', 'rockphysics'];
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

        // Speichern der kombinierten Daten
        if (!empty($combinedData)) {
            file_put_contents($combinedJsonFile, json_encode($combinedData, JSON_PRETTY_PRINT));
        }

        header('Content-Type: application/json');
        echo json_encode([
            'message' => "Updating vocab for type: $type",
            'results' => $results
        ]);
    }


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
    public function getRoles($vars)
    {
        global $connection;
        $type = $vars['type'] ?? $_GET['type'] ?? 'all';

        // SQL-Abfrage basierend auf dem Typ
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

    public function updateTimezones()
    {
        global $apiKeyTimezone;

        try {
            // Die URL der TimeZoneDB API, um die Zeitzonendaten abzurufen
            $apiUrl = 'http://api.timezonedb.com/v2.1/list-time-zone?key=' . urlencode($apiKeyTimezone) . '&format=json';

            // Daten von der externen API abrufen
            $response = file_get_contents($apiUrl);
            if ($response === FALSE) {
                throw new Exception('Error fetching data from timezonedb API.');
            }

            // Antwort in ein Array dekodieren
            $data = json_decode($response, true);
            if ($data['status'] != 'OK') {
                throw new Exception('Error occurred: ' . $data['message']);
            }

            // Zeitzonen formatieren, UTC+X (Zone)
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

            // Daten als JSON-String auf Server zwischenspeichern
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
    private function getLicensesByType($forSoftwareOnly = false)
    {
        try {
            $sql = $forSoftwareOnly
                ? 'SELECT * FROM Rights WHERE forSoftware = 1'
                : 'SELECT * FROM Rights';

            $result = $GLOBALS['connection']->query($sql);

            if (!$result) {
                throw new Exception("Datenbankabfrage fehlgeschlagen");
            }

            $licenses = [];
            while ($row = $result->fetch_assoc()) {
                $licenses[] = $row;
            }

            if (empty($licenses)) {
                http_response_code(404);
                echo json_encode([
                    'error' => $forSoftwareOnly
                        ? 'Keine Softwarelizenzen gefunden'
                        : 'Keine Lizenzen gefunden'
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
