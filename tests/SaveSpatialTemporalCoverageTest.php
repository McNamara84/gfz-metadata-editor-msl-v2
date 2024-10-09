<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
use mysqli_sql_exception;

require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../save/formgroups/save_spatialtemporalcoverage.php';

class SaveSpatialTemporalCoverageTest extends TestCase
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
            $this->markTestSkipped("Testdatenbank '$dbname' ist nicht verfügbar. Bitte überprüfen Sie die Datenbankverbindung.");
        }
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    private function cleanupTestData()
    {
        $this->connection->query("DELETE FROM Resource_has_Spatial_Temporal_Coverage");
        $this->connection->query("DELETE FROM Spatial_Temporal_Coverage");
        $this->connection->query("DELETE FROM Title");
        $this->connection->query("DELETE FROM Resource");
    }

    /**
     * Alle Felder wurden einmal befüllt
     */
    public function testSaveAllFieldsFilled()
    {
        $resourceData = [
            "doi" => "10.5880/GFZ.TEST.ALL.FIELDS",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "resourcetype" => 1,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Test All Fields STC"],
            "titleType" => [1]
        ];
        $resource_id = saveResourceInformationAndRights($this->connection, $resourceData);

        $postData = [
            "tscLatitudeMin" => ["40.7128"],
            "tscLatitudeMax" => ["40.7828"],
            "tscLongitudeMin" => ["-74.0060"],
            "tscLongitudeMax" => ["-73.9360"],
            "tscDescription" => ["New York City"],
            "tscDateStart" => ["2023-01-01"],
            "tscTimeStart" => ["00:00:00"],
            "tscDateEnd" => ["2023-12-31"],
            "tscTimeEnd" => ["23:59:59"],
            "tscTimezone" => ["-05:00"]
        ];

        $result = saveSpatialTemporalCoverage($this->connection, $postData, $resource_id);

        $this->assertTrue($result, "Die Funktion sollte true zurückgeben.");

        // Check if the STC was saved correctly
        $stmt = $this->connection->prepare("SELECT * FROM Spatial_Temporal_Coverage WHERE Description = ?");
        $stmt->bind_param("s", $postData["tscDescription"][0]);
        $stmt->execute();
        $stc = $stmt->get_result()->fetch_assoc();

        $this->assertNotNull($stc, "Der STC-Eintrag sollte gespeichert worden sein.");
        $this->assertEquals($postData["tscLatitudeMin"][0], $stc["latitudeMin"]);
        $this->assertEquals($postData["tscLatitudeMax"][0], $stc["latitudeMax"]);
        $this->assertEquals($postData["tscLongitudeMin"][0], $stc["longitudeMin"]);
        $this->assertEquals($postData["tscLongitudeMax"][0], $stc["longitudeMax"]);
        $this->assertEquals($postData["tscDateStart"][0] . " " . $postData["tscTimeStart"][0], $stc["dateTimeStart"]);
        $this->assertEquals($postData["tscDateEnd"][0] . " " . $postData["tscTimeEnd"][0], $stc["dateTimeEnd"]);
        $this->assertEquals("-05:00", $stc["timezone"]);

        // Check if the relation to the resource was created
        $stmt = $this->connection->prepare("SELECT * FROM Resource_has_Spatial_Temporal_Coverage WHERE Resource_resource_id = ? AND Spatial_Temporal_Coverage_spatial_temporal_coverage_id = ?");
        $stmt->bind_param("ii", $resource_id, $stc["spatial_temporal_coverage_id"]);
        $stmt->execute();
        $relation = $stmt->get_result()->fetch_assoc();

        $this->assertNotNull($relation, "Die Verknüpfung zwischen Resource und STC sollte existieren.");
    }

    /**
     * Eingabe von 3 vollständig ausgefüllten Datensätzen
     */
    public function testSaveThreeCompleteSets()
    {
        $resourceData = [
            "doi" => "10.5880/GFZ.TEST.THREE.SETS",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "resourcetype" => 1,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Test Three Sets STC"],
            "titleType" => [1]
        ];
        $resource_id = saveResourceInformationAndRights($this->connection, $resourceData);

        $postData = [
            "tscLatitudeMin" => ["40.7128", "51.5074", "48.8566"],
            "tscLatitudeMax" => ["40.7828", "51.5774", "48.9266"],
            "tscLongitudeMin" => ["-74.0060", "-0.1278", "2.3522"],
            "tscLongitudeMax" => ["-73.9360", "-0.0578", "2.4222"],
            "tscDescription" => ["New York", "London", "Paris"],
            "tscDateStart" => ["2023-01-01", "2023-02-01", "2023-03-01"],
            "tscTimeStart" => ["00:00:00", "00:00:00", "00:00:00"],
            "tscDateEnd" => ["2023-12-31", "2023-12-31", "2023-12-31"],
            "tscTimeEnd" => ["23:59:59", "23:59:59", "23:59:59"],
            "tscTimezone" => ["-05:00", "+00:00", "+01:00"]
        ];

        $result = saveSpatialTemporalCoverage($this->connection, $postData, $resource_id);

        $this->assertTrue($result, "Die Funktion sollte true zurückgeben.");

        // Check if all three STCs were saved correctly
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Spatial_Temporal_Coverage");
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];

        $this->assertEquals(3, $count, "Es sollten genau drei STC-Einträge gespeichert worden sein.");

        // Check if all three relations to the resource were created
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Resource_has_Spatial_Temporal_Coverage WHERE Resource_resource_id = ?");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];

        $this->assertEquals(3, $count, "Es sollten genau drei Verknüpfungen zwischen Resource und STC existieren.");
    }

    /**
     * Alle Felder wurden befüllt, bis auf Latitude Max und Longitude Max
     */
    public function testSaveWithoutMaxCoordinates()
    {
        $resourceData = [
            "doi" => "10.5880/GFZ.TEST.NO.MAX.COORDS",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "resourcetype" => 1,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Test No Max Coordinates STC"],
            "titleType" => [1]
        ];
        $resource_id = saveResourceInformationAndRights($this->connection, $resourceData);

        $postData = [
            "tscLatitudeMin" => ["40.7128"],
            "tscLatitudeMax" => [""],
            "tscLongitudeMin" => ["-74.0060"],
            "tscLongitudeMax" => [""],
            "tscDescription" => ["New York City"],
            "tscDateStart" => ["2023-01-01"],
            "tscTimeStart" => ["00:00:00"],
            "tscDateEnd" => ["2023-12-31"],
            "tscTimeEnd" => ["23:59:59"],
            "tscTimezone" => ["-05:00"]
        ];

        $result = saveSpatialTemporalCoverage($this->connection, $postData, $resource_id);

        $this->assertTrue($result, "Die Funktion sollte true zurückgeben.");

        // Check if the STC was saved correctly
        $stmt = $this->connection->prepare("SELECT * FROM Spatial_Temporal_Coverage WHERE Description = ?");
        $stmt->bind_param("s", $postData["tscDescription"][0]);
        $stmt->execute();
        $stc = $stmt->get_result()->fetch_assoc();

        $this->assertNotNull($stc, "Der STC-Eintrag sollte gespeichert worden sein.");
        $this->assertEquals($postData["tscLatitudeMin"][0], $stc["latitudeMin"]);
        $this->assertNull($stc["latitudeMax"]);
        $this->assertEquals($postData["tscLongitudeMin"][0], $stc["longitudeMin"]);
        $this->assertNull($stc["longitudeMax"]);
    }

    /**
     * Alle Felder wurden befüllt, aber Latitude Min und Longitude Max nicht
     */
    public function testSaveWithInvalidCoordinates()
    {
        $resourceData = [
            "doi" => "10.5880/GFZ.TEST.INVALID.COORDS",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "resourcetype" => 1,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Test Invalid Coordinates STC"],
            "titleType" => [1]
        ];
        $resource_id = saveResourceInformationAndRights($this->connection, $resourceData);

        $postData = [
            "tscLatitudeMin" => [""],
            "tscLatitudeMax" => ["40.7828"],
            "tscLongitudeMin" => ["-74.0060"],
            "tscLongitudeMax" => [""],
            "tscDescription" => ["New York City"],
            "tscDateStart" => ["2023-01-01"],
            "tscTimeStart" => ["00:00:00"],
            "tscDateEnd" => ["2023-12-31"],
            "tscTimeEnd" => ["23:59:59"],
            "tscTimezone" => ["-05:00"]
        ];

        $result = saveSpatialTemporalCoverage($this->connection, $postData, $resource_id);

        $this->assertFalse($result, "Die Funktion sollte false zurückgeben.");

        // Check that no STC was saved
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Spatial_Temporal_Coverage");
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];

        $this->assertEquals(0, $count, "Es sollten keine STC-Einträge gespeichert worden sein.");
    }

    /**
     * Alle Felder wurden ordnungsgemäß befüllt, aber es fehlt das Startdatum und die Startuhrzeit
     */
    public function testSaveWithoutStartDateTime()
    {
        $resourceData = [
            "doi" => "10.5880/GFZ.TEST.NO.START.DATETIME",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "resourcetype" => 1,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Test No Start DateTime STC"],
            "titleType" => [1]
        ];
        $resource_id = saveResourceInformationAndRights($this->connection, $resourceData);

        $postData = [
            "tscLatitudeMin" => ["40.7128"],
            "tscLatitudeMax" => ["40.7828"],
            "tscLongitudeMin" => ["-74.0060"],
            "tscLongitudeMax" => ["-73.9360"],
            "tscDescription" => ["New York City"],
            "tscDateStart" => [""],
            "tscTimeStart" => [""],
            "tscDateEnd" => ["2023-12-31"],
            "tscTimeEnd" => ["23:59:59"],
            "tscTimezone" => ["-05:00"]
        ];

        $result = saveSpatialTemporalCoverage($this->connection, $postData, $resource_id);

        $this->assertFalse($result, "Die Funktion sollte false zurückgeben.");

        // Check that no STC was saved
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Spatial_Temporal_Coverage");
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];

        $this->assertEquals(0, $count, "Es sollten keine STC-Einträge gespeichert worden sein.");
    }
}