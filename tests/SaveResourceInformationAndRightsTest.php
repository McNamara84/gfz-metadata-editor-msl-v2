<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
use mysqli_sql_exception;

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
        $this->connection->query("DELETE FROM Title WHERE Resource_resource_id IN (SELECT resource_id FROM Resource WHERE doi IN ('10.5880/GFZ', '10.5880/GFZ.45.57', '10.5880/GFZ.TEST.NULL', '10.5880/GFZ.DUPLICATE.TEST', '10.5880/GFZ.DUPLICATE.TITLE.TEST'))");
        $this->connection->query("DELETE FROM Resource WHERE doi IN ('10.5880/GFZ', '10.5880/GFZ.45.57', '10.5880/GFZ.TEST.NULL', '10.5880/GFZ.DUPLICATE.TEST', '10.5880/GFZ.DUPLICATE.TITLE.TEST')");
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
    public function testSaveResourceInformationAndRightsWithNullValues()
    {
        if (!function_exists('saveResourceInformationAndRights')) {
            require_once __DIR__ . '/../save/formgroups/save_resourceinformation_and_rights.php';
        }

        $postData = [
            "doi" => null,
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "dateEmbargo" => null,
            "resourcetype" => 4,
            "version" => null,
            "language" => 2,
            "Rights" => 3,
            "title" => ["Testing Title"],
            "titleType" => [1]
        ];

        $resource_id = saveResourceInformationAndRights($this->connection, $postData);

        // Überprüfen, ob eine Resource ID zurückgegeben wurde
        $this->assertIsInt($resource_id);
        $this->assertGreaterThan(0, $resource_id);

        // Überprüfen, ob die Daten korrekt in die Datenbank eingetragen und abgerufen wurden
        $stmt = $this->connection->prepare("SELECT * FROM Resource WHERE resource_id = ?");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $this->assertNull($row["doi"]);
        $this->assertEquals($postData["year"], $row["year"]);
        $this->assertEquals($postData["dateCreated"], $row["dateCreated"]);
        $this->assertNull($row["dateEmbargoUntil"]);
        $this->assertEquals($postData["resourcetype"], $row["Resource_Type_resource_name_id"]);
        $this->assertNull($row["version"]);
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
    public function testSaveResourceInformationAndRightsWithEmptyRequiredFields()
    {
        if (!function_exists('saveResourceInformationAndRights')) {
            require_once __DIR__ . '/../save/formgroups/save_resourceinformation_and_rights.php';
        }

        $postData = [
            "doi" => null,
            "year" => null,
            "dateCreated" => null,
            "dateEmbargo" => null,
            "resourcetype" => null,
            "version" => null,
            "language" => null,
            "Rights" => null,
            "title" => [],
            "titleType" => []
        ];

        // Zählen der bestehenden Datensätze vor dem Test
        $countBefore = $this->connection->query("SELECT COUNT(*) as count FROM Resource")->fetch_assoc()['count'];

        try {
            $result = saveResourceInformationAndRights($this->connection, $postData);

            // Überprüfen, ob false zurückgegeben wurde
            $this->assertFalse($result, "Die Methode sollte false zurückgeben, wenn Pflichtfelder leer sind");

            // Zählen der Datensätze nach dem Test
            $countAfter = $this->connection->query("SELECT COUNT(*) as count FROM Resource")->fetch_assoc()['count'];

            // Überprüfen, ob kein neuer Datensatz angelegt wurde
            $this->assertEquals($countBefore, $countAfter, "Es sollte kein neuer Datensatz angelegt worden sein");

            // Überprüfen, ob kein neuer Titel angelegt wurde
            $titleCount = $this->connection->query("SELECT COUNT(*) as count FROM Title")->fetch_assoc()['count'];
            $this->assertEquals(0, $titleCount, "Es sollte kein neuer Titel angelegt worden sein");
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), "Column") !== false && strpos($e->getMessage(), "cannot be null") !== false) {
                $this->fail("Die Funktion saveResourceInformationAndRights() versucht einen unvollständigen Datensatz in der Datenbank zu speichern!");
            } else {
                throw $e; // Andere SQL-Ausnahmen werfen
            }
        }
    }
    public function testPreventDuplicateResourceSave()
    {
        if (!function_exists('saveResourceInformationAndRights')) {
            require_once __DIR__ . '/../save/formgroups/save_resourceinformation_and_rights.php';
        }

        $postData = [
            "doi" => "10.5880/GFZ.DUPLICATE.TEST",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "dateEmbargo" => "2024-12-31",
            "resourcetype" => 1,
            "version" => 1.0,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Duplicate Test Dataset"],
            "titleType" => [1]
        ];

        // Ersten Datensatz speichern
        $first_resource_id = saveResourceInformationAndRights($this->connection, $postData);
        $this->assertIsInt($first_resource_id);
        $this->assertGreaterThan(0, $first_resource_id);

        // Versuchen, denselben Datensatz erneut zu speichern
        $second_resource_id = saveResourceInformationAndRights($this->connection, $postData);

        // Überprüfen, ob false zurückgegeben wurde (oder eine andere Indikation für Duplikat)
        $this->assertFalse($second_resource_id, "Die Funktion sollte false zurückgeben, wenn versucht wird, eine doppelte DOI zu speichern");

        // Überprüfen, ob nur ein Datensatz in der Datenbank existiert
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Resource WHERE doi = ?");
        $stmt->bind_param("s", $postData["doi"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];

        $this->assertEquals(1, $count, "Es sollte nur ein Datensatz mit dieser DOI in der Datenbank existieren");
    }
    public function testHandleDuplicateTitles()
    {
        if (!function_exists('saveResourceInformationAndRights')) {
            require_once __DIR__ . '/../save/formgroups/save_resourceinformation_and_rights.php';
        }

        $postData = [
            "doi" => "10.5880/GFZ.DUPLICATE.TITLE.TEST",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "dateEmbargo" => "2024-12-31",
            "resourcetype" => 1,
            "version" => 1.0,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Duplicate Title", "Duplicate Title", "Unique Title"],
            "titleType" => [1, 1, 2]
        ];

        $resource_id = saveResourceInformationAndRights($this->connection, $postData);
        $this->assertIsInt($resource_id);
        $this->assertGreaterThan(0, $resource_id);

        // Überprüfen, ob nur zwei Titel gespeichert wurden (ein Duplikat entfernt)
        $stmt = $this->connection->prepare("SELECT * FROM Title WHERE Resource_resource_id = ? ORDER BY Title_Type_fk");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(2, $result->num_rows, "Es sollten genau zwei Titel gespeichert worden sein");

        $titles = [];
        while ($row = $result->fetch_assoc()) {
            $titles[] = $row;
        }

        $this->assertEquals("Duplicate Title", $titles[0]['text']);
        $this->assertEquals(1, $titles[0]['Title_Type_fk']);
        $this->assertEquals("Unique Title", $titles[1]['text']);
        $this->assertEquals(2, $titles[1]['Title_Type_fk']);
    }
}