<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class ApiTest
 *
 * This class contains unit tests for the API endpoints.
 * It uses GuzzleHttp to send HTTP requests and PHPUnit for assertions.
 */
class ApiTest extends TestCase
{
    /**
     * @var Client $client The HTTP client used to make requests to the API.
     */
    private $client;

    /**
     * @var string $baseUri The base URI of the API.
     */
    private $baseUri;

    /**
     * @var string $projectPath The project path relative to the server root.
     */
    private $projectPath;

    /**
     * Sets up the test environment before each test method is executed.
     *
     * Initializes the HTTP client and determines the base URI and project path
     * based on the environment (development or production).
     *
     * @return void
     */
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
            'verify' => false,
        ]);
    }

    /**
     * Constructs the full API URL for a given endpoint.
     *
     * @param string $endpoint The API endpoint (e.g., 'general/alive').
     *
     * @return string The full API URL including the project path.
     */
    private function getApiUrl($endpoint): string
    {
        $path = trim($this->projectPath . '/api/v2/' . ltrim($endpoint, '/'), '/');
        return "/{$path}";
    }

    /**
     * Tests that the health check endpoint returns the expected alive message.
     *
     * Sends a GET request to the 'general/alive' endpoint and asserts that the
     * response status code is 200 and the message in the response body is correct.
     *
     * @return void
     *
     * @throws GuzzleException If an error occurs during the HTTP request.
     */
    public function healthCheckShouldReturnAliveMessage()
    {
        $endpointUrl = $this->getApiUrl('general/alive');
        echo "\nTesting endpoint: {$endpointUrl}";

        $response = $this->client->get($endpointUrl);

        $this->assertEquals(200, $response->getStatusCode(), 'Expected status code 200.');

        $data = json_decode($response->getBody(), true);

        $this->assertArrayHasKey('message', $data, 'Response body should contain a "message" key.');
        $this->assertEquals("I'm still alive...", $data['message'], 'Expected message does not match.');
    }
}
