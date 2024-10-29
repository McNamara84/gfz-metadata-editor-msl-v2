$(document).ready(function () {
    // Konfigurationen für die verschiedenen Keyword-Eingabefelder
    var keywordConfigurations = [
        {
            inputId: '#inputScienceKeywords',  // ID des Eingabefelds
            jsonFile: 'json/gcmdScienceKeywords.json',  // Pfad zur JSON-Datei mit den Daten
            jsTreeId: '#jstreeScience',   // ID des jsTrees
            searchInputId: '#searchInputScience'  // ID des Suchfeldes für diesen Baum
        },
        // MSL-Keywords
        {
            inputId: '#inputMSLKeywords',
            jsonFile: 'json/msl-vocabularies.json',
            jsTreeId: '#jstreeMSLKeywords',
            searchInputId: '#searchInputMSLKeywords',
        },
    ];

    // Initialisiere das Keyword-Eingabefeld
    function initializeKeywordInput(config) {
        var input = $(config.inputId)[0];
        var suggestedKeywords = [];

        // Keywords aus der JSON-Datei laden und verarbeiten
        function loadKeywords(data) {
            var filteredData = data;

            // Spezifischen Knoten filtern, falls rootNodeId angegeben ist
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

                // Knoten und seine Kinder filtern
                var selectedNode = findNodeById(data, config.rootNodeId);
                if (selectedNode) {
                    filteredData = selectedNode.children || [];
                } else {
                    console.error(`Root node with ID ${config.rootNodeId} not found in ${config.jsonFile}`);
                    return;
                }
            }

            // Verarbeite Knoten für die Baumstruktur
            function processNodes(nodes) {
                return nodes.map(function (node) {
                    if (node.children) {
                        node.children = processNodes(node.children);
                    }
                    // Tooltip für jeden Knoten
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

            // Erstelle die Vorschlagsliste für Tagify
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

                    // Rekursive Verarbeitung von Unterknoten
                    if (item.children) {
                        buildWhitelist(item.children, parentPath.concat(item.text));
                    }
                });
            }

            buildWhitelist(filteredData);

            // Initialisiere Tagify für das Eingabefeld
            var tagify = new Tagify(input, {
                whitelist: suggestedKeywords,
                enforceWhitelist: true,
                dropdown: {
                    maxItems: 50,  // Maximale Anzahl der Vorschläge
                    enabled: 3,    // Dropdown nach 3 Zeichen anzeigen
                    closeOnSelect: true  // Dropdown nach Auswahl schließen
                },
                editTags: false,  // Keine Bearbeitung von Tags erlaubt
            });

            // Initialisiere jsTree mit den verarbeiteten Daten
            $(config.jsTreeId).jstree({
                core: {
                    data: processedData,
                    themes: {
                        icons: false  // Keine Icons anzeigen
                    }
                },
                checkbox: {
                    keep_selected_style: true  // Checkbox-Style beibehalten
                },
                plugins: ['search', 'checkbox'],  // Such- und Checkbox-Plugin aktivieren
                search: {
                    show_only_matches: true,  // Nur passende Knoten anzeigen
                    search_callback: function (str, node) {
                        // Suchkriterium für Text oder Tooltip
                        return node.text.toLowerCase().indexOf(str.toLowerCase()) !== -1 ||
                            (node.a_attr && node.a_attr.title && node.a_attr.title.toLowerCase().indexOf(str.toLowerCase()) !== -1);
                    }
                }
            });

            // Echtzeitsuche im Baum
            $(config.searchInputId).on("input", function () {
                $(config.jsTreeId).jstree(true).search($(this).val());
            });

            // Knoten auswählen und als Tag hinzufügen
            $(config.jsTreeId).on("select_node.jstree", function (e, data) {
                var fullPath = data.instance.get_path(data.node, " > ");
                var existingTags = tagify.value.map((tag) => tag.id);

                // Nur hinzufügen, wenn noch nicht vorhanden
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

        // Lade die JSON-Datei mit den Keywords
        $.getJSON(config.jsonFile, function (data) {
            loadKeywords(data);
        });
    }

    // Initialisiere alle Keyword-Eingabefelder
    keywordConfigurations.forEach(function (config) {
        initializeKeywordInput(config);
    });
});
