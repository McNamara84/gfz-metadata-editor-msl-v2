<?php
// settings.php einbinden damit Variablen verfügbar sind
require_once __DIR__ . '/../../../settings.php';
class ValidationController
{
    private $connection;
    public function __construct()
    {
        global $connection;
        $this->connection = $connection;
    }
    public function getPattern($vars)
{
    $type = $vars['type'] ?? null;
    if (!$type) {
        http_response_code(400);
        echo json_encode(['error' => 'No identifier type specified']);
        return;
    }

    $stmt = $this->connection->prepare('SELECT pattern FROM Identifier_Type WHERE name = ?');
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare statement: ' . $this->connection->error]);
        return;
    }

    $stmt->bind_param('s', $type);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to execute statement: ' . $stmt->error]);
        return;
    }
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode(['pattern' => $row['pattern']]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No pattern found for the specified identifier type']);
        }
        $stmt->close();
        exit();
    }
}
