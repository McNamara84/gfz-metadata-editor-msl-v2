<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ApiTest extends TestCase
{
    private $client;
    private $baseUri;
    private $projectPath;

    protected function setUp(): void
    {
        // Get project root directory (2 levels up from tests directory)
        $projectRoot = dirname(__DIR__);

        // Get project folder name
        $this->projectPath = basename($projectRoot);

        // Construct local development URL
        $this->baseUri = "http://localhost";

        // For production testing, override with environment variable if set
        if (getenv('API_BASE_URL')) {
            $this->baseUri = rtrim(getenv('API_BASE_URL'), '/');
            $this->projectPath = '';  // No project subfolder needed in production
        }

        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 5.0,
            'verify' => false
        ]);
    }

    private function getApiUrl($endpoint): string
    {
        $path = trim($this->projectPath . '/api/v2/' . ltrim($endpoint, '/'), '/');
        return "/{$path}";
    }

    /**
     * @test
     */
    public function healthCheckShouldReturnAliveMessage()
    {
        $endpointUrl = $this->getApiUrl('general/alive');
        echo "\nTesting endpoint: {$endpointUrl}";

        $response = $this->client->get($endpointUrl);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);

        $this->assertArrayHasKey('message', $data);
        $this->assertEquals("I'm still alive...", $data['message']);
    }
}