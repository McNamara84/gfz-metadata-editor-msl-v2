<?php
class GeneralController
{
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