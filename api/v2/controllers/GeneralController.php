<?php
/**
 * Class GeneralController
 *
 * This controller provides general endpoints for the API.
 */
class GeneralController
{
    /**
     * Checks the health status of the service and returns a JSON response.
     *
     * Sends an HTTP response with status code 200 if the service is running,
     * or 500 if an error occurs.
     *
     * @return void
     */
    public function getAlive()
    {
        try {
            $response = [
                'message' => "I'm still alive..."
            ];
            http_response_code(200);
        } catch (Exception $e) {
            $response = [
                'error' => 'An error occurred during health check: ' . $e->getMessage()
            ];
            http_response_code(500);
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}