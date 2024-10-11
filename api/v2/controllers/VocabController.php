<?php
// settings.php einbinden damit Variablen verfügbar sind
require_once __DIR__ . '/../../../settings.php';
class VocabController
{
    private $url;

    public function __construct()
    {
        global $mslLabsUrl;
        $this->url = $mslLabsUrl;
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

    private function downloadAndSave($url, $savePath)
    {
        $json = @file_get_contents($url);
        if ($json === false) {
            return false;
        }
        // Schlüssel "uri" in JSON-Datei umbenennen in "id"
        $json = str_replace('"uri":', '"id":', $json);
        // Schlüssel "vocab_uri" in JSON-Datei umbenennen in "schemeURI"
        $json = str_replace('"vocab_uri":', '"schemeURI":', $json);
        // Schlüssel "label" in JSON-Datei umbenennen in "text"
        $json = str_replace('"label":', '"text":', $json);
        return file_put_contents($savePath, $json) !== false;
    }

    public function getMslVocab($vars)
    {
        $type = $vars['type'] ?? $_GET['type'] ?? 'all';

        $baseUrl = 'https://raw.githubusercontent.com/UtrechtUniversity/msl_vocabularies/main/vocabularies/';
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
                $latestVersion = $this->getLatestVersion($baseUrl, $t);
                if ($latestVersion) {
                    $url = "{$baseUrl}{$t}/{$latestVersion}/{$t}_" . str_replace('.', '-', $latestVersion) . ".json";
                    $jsonContent = $this->downloadContent($url);
                    if ($jsonContent !== false) {
                        $combinedData[$t] = json_decode($jsonContent, true);
                        $results[$t] = "Updated to version {$latestVersion}";
                    } else {
                        $results[$t] = "Failed to update";
                    }
                } else {
                    $results[$t] = "No version found";
                }
            }
        } elseif (in_array($type, $types)) {
            $latestVersion = $this->getLatestVersion($baseUrl, $type);
            if ($latestVersion) {
                $url = "{$baseUrl}{$type}/{$latestVersion}/{$type}_" . str_replace('.', '-', $latestVersion) . ".json";
                $jsonContent = $this->downloadContent($url);
                if ($jsonContent !== false) {
                    $combinedData[$type] = json_decode($jsonContent, true);
                    $results[$type] = "Updated to version {$latestVersion}";
                } else {
                    $results[$type] = "Failed to update";
                }
            } else {
                $results[$type] = "No version found";
            }
        } else {
            $results['error'] = "Invalid type specified";
        }

        // Merge new data with existing data
        if (file_exists($combinedJsonFile)) {
            $existingData = json_decode(file_get_contents($combinedJsonFile), true);
            $combinedData = array_merge($existingData, $combinedData);
        }

        // Save combined data
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

}
