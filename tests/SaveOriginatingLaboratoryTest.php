<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
use mysqli_sql_exception;

require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../save/formgroups/save_originatinglaboratory.php';

class SaveOriginatingLaboratoryTest extends TestCase
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
        $this->connection->query("DELETE FROM Resource_has_Originating_Laboratory");
        $this->connection->query("DELETE FROM Originating_Laboratory_has_Affiliation");
        $this->connection->query("DELETE FROM Originating_Laboratory");
        $this->connection->query("DELETE FROM Affiliation");
        $this->connection->query("DELETE FROM Resource");
    }

    /**
     * Speicherung eines einzelnen Originating Laboratory mit allen Feldern.
     */
    public function testSaveSingleOriginatingLaboratory()
    {
        $resourceData = [
            "doi" => "10.5880/GFZ.TEST.SINGLE.LAB",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "resourcetype" => 1,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Test Single Laboratory"],
            "titleType" => [1]
        ];
        $resource_id = saveResourceInformationAndRights($this->connection, $resourceData);

        $postData = [
            "laboratoryName" => ['[{"value":"Test Lab"}]'],
            "LabId" => ["1b9abbf97c7caa2d763b647d476b2910"],
            "laboratoryAffiliation" => ['[{"value":"Test University"}]'],
            "laboratoryRorIds" => ['[{"value":"https://ror.org/03yrm5c26"}]']
        ];

        saveOriginatingLaboratories($this->connection, $postData, $resource_id);

        // Überprüfen, ob das Originating Laboratory korrekt gespeichert wurde
        $stmt = $this->connection->prepare("SELECT * FROM Originating_Laboratory WHERE laboratoryname = ?");
        $labName = json_decode($postData["laboratoryName"][0], true)[0]["value"];
        $stmt->bind_param("s", $labName);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $this->assertNotNull($result, "Das Originating Laboratory wurde nicht gespeichert.");
        $this->assertEquals($labName, $result["laboratoryname"], "Der Laborname wurde nicht korrekt gespeichert.");
        $this->assertEquals($postData["LabId"][0], $result["labId"], "Die Lab ID wurde nicht korrekt gespeichert.");

        // Überprüfen der Verknüpfung zur Resource
        $stmt = $this->connection->prepare("SELECT * FROM Resource_has_Originating_Laboratory WHERE Resource_resource_id = ? AND Originating_Laboratory_originating_laboratory_id = ?");
        $stmt->bind_param("ii", $resource_id, $result["originating_laboratory_id"]);
        $stmt->execute();
        $this->assertEquals(1, $stmt->get_result()->num_rows, "Die Verknüpfung zur Resource wurde nicht korrekt erstellt.");

        // Überprüfen der Affiliation
        $stmt = $this->connection->prepare("SELECT a.name, a.rorId FROM Affiliation a 
                                            JOIN Originating_Laboratory_has_Affiliation olha ON a.affiliation_id = olha.Affiliation_affiliation_id
                                            WHERE olha.Originating_Laboratory_originating_laboratory_id = ?");
        $stmt->bind_param("i", $result["originating_laboratory_id"]);
        $stmt->execute();
        $affiliationResult = $stmt->get_result()->fetch_assoc();

        $this->assertEquals(json_decode($postData["laboratoryAffiliation"][0], true)[0]["value"], $affiliationResult["name"], "Der Name der Affiliation wurde nicht korrekt gespeichert.");
        $this->assertEquals(str_replace("https://ror.org/", "", json_decode($postData["laboratoryRorIds"][0], true)[0]["value"]), $affiliationResult["rorId"], "Die ROR-ID der Affiliation wurde nicht korrekt gespeichert.");
    }

    /**
     * Speicherung von drei vollständig ausgefüllten Originating Laboratories.
     */
    public function testSaveMultipleOriginatingLaboratories()
    {
        $resourceData = [
            "doi" => "10.5880/GFZ.TEST.MULTIPLE.LABS",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "resourcetype" => 1,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Test Multiple Laboratories"],
            "titleType" => [1]
        ];
        $resource_id = saveResourceInformationAndRights($this->connection, $resourceData);

        $postData = [
            "laboratoryName" => ['[{"value":"Lab A"}]', '[{"value":"Lab B"}]', '[{"value":"Lab C"}]'],
            "LabId" => ["1b9abbf97c7caa2d763b647d476b2910", "9cd562c216daa82792972a074a222c52", "09e434194091574963c80f83d586875d"],
            "laboratoryAffiliation" => ['[{"value":"University A"}]', '[{"value":"University B"}]', '[{"value":"University C"}]'],
            "laboratoryRorIds" => ['[{"value":"https://ror.org/03yrm5c26"}]', '[{"value":"https://ror.org/02nr0ka47"}]', '[{"value":"https://ror.org/0168r3w48"}]']
        ];

        saveOriginatingLaboratories($this->connection, $postData, $resource_id);

        // Überprüfen, ob alle drei Originating Laboratories korrekt gespeichert wurden
        for ($i = 0; $i < 3; $i++) {
            $stmt = $this->connection->prepare("SELECT * FROM Originating_Laboratory WHERE laboratoryname = ?");
            $labName = json_decode($postData["laboratoryName"][$i], true)[0]["value"];
            $stmt->bind_param("s", $labName);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            $this->assertNotNull($result, "Das Originating Laboratory " . ($i + 1) . " wurde nicht gespeichert.");
            $this->assertEquals($labName, $result["laboratoryname"], "Der Laborname " . ($i + 1) . " wurde nicht korrekt gespeichert.");
            $this->assertEquals($postData["LabId"][$i], $result["labId"], "Die Lab ID " . ($i + 1) . " wurde nicht korrekt gespeichert.");

            // Überprüfen der Verknüpfung zur Resource
            $stmt = $this->connection->prepare("SELECT * FROM Resource_has_Originating_Laboratory WHERE Resource_resource_id = ? AND Originating_Laboratory_originating_laboratory_id = ?");
            $stmt->bind_param("ii", $resource_id, $result["originating_laboratory_id"]);
            $stmt->execute();
            $this->assertEquals(1, $stmt->get_result()->num_rows, "Die Verknüpfung zur Resource für Labor " . ($i + 1) . " wurde nicht korrekt erstellt.");

            // Überprüfen der Affiliation
            $stmt = $this->connection->prepare("SELECT a.name, a.rorId FROM Affiliation a 
                                                JOIN Originating_Laboratory_has_Affiliation olha ON a.affiliation_id = olha.Affiliation_affiliation_id
                                                WHERE olha.Originating_Laboratory_originating_laboratory_id = ?");
            $stmt->bind_param("i", $result["originating_laboratory_id"]);
            $stmt->execute();
            $affiliationResult = $stmt->get_result()->fetch_assoc();

            $this->assertEquals(json_decode($postData["laboratoryAffiliation"][$i], true)[0]["value"], $affiliationResult["name"], "Der Name der Affiliation für Labor " . ($i + 1) . " wurde nicht korrekt gespeichert.");
            $this->assertEquals(str_replace("https://ror.org/", "", json_decode($postData["laboratoryRorIds"][$i], true)[0]["value"]), $affiliationResult["rorId"], "Die ROR-ID der Affiliation für Labor " . ($i + 1) . " wurde nicht korrekt gespeichert.");
        }
    }
}