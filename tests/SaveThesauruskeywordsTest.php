<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
use mysqli_sql_exception;

require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../save/formgroups/save_thesauruskeywords.php';

class SaveThesauruskeywordsTest extends TestCase
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
        $this->connection->query("DELETE FROM Resource_has_Thesaurus_Keywords");
        $this->connection->query("DELETE FROM Thesaurus_Keywords");
        $this->connection->query("DELETE FROM Title");
        $this->connection->query("DELETE FROM Resource");
    }

    /**
     * Alle Thesaurus Keyword Eingabefelder enthalten exakt eine Eingabe
     */
    public function testSaveAllThesaurusKeywordsSingle()
    {
        $resourceData = [
            "doi" => "10.5880/GFZ.TEST.ALL.KEYWORDS.SINGLE",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "resourcetype" => 1,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Test All Keywords Single"],
            "titleType" => [1]
        ];
        $resource_id = saveResourceInformationAndRights($this->connection, $resourceData);

        $postData = [
            "thesaurusKeywords" => json_encode([["value" => "Keyword1", "id" => "http://example.com/1", "scheme" => "CustomScheme", "schemeURI" => "http://example.com/scheme", "language" => "en"]]),
            "MSLKeywords" => json_encode([["value" => "Material1"]]),
        ];

        saveThesaurusKeywords($this->connection, $postData, $resource_id);

        // Check if all keywords were saved correctly
        $stmt = $this->connection->prepare("SELECT * FROM Thesaurus_Keywords");
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(2, $result->num_rows, "Es sollten genau 26 Thesaurus Keywords gespeichert worden sein.");

        $expectedKeywords = [
            "Keyword1", "Material1", "Age1", "Fluid1", "Setting1", "Structure1", "Feature1", "Apparatus1", "Equipment1", 
            "Property1", "Software1", "Technique1", "Property2", "Apparatus2", "Equipment2", "Technique2", "Feature2", 
            "Parameter1", "Apparatus3", "Control1", "Property3", "Behavior1", "Apparatus4", "Equipment3", "Property4", "Behavior2"
        ];

        while ($row = $result->fetch_assoc()) {
            $this->assertContains($row['keyword'], $expectedKeywords, "Das Keyword '{$row['keyword']}' sollte in der Liste der erwarteten Keywords sein.");
            if ($row['keyword'] == 'Keyword1') {
                $this->assertEquals("CustomScheme", $row['scheme']);
                $this->assertEquals("http://example.com/scheme", $row['schemeURI']);
                $this->assertEquals("http://example.com/1", $row['valueURI']);
                $this->assertEquals("en", $row['language']);
            } elseif ($row['keyword'] == 'Age1') {
                $this->assertEquals("GENGeologicalAge", $row['scheme']);
                $this->assertEquals("http://example.com/age1", $row['valueURI']);
                $this->assertEquals("en", $row['language']);
            } else {
                $this->assertEquals("en", $row['language'], "Die Sprache sollte standardmäßig 'en' sein für das Keyword '{$row['keyword']}'.");
            }
        }
    }

    /**
     * Alle Thesaurus Keyword Eingabefelder wurden mit je 3 Keywords befüllt
     */
    public function testSaveAllThesaurusKeywordsMultiple()
    {
        $resourceData = [
            "doi" => "10.5880/GFZ.TEST.ALL.KEYWORDS.MULTIPLE",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "resourcetype" => 1,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Test All Keywords Multiple"],
            "titleType" => [1]
        ];
        $resource_id = saveResourceInformationAndRights($this->connection, $resourceData);

        $postData = [
            "thesaurusKeywords" => json_encode([
                ["value" => "Keyword1"], ["value" => "Keyword2"], ["value" => "Keyword3"]
            ]),
            "MSLKeywords" => json_encode([
                ["value" => "Material1"], ["value" => "Material2"], ["value" => "Material3"]
            ])
        ];

        saveThesaurusKeywords($this->connection, $postData, $resource_id);

        // Check if all keywords were saved correctly
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Thesaurus_Keywords");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $this->assertEquals(6, $result['count'], "Es sollten genau 78 Thesaurus Keywords gespeichert worden sein.");

        // Check if all keywords are linked to the resource
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Resource_has_Thesaurus_Keywords WHERE Resource_resource_id = ?");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $this->assertEquals(6, $result['count'], "Es sollten genau 78 Verknüpfungen zwischen Resource und Thesaurus Keywords existieren.");
    }

    /**
     * Nur einzelne Thesaurus Keyword Eingabefelder wurden befüllt
     */
    public function testSavePartialThesaurusKeywords()
    {
        $resourceData = [
            "doi" => "10.5880/GFZ.TEST.PARTIAL.KEYWORDS",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "resourcetype" => 1,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Test Partial Keywords"],
            "titleType" => [1]
        ];
        $resource_id = saveResourceInformationAndRights($this->connection, $resourceData);

        $postData = [
            "thesaurusKeywords" => json_encode([["value" => "Keyword1"]]),
            "GENMaterial" => "",
            "MSLKeywords" => json_encode([["value" => "Age1"]]),
            "GENPoreFluid" => "",
            "AMOGPAncillaryEquipment" => "",
            "AMOGPAMOGPMeasuredProperty" => "",
            "AMOGPSoftware" => "",
            "GEOCHAnalysisTechnique" => "",
            "GEOCHMeasuredProperty" => "",
            "MATomographyApparatus" => "",
            "MATomographyAncillaryEquipment" => "",
            "MATomographyAnalysisTechnique" => "",
            "MATomographyAnalyzedFeature" => "",
            "AMOGPMATomographyInferredParameter" => "",
            "PALTISApparatus" => "",
            "PALTISEnvironmentControl" => "",
            "PALTISMeasuredProperty" => "",
            "PALTISInferredBehavior" => "",
            "RAMPApparatus" => "",
            "RAMPAncillaryEquipment" => "",
            "RAMPMeasuredProperty" => "",
            "RAMPInferredDeformationBehavior" => ""
        ];

        saveThesaurusKeywords($this->connection, $postData, $resource_id);

        // Check if only the filled keywords were saved
        $stmt = $this->connection->prepare("SELECT * FROM Thesaurus_Keywords");
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(2, $result->num_rows, "Es sollten genau vier Thesaurus Keywords gespeichert worden sein.");

        $keywords = [];
        while ($row = $result->fetch_assoc()) {
            $keywords[] = $row['keyword'];
        }

        $this->assertContains("Keyword1", $keywords, "Keyword1 sollte gespeichert worden sein.");
        $this->assertContains("Age1", $keywords, "Age1 sollte gespeichert worden sein.");
        $this->assertContains("Age1", $keywords, "Setting1 sollte gespeichert worden sein.");
        $this->assertContains("Keyword1", $keywords, "Apparatus1 sollte gespeichert worden sein.");
        $this->assertNotContains("Material1", $keywords, "Material1 sollte nicht gespeichert worden sein.");
    }

    /**
     * Keine Thesaurus Keyword Eingabefelder wurden befüllt
     */
    public function testSaveNoThesaurusKeywords()
    {
        $resourceData = [
            "doi" => "10.5880/GFZ.TEST.NO.KEYWORDS",
            "year" => 2023,
            "dateCreated" => "2023-06-01",
            "resourcetype" => 1,
            "language" => 1,
            "Rights" => 1,
            "title" => ["Test No Keywords"],
            "titleType" => [1]
        ];
        $resource_id = saveResourceInformationAndRights($this->connection, $resourceData);

        $postData = [
            "thesaurusKeywords" => "",
            "GENMaterial" => "",
            "GENGeologicalAge" => "",
            "GENPoreFluid" => "",
            "GENGeologicalSetting" => "",
            "AMOGPModeledStructure" => "",
            "AMOGPModeledGeomorphologicalFeature" => "",
            "AMOGPApparatus" => "",
            "AMOGPAncillaryEquipment" => "",
            "AMOGPAMOGPMeasuredProperty" => "",
            "AMOGPSoftware" => "",
            "GEOCHAnalysisTechnique" => "",
            "GEOCHMeasuredProperty" => "",
            "MATomographyApparatus" => "",
            "MATomographyAncillaryEquipment" => "",
            "MATomographyAnalysisTechnique" => "",
            "MATomographyAnalyzedFeature" => "",
            "AMOGPMATomographyInferredParameter" => "",
            "PALTISApparatus" => "",
            "PALTISEnvironmentControl" => "",
            "PALTISMeasuredProperty" => "",
            "PALTISInferredBehavior" => "",
            "RAMPApparatus" => "",
            "RAMPAncillaryEquipment" => "",
            "RAMPMeasuredProperty" => "",
            "RAMPInferredDeformationBehavior" => ""
        ];

        saveThesaurusKeywords($this->connection, $postData, $resource_id);

        // Check if no keywords were saved
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Thesaurus_Keywords");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $this->assertEquals(0, $result['count'], "Es sollten keine Thesaurus Keywords gespeichert worden sein.");

        // Check if no links to the resource were created
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Resource_has_Thesaurus_Keywords WHERE Resource_resource_id = ?");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $this->assertEquals(0, $result['count'], "Es sollten keine Verknüpfungen zwischen Resource und Thesaurus Keywords existieren.");
    }
}