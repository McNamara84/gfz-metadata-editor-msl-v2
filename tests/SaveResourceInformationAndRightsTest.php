<?php
namespace Tests;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../settings.php';

class SaveResourceInformationAndRightsTest extends TestCase
{
    private $connection;

    protected function setUp(): void
    {
        global $connection;
        if (!$connection) {
            $connection = connectDb();
        }
        $this->connection = $connection;

        $dbname = 'geobrains-msl-test';
        if ($this->connection->select_db($dbname) === false) {
            $this->markTestSkipped("Testdatenbank '$dbname' nicht verfügbar.");
        }
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    private function cleanupTestData()
    {
        $this->connection->query("DELETE FROM Title WHERE Resource_resource_id IN (SELECT resource_id FROM Resource WHERE doi IN ('10.5880/GFZ', '10.5880/GFZ.45.57'))");
        $this->connection->query("DELETE FROM Resource WHERE doi IN ('10.5880/GFZ', '10.5880/GFZ.45.57')");
    }

    public function testSaveResourceInformationAndRights()
    {
        if (!function_exists('saveResourceInformationAndRights')) {
            require_once __DIR__ . '/../save/formgroups/save_resourceinformation_and_rights.php';
        }

        $postData = [
            "doi" => "10.5880/GFZ",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "dateEmbargo" => "2024-12-31",
            "resourcetype" => 1,
            "version" => 1.0,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Testing Dataset for Unit Test"],
            "titleType" => [1]
        ];

        $resource_id = saveResourceInformationAndRights($this->connection, $postData);

        // Überprüfen, ob eine Resource ID zurückgegeben wurde
        $this->assertIsInt($resource_id);
        $this->assertGreaterThan(0, $resource_id);

        // Überprüfen, ob die Daten korrekt in die Datenbank eingetragen wurden
        $stmt = $this->connection->prepare("SELECT * FROM Resource WHERE resource_id = ?");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->assertEquals($postData["doi"], $row["doi"]);
        $this->assertEquals($postData["year"], $row["year"]);
        $this->assertEquals($postData["dateCreated"], $row["dateCreated"]);
        $this->assertEquals($postData["dateEmbargo"], $row["dateEmbargoUntil"]);
        $this->assertEquals($postData["resourcetype"], $row["Resource_Type_resource_name_id"]);
        $this->assertEquals($postData["version"], $row["version"]);
        $this->assertEquals($postData["language"], $row["Language_language_id"]);
        $this->assertEquals($postData["Rights"], $row["Rights_rights_id"]);

        // Überprüfen, ob der Titel korrekt eingetragen wurde
        $stmt = $this->connection->prepare("SELECT * FROM Title WHERE Resource_resource_id = ?");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->assertEquals($postData["title"][0], $row["text"]);
        $this->assertEquals($postData["titleType"][0], $row["Title_Type_fk"]);
    }

    public function testSaveResourceInformationAndRightsWithThreeTitles()
    {
        if (!function_exists('saveResourceInformationAndRights')) {
            require_once __DIR__ . '/../save/formgroups/save_resourceinformation_and_rights.php';
        }

        $postData = [
            "doi" => "10.5880/GFZ.45.57",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "dateEmbargo" => "2024-12-31",
            "resourcetype" => 1,
            "version" => 1.1,
            "language" => 2,
            "Rights" => 2,
            "title" => [
                "Main Title for Multiple Title Test",
                "Subtitle for Multiple Title Test",
                "Alternative Title for Multiple Title Test"
            ],
            "titleType" => [1, 2, 3]  // Angenommen, 1 = Main, 2 = Subtitle, 3 = Alternative
        ];

        $resource_id = saveResourceInformationAndRights($this->connection, $postData);

        // Überprüfen, ob eine Resource ID zurückgegeben wurde
        $this->assertIsInt($resource_id);
        $this->assertGreaterThan(0, $resource_id);

        // Überprüfen, ob die Ressource-Daten korrekt in die Datenbank eingetragen wurden
        $stmt = $this->connection->prepare("SELECT * FROM Resource WHERE resource_id = ?");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->assertEquals($postData["doi"], $row["doi"]);
        $this->assertEquals($postData["year"], $row["year"]);
        $this->assertEquals($postData["dateCreated"], $row["dateCreated"]);
        $this->assertEquals($postData["dateEmbargo"], $row["dateEmbargoUntil"]);
        $this->assertEquals($postData["resourcetype"], $row["Resource_Type_resource_name_id"]);
        $this->assertEquals($postData["version"], $row["version"]);
        $this->assertEquals($postData["language"], $row["Language_language_id"]);
        $this->assertEquals($postData["Rights"], $row["Rights_rights_id"]);

        // Überprüfen, ob alle drei Titel korrekt eingetragen wurden
        $stmt = $this->connection->prepare("SELECT * FROM Title WHERE Resource_resource_id = ? ORDER BY Title_Type_fk");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(3, $result->num_rows, "Es sollten genau drei Titel gespeichert worden sein");

        $index = 0;
        while ($row = $result->fetch_assoc()) {
            $this->assertEquals($postData["title"][$index], $row["text"], "Der Titel an Position $index stimmt nicht überein");
            $this->assertEquals($postData["titleType"][$index], $row["Title_Type_fk"], "Der Titeltyp an Position $index stimmt nicht überein");
            $index++;
        }
    }
}