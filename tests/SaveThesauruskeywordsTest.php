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
            "GENMaterial" => json_encode([["value" => "Material1"]]),
            "GENGeologicalAge" => json_encode([["value" => "Age1", "id" => "http://example.com/age1"]]),
            "GENPoreFluid" => json_encode([["value" => "Fluid1"]]),
            "GENGeologicalSetting" => json_encode([["value" => "Setting1"]]),
            "AMOGPModeledStructure" => json_encode([["value" => "Structure1"]]),
            "AMOGPModeledGeomorphologicalFeature" => json_encode([["value" => "Feature1"]]),
            "AMOGPApparatus" => json_encode([["value" => "Apparatus1"]]),
            "AMOGPAncillaryEquipment" => json_encode([["value" => "Equipment1"]]),
            "AMOGPAMOGPMeasuredProperty" => json_encode([["value" => "Property1"]]),
            "AMOGPSoftware" => json_encode([["value" => "Software1"]]),
            "GEOCHAnalysisTechnique" => json_encode([["value" => "Technique1"]]),
            "GEOCHMeasuredProperty" => json_encode([["value" => "Property2"]]),
            "MATomographyApparatus" => json_encode([["value" => "Apparatus2"]]),
            "MATomographyAncillaryEquipment" => json_encode([["value" => "Equipment2"]]),
            "MATomographyAnalysisTechnique" => json_encode([["value" => "Technique2"]]),
            "MATomographyAnalyzedFeature" => json_encode([["value" => "Feature2"]]),
            "AMOGPMATomographyInferredParameter" => json_encode([["value" => "Parameter1"]]),
            "PALTISApparatus" => json_encode([["value" => "Apparatus3"]]),
            "PALTISEnvironmentControl" => json_encode([["value" => "Control1"]]),
            "PALTISMeasuredProperty" => json_encode([["value" => "Property3"]]),
            "PALTISInferredBehavior" => json_encode([["value" => "Behavior1"]]),
            "RAMPApparatus" => json_encode([["value" => "Apparatus4"]]),
            "RAMPAncillaryEquipment" => json_encode([["value" => "Equipment3"]]),
            "RAMPMeasuredProperty" => json_encode([["value" => "Property4"]]),
            "RAMPInferredDeformationBehavior" => json_encode([["value" => "Behavior2"]])
        ];

        saveThesaurusKeywords($this->connection, $postData, $resource_id);

        // Check if all keywords were saved correctly
        $stmt = $this->connection->prepare("SELECT * FROM Thesaurus_Keywords");
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(26, $result->num_rows, "Es sollten genau 26 Thesaurus Keywords gespeichert worden sein.");

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
            "GENMaterial" => json_encode([
                ["value" => "Material1"], ["value" => "Material2"], ["value" => "Material3"]
            ]),
            "GENGeologicalAge" => json_encode([
                ["value" => "Age1"], ["value" => "Age2"], ["value" => "Age3"]
            ]),
            "GENPoreFluid" => json_encode([
                ["value" => "Fluid1"], ["value" => "Fluid2"], ["value" => "Fluid3"]
            ]),
            "GENGeologicalSetting" => json_encode([
                ["value" => "Setting1"], ["value" => "Setting2"], ["value" => "Setting3"]
            ]),
            "AMOGPModeledStructure" => json_encode([
                ["value" => "Structure1"], ["value" => "Structure2"], ["value" => "Structure3"]
            ]),
            "AMOGPModeledGeomorphologicalFeature" => json_encode([
                ["value" => "Feature1"], ["value" => "Feature2"], ["value" => "Feature3"]
            ]),
            "AMOGPApparatus" => json_encode([
                ["value" => "Apparatus1"], ["value" => "Apparatus2"], ["value" => "Apparatus3"]
            ]),
            "AMOGPAncillaryEquipment" => json_encode([
                ["value" => "Equipment1"], ["value" => "Equipment2"], ["value" => "Equipment3"]
            ]),
            "AMOGPAMOGPMeasuredProperty" => json_encode([
                ["value" => "Property1"], ["value" => "Property2"], ["value" => "Property3"]
            ]),
            "AMOGPSoftware" => json_encode([
                ["value" => "Software1"], ["value" => "Software2"], ["value" => "Software3"]
            ]),
            "GEOCHAnalysisTechnique" => json_encode([
                ["value" => "Technique1"], ["value" => "Technique2"], ["value" => "Technique3"]
            ]),
            "GEOCHMeasuredProperty" => json_encode([
                ["value" => "Property4"], ["value" => "Property5"], ["value" => "Property6"]
            ]),
            "MATomographyApparatus" => json_encode([
                ["value" => "Apparatus4"], ["value" => "Apparatus5"], ["value" => "Apparatus6"]
            ]),
            "MATomographyAncillaryEquipment" => json_encode([
                ["value" => "Equipment4"], ["value" => "Equipment5"], ["value" => "Equipment6"]
            ]),
            "MATomographyAnalysisTechnique" => json_encode([
                ["value" => "Technique4"], ["value" => "Technique5"], ["value" => "Technique6"]
            ]),
            "MATomographyAnalyzedFeature" => json_encode([
                ["value" => "Feature4"], ["value" => "Feature5"], ["value" => "Feature6"]
            ]),
            "AMOGPMATomographyInferredParameter" => json_encode([
                ["value" => "Parameter1"], ["value" => "Parameter2"], ["value" => "Parameter3"]
            ]),
            "PALTISApparatus" => json_encode([
                ["value" => "Apparatus7"], ["value" => "Apparatus8"], ["value" => "Apparatus9"]
            ]),
            "PALTISEnvironmentControl" => json_encode([
                ["value" => "Control1"], ["value" => "Control2"], ["value" => "Control3"]
            ]),
            "PALTISMeasuredProperty" => json_encode([
                ["value" => "Property7"], ["value" => "Property8"], ["value" => "Property9"]
            ]),
            "PALTISInferredBehavior" => json_encode([
                ["value" => "Behavior1"], ["value" => "Behavior2"], ["value" => "Behavior3"]
            ]),
            "RAMPApparatus" => json_encode([
                ["value" => "Apparatus10"], ["value" => "Apparatus11"], ["value" => "Apparatus12"]
            ]),
            "RAMPAncillaryEquipment" => json_encode([
                ["value" => "Equipment7"], ["value" => "Equipment8"], ["value" => "Equipment9"]
            ]),
            "RAMPMeasuredProperty" => json_encode([
                ["value" => "Property10"], ["value" => "Property11"], ["value" => "Property12"]
            ]),
            "RAMPInferredDeformationBehavior" => json_encode([
                ["value" => "Behavior4"], ["value" => "Behavior5"], ["value" => "Behavior6"]
            ])
        ];

        saveThesaurusKeywords($this->connection, $postData, $resource_id);

        // Check if all keywords were saved correctly
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Thesaurus_Keywords");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $this->assertEquals(78, $result['count'], "Es sollten genau 78 Thesaurus Keywords gespeichert worden sein.");

        // Check if all keywords are linked to the resource
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Resource_has_Thesaurus_Keywords WHERE Resource_resource_id = ?");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $this->assertEquals(78, $result['count'], "Es sollten genau 78 Verknüpfungen zwischen Resource und Thesaurus Keywords existieren.");
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
            "GENGeologicalAge" => json_encode([["value" => "Age1"]]),
            "GENPoreFluid" => "",
            "GENGeologicalSetting" => json_encode([["value" => "Setting1"]]),
            "AMOGPModeledStructure" => "",
            "AMOGPModeledGeomorphologicalFeature" => "",
            "AMOGPApparatus" => json_encode([["value" => "Apparatus1"]]),
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

        $this->assertEquals(4, $result->num_rows, "Es sollten genau vier Thesaurus Keywords gespeichert worden sein.");

        $keywords = [];
        while ($row = $result->fetch_assoc()) {
            $keywords[] = $row['keyword'];
        }

        $this->assertContains("Keyword1", $keywords, "Keyword1 sollte gespeichert worden sein.");
        $this->assertContains("Age1", $keywords, "Age1 sollte gespeichert worden sein.");
        $this->assertContains("Setting1", $keywords, "Setting1 sollte gespeichert worden sein.");
        $this->assertContains("Apparatus1", $keywords, "Apparatus1 sollte gespeichert worden sein.");
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