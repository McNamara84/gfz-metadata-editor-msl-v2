$(document).ready(function () {
    // Konfigurationen für die verschiedenen Keyword-Eingabefelder
    var keywordConfigurations = [
        {
            inputId: '#inputScienceKeywords',  // ID des Eingabefelds
            jsonFile: 'json/gcmdScienceKeywords.json',  // Pfad zur JSON-Datei mit den Daten
            jsTreeId: '#jstreeScience',   // ID des jsTrees
            searchInputId: '#searchInputScience'  // ID des Suchfeldes für diesen Baum
        },
        {
            inputId: '#inputInstruments',
            jsonFile: 'json/gcmdInstrumentsKeywords.json',
            jsTreeId: '#jstreeInstruments',
            searchInputId: '#searchInputInstruments'
        },
        {
            inputId: '#inputPlatforms',
            jsonFile: 'json/gcmdPlatformsKeywords.json',
            jsTreeId: '#jstreePlatforms',
            searchInputId: '#searchInputPlatforms'
        },
        {
            inputId: '#inputChrono',
            jsonFile: 'json/ChronostratKeywords.json',
            jsTreeId: '#jstreeChrono',
            searchInputId: '#searchInputChrono'
        },
        {
            inputId: '#inputCgi',
            jsonFile: 'json/cgi.json',
            jsTreeId: '#jstreeCgi',
            searchInputId: '#searchInputCgi'
        },
        {
            inputId: '#inputGemet',
            jsonFile: 'json/gemet.json',
            jsTreeId: '#jstreeGemet',
            searchInputId: '#searchInputGemet'
        },
        // General
        {
            inputId: '#inputGENMaterial',
            jsonFile: 'json/materials.json',
            jsTreeId: '#jstreeGENMaterial',
            searchInputId: '#searchInputGENMaterial'
        },
        {
            inputId: '#inputGENGeologicalAge',
            jsonFile: 'json/geologicalage.json',
            jsTreeId: '#jstreeGENGeologicalAge',
            searchInputId: '#searchInputGENGeologicalAge'
        },
        {
            inputId: '#inputGENPoreFluid',
            jsonFile: 'json/porefluids.json',
            jsTreeId: '#jstreeGENPoreFluide',
            searchInputId: '#searchInputGENPoreFluide'
        },
        {
            inputId: '#inputGENGeologicalSetting',
            jsonFile: 'json/geologicalsetting.json',
            jsTreeId: '#jstreeGENGeologicalSetting',
            searchInputId: '#searchInputGENGeologicalSetting'
        },
        // Analogue Modelling Of Geological Processes
        {
            inputId: '#inputAMOGPModeledStructure',
            jsonFile: 'json/porefluids.json',
            jsTreeId: '#jstreeAMOGPModeledStructure',
            searchInputId: '#searchInputAMOGPModeledStructure',

        },
        {
            inputId: '#inputAMOGPModeledGeomorphologicalFeature',
            jsonFile: 'json/geochemistry.json',
            jsTreeId: '#jstreeAMOGPModeledGeomorphologicalFeature',
            searchInputId: '#searchInputAMOGPModeledGeomorphologicalFeature',

        },
        {
            inputId: '#inputAMOGPApparatus',
            jsonFile: 'json/geochemistry.json',
            jsTreeId: '#jstreeAMOGPApparatus',
            searchInputId: '#searchInputAMOGPApparatus',

        },
        {
            inputId: '#inputAMOGPAncillaryEquipment',
            jsonFile: 'json/geochemistry.json',
            jsTreeId: '#jstreeAMOGPAncillaryEquipment',
            searchInputId: '#searchInputAMOGPAncillaryEquipment',

        },
        {
            inputId: '#inputAMOGPMeasuredProperty',
            jsonFile: 'json/geochemistry.json',
            jsTreeId: '#jstreeAMOGPMeasuredProperty',
            searchInputId: '#searchInputAMOGPMeasuredProperty',

        },
        {
            inputId: '#inputAMOGPSoftware',
            jsonFile: 'json/geochemistry.json',
            jsTreeId: '#jstreeAMOGPSoftware',
            searchInputId: '#searchInputAMOGPSoftware',

        },
        // Geochemistry
        {
            inputId: '#inputGEOCHAnalysisTechnique',
            jsonFile: 'json/geochemistry.json',
            jsTreeId: '#jstreeGEOCHAnalysisTechnique',
            searchInputId: '#searchInputGEOCHAnalysisTechnique',
            rootNodeId: 'https://epos-msl.uu.nl/voc/geochemistry/1.3/analysis'
        },
        {
            inputId: '#inputGEOCHMeasuredProperty',
            jsonFile: 'json/geochemistry.json',
            jsTreeId: '#jstreeGEOCHMeasuredProperty',
            searchInputId: '#searchInputGEOCHMeasuredProperty',
            rootNodeId: 'https:\/\/epos-msl.uu.nl\/voc\/geochemistry\/1.3\/measured_property'
        },
    ];

    // Funktion zum Initialisieren der Keyword-Eingabefelder
    function initializeKeywordInput(config) {
        var input = $(config.inputId)[0];
        var suggestedKeywords = [];

        // Funktion zum Laden und Verarbeiten der Keywords aus der JSON-Datei
        function loadKeywords(data) {
            var filteredData = data;

            // Wenn eine rootNodeId angegeben ist, wende die spezielle Filterung an
            if (config.rootNodeId) {
                function findNodeById(nodes, id) {
                    for (var i = 0; i < nodes.length; i++) {
                        if (nodes[i].id === id) {
                            return nodes[i];
                        }
                        if (nodes[i].children) {
                            var foundNode = findNodeById(nodes[i].children, id);
                            if (foundNode) {
                                return foundNode;
                            }
                        }
                    }
                    return null;
                }

                // Den gewünschten Knoten finden und nur seine Kinder verwenden
                var selectedNode = findNodeById(data, config.rootNodeId);
                if (selectedNode) {
                    filteredData = selectedNode.children || [];
                } else {
                    console.error(`Root node with ID ${config.rootNodeId} not found in ${config.jsonFile}`);
                    return;
                }
            }

            function processNodes(nodes) {
                return nodes.map(function (node) {
                    if (node.children) {
                        node.children = processNodes(node.children);
                    }
                    node.a_attr = {
                        title: node.description || node.text
                    };
                    node.original = {
                        scheme: node.scheme || "",
                        schemeURI: node.schemeURI || "",
                        language: node.language || ""
                    };
                    return node;
                });
            }

            var processedData = processNodes(filteredData);
            // Funktion zum Erstellen der Whitelist (Vorschläge) für Tagify
            function buildWhitelist(data, parentPath = []) {
                data.forEach(function (item) {
                    var textToAdd = parentPath.concat(item.text).join(' > ');
                    suggestedKeywords.push({
                        value: textToAdd,
                        id: item.id,
                        scheme: item.scheme,
                        schemeURI: item.schemeURI,
                        language: item.language
                    });

                    if (item.children) {
                        buildWhitelist(item.children, parentPath.concat(item.text));
                    }
                });
            }

            buildWhitelist(filteredData);

            var tagify = new Tagify(input, {
                whitelist: suggestedKeywords,
                enforceWhitelist: true,
                dropdown: {
                    maxItems: 50,
                    enabled: 3,
                    closeOnSelect: true
                },
                editTags: false,
            });

            $(config.jsTreeId).jstree({
                core: {
                    data: processedData,
                    themes: {
                        icons: false
                    }
                },
                checkbox: {
                    keep_selected_style: true
                },
                plugins: ['search', 'checkbox'],
                search: {
                    show_only_matches: true,
                    search_callback: function (str, node) {
                        return node.text.toLowerCase().indexOf(str.toLowerCase()) !== -1 ||
                            (node.a_attr && node.a_attr.title && node.a_attr.title.toLowerCase().indexOf(str.toLowerCase()) !== -1);
                    }
                }
            });

            $(config.searchInputId).on("input", function () {
                $(config.jsTreeId).jstree(true).search($(this).val());
            });

            $(config.jsTreeId).on("select_node.jstree", function (e, data) {
                var fullPath = data.instance.get_path(data.node, " > ");
                var existingTags = tagify.value.map((tag) => tag.id);

                if (!existingTags.includes(data.node.id)) {
                    tagify.addTags([{
                        value: fullPath,
                        id: data.node.id,
                        scheme: data.node.original.scheme,
                        schemeURI: data.node.original.schemeURI,
                        language: data.node.original.language
                    }]);
                }
            });
        }

        $.getJSON(config.jsonFile, function (data) {
            loadKeywords(data);
        });
    }

    keywordConfigurations.forEach(function (config) {
        initializeKeywordInput(config);
    });
});
