<?php
class VocabController
{
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
        $jsonDir = __DIR__ . '../../../json/';

        if (!file_exists($jsonDir)) {
            mkdir($jsonDir, 0755, true);
        }

        $results = [];

        if ($type == 'all') {
            foreach ($types as $t) {
                $latestVersion = $this->getLatestVersion($baseUrl, $t);
                if ($latestVersion) {
                    $url = "{$baseUrl}{$t}/{$latestVersion}/{$t}_" . str_replace('.', '-', $latestVersion) . ".json";
                    $savePath = $jsonDir . "{$t}.json";
                    $success = $this->downloadAndSave($url, $savePath);
                    $results[$t] = $success ? "Updated to version {$latestVersion}" : "Failed to update";
                } else {
                    $results[$t] = "No version found";
                }
            }
        } elseif (in_array($type, $types)) {
            $latestVersion = $this->getLatestVersion($baseUrl, $type);
            if ($latestVersion) {
                $url = "{$baseUrl}{$type}/{$latestVersion}/{$type}_" . str_replace('.', '-', $latestVersion) . ".json";
                $savePath = $jsonDir . "{$type}.json";
                $success = $this->downloadAndSave($url, $savePath);
                $results[$type] = $success ? "Updated to version {$latestVersion}" : "Failed to update";
            } else {
                $results[$type] = "No version found";
            }
        } else {
            $results['error'] = "Invalid type specified";
        }

        header('Content-Type: application/json');
        echo json_encode([
            'message' => "Updating vocab for type: $type"
        ]);
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
}
