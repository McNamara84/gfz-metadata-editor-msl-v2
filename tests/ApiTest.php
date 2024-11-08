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

        // Get base URI from environment variable or use default
        $this->baseUri = getenv('API_BASE_URL') ?: 'http://localhost:8000';

        // Log the base URI for debugging
        echo "\nUsing base URI: " . $this->baseUri;

        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 5.0,
            'verify' => false,
            'http_errors' => false // Prevents throwing exceptions on 4xx/5xx responses
        ]);
    }

    private function getApiUrl($endpoint): string
    {
        // In CI environment, we don't need the project path
        if (getenv('API_BASE_URL')) {
            return '/api/v2/' . ltrim($endpoint, '/');
        }

        // For local development
        $path = trim($this->projectPath . '/api/v2/' . ltrim($endpoint, '/'), '/');
        return "/{$path}";
    }

    public function testHealthCheckShouldReturnAliveMessage(): void
    {
        $endpointUrl = $this->getApiUrl('general/alive');
        echo "\nTesting endpoint: " . $this->baseUri . $endpointUrl;

        try {
            $response = $this->client->get($endpointUrl);

            echo "\nResponse Status: " . $response->getStatusCode();
            echo "\nResponse Body: " . $response->getBody();

            $this->assertEquals(
                200,
                $response->getStatusCode(),
                'Expected status code 200. Response: ' . $response->getBody()
            );

            $data = json_decode($response->getBody(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->fail('Failed to parse JSON response: ' . json_last_error_msg());
            }

            $this->assertArrayHasKey('message', $data, 'Response body should contain a "message" key.');
            $this->assertEquals("I'm still alive...", $data['message'], 'Expected message does not match.');
        } catch (Exception $e) {
            echo "\nException: " . get_class($e);
            echo "\nMessage: " . $e->getMessage();
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $response = $e->getResponse();
                echo "\nResponse Status: " . $response->getStatusCode();
                echo "\nResponse Body: " . $response->getBody();
            }
            throw $e;
        }
    }

    public function testGetAllLicensesShouldReturnLicenseList(): void
    {
        $endpointUrl = $this->getApiUrl('vocabs/licenses/all');
        echo "\nTesting endpoint: " . $this->baseUri . $endpointUrl;

        try {
            $response = $this->client->get($endpointUrl);

            echo "\nResponse Status: " . $response->getStatusCode();
            echo "\nResponse Body: " . $response->getBody();

            $this->assertEquals(
                200,
                $response->getStatusCode(),
                'Expected status code 200. Response: ' . $response->getBody()
            );

            $data = json_decode($response->getBody(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->fail('Failed to parse JSON response: ' . json_last_error_msg());
            }

            $this->assertIsArray($data, 'Response should be an array of licenses');

            // Test structure of first license if array is not empty
            if (!empty($data)) {
                $firstLicense = $data[0];
                $this->assertArrayHasKey('rightsIdentifier', $firstLicense, 'License should have rightsIdentifier');
                $this->assertArrayHasKey('text', $firstLicense, 'License should have text');

                // Optional URL field
                if (isset($firstLicense['url'])) {
                    $this->assertIsString($firstLicense['url'], 'URL should be a string if present');
                }
            }
        } catch (Exception $e) {
            $this->handleTestException($e);
        }
    }

    /**
     * Tests the endpoint that returns software licenses
     */
    public function testGetSoftwareLicensesShouldReturnSoftwareLicenseList(): void
    {
        $endpointUrl = $this->getApiUrl('vocabs/licenses/software');
        echo "\nTesting endpoint: " . $this->baseUri . $endpointUrl;

        try {
            $response = $this->client->get($endpointUrl);

            echo "\nResponse Status: " . $response->getStatusCode();
            echo "\nResponse Body: " . $response->getBody();

            $this->assertEquals(
                200,
                $response->getStatusCode(),
                'Expected status code 200. Response: ' . $response->getBody()
            );

            $data = json_decode($response->getBody(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->fail('Failed to parse JSON response: ' . json_last_error_msg());
            }

            $this->assertIsArray($data, 'Response should be an array of software licenses');

            // Test structure of first license if array is not empty
            if (!empty($data)) {
                $firstLicense = $data[0];
                $this->assertArrayHasKey('rightsIdentifier', $firstLicense, 'License should have rightsIdentifier');
                $this->assertArrayHasKey('text', $firstLicense, 'License should have text');

                // Optional URL field
                if (isset($firstLicense['url'])) {
                    $this->assertIsString($firstLicense['url'], 'URL should be a string if present');
                }
            }
        } catch (Exception $e) {
            $this->handleTestException($e);
        }
    }

    /**
     * Helper method to handle test exceptions
     */
    private function handleTestException($e): void
    {
        echo "\nException: " . get_class($e);
        echo "\nMessage: " . $e->getMessage();
        if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
            $response = $e->getResponse();
            echo "\nResponse Status: " . $response->getStatusCode();
            echo "\nResponse Body: " . $response->getBody();
        }
        throw $e;
    }
}
