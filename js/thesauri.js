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
        // MSL-Keywords
        {
            inputId: '#inputMSLKeywords',
            jsonFile: 'json/msl-vocabularies.json',
            jsTreeId: '#jstreeMSLKeywords',
            searchInputId: '#searchInputMSLKeywords',
        },
    ];

    // Funktion zum Initialisieren der Keyword-Eingabefelder
    function initializeKeywordInput(config) {
        var input = $(config.inputId)[0];
        var suggestedKeywords = [];

        // Funktion zum Laden und Verarbeiten der Keywords aus der JSON-Datei
        function loadKeywords(data) {
            var filteredData = [];

            // Prüfen, ob die Datee Arrays enthält oder Objekt und darunter Arrays enthält 
            if (Array.isArray(data)) {
                // Für Arrays nur
                filteredData = processNodes(data); 
            } else {
                // Für Objekte 
                for (var rootKey in data) {
                    if (data.hasOwnProperty(rootKey)) {
                        var rootChildren = data[rootKey];

                        var rootNode = {
                            text: rootKey,
                            children: rootChildren.map(function (child) {
                                return processNode(child);
                            })
                        };

                        filteredData.push(rootNode);
                    }
                }
            }

            var processedData = processNodes(filteredData);

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

    function processNode(node) {
        if (node.children) {
            node.children = node.children.map(function (child) {
                return processNode(child);
            });
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
    }

    function processNodes(nodes) {
        return nodes.map(function (node) {
            return processNode(node);
        });
    }

    function processNode(node) {
        // Überprüfen, ob neue oder alte Struktur vorliegt, und Attribute entsprechend zuordnen
        var id = node.id || node.uri || ""; 
        var text = node.text || node.label || node.value || "No text";  
        var schemeURI = node.schemeURI || node.vocab_uri || "";  
        var language = node.language || "";  
    
        // Bearbeite die Kindknoten
        if (node.children && Array.isArray(node.children)) {
            node.children = node.children.map(function (child) {
                return processNode(child);
            });
        } else {
            node.children = [];
        }
    
        // Erstelle das 'a_attr' Attribut und das 'original' Attribut für das Knotenobjekt
        node.a_attr = {
            title: node.description || node.text || "No description"
        };
        node.original = {
            scheme: node.scheme || "",
            schemeURI: schemeURI, 
            language: language 
        };
    
        // Rückgabe des angepassten Knotens mit den neuen Attributen
        return {
            id: id,
            text: text, 
            children: node.children, 
            a_attr: node.a_attr, 
            original: node.original
        };
    }
    
});