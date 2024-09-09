$(document).ready(function () {
    var keywordConfigurations = [
        {
            inputId: '#inputScienceKeywords',
            jsonFile: 'json/gcmdScienceKeywords.json',
            jsTreeId: '#jstreeScience',
            searchInputId: '#searchInputScience'
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
    ];

    function initializeKeywordInput(config) {
        var input = $(config.inputId)[0];
        var suggestedKeywords = [];

        function loadKeywords(data) {
            //Keywords, die "NOT APLLICABLE" heißen herausfiltern
            function filterNotApplicable(nodes) {
                return nodes.filter(function (node) {
                    return node.text !== "NOT APPLICABLE";
                });
            }

            var filteredData = filterNotApplicable(data);
            //Knoten vorbereiten und mit Daten anreichern
            function processNodes(nodes) {
                return nodes.map(function (node) {
                    if (node.children) {
                        node.children = processNodes(node.children);
                    }
                    //macht das Anzeigen der Keyword-Description bei drüberhovern möglich:
                    node.a_attr = {
                        title: node.description || node.text
                    };
                    //speichert die Werte für scheme, schemeURI, language
                    node.original = {
                        scheme: node.scheme || "",
                        schemeURI: node.schemeURI || "",
                        language: node.language || ""
                    };
                    return node;
                });
            }
            var processedData = processNodes(filteredData);

            //Vorschlagsliste bauen, die im dropdown angezeigt wird
            function buildWhitelist(data, parentPath = []) {
                data.forEach(function (item) {
                    //Pfade anzeigen
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
            buildWhitelist(data);

            // Tagify initialisieren
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

            // jsTree initialisieren
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
                //bei Suche werden auch die Descriptions (node.a_attr.title) durchsucht
                search: {
                    show_only_matches: true,
                    search_callback: function (str, node) {
                        return node.text.toLowerCase().indexOf(str.toLowerCase()) !== -1 ||
                            (node.a_attr && node.a_attr.title && node.a_attr.title.toLowerCase().indexOf(str.toLowerCase()) !== -1);
                    }
                }
            });

            // Suche wird ausgeführt bei Eingabe ins Suchfeld
            $(config.searchInputId).on("input", function () {
                $(config.jsTreeId).jstree(true).search($(this).val());
            });

            //Wenn ein Knoten in jsTree ausgewählt wird, füge Tag hinzu
            $(config.jsTreeId).on("select_node.jstree", function (e, data) {
                var fullPath = data.instance.get_path(data.node, " > ");
                var existingTags = tagify.value.map((tag) => tag.value);

                if (!existingTags.includes(fullPath)) {
                    tagify.addTags([fullPath]);
                }
            });

            // //Wenn ein Knoten in jsTree abgewählt wird, entferne Tag
            $(config.jsTreeId).on("deselect_node.jstree", function (e, data) {
                var fullPath = data.instance.get_path(data.node, " > ");
                tagify.removeTag(fullPath);
            });

            // Wenn ein Tag hinzugefügt wird, wähle entsprechenden Knoten in jsTree aus
            tagify.on('add', function (e) {
                var tagText = e.detail.data.value;
                var jsTree = $(config.jsTreeId).jstree(true);
                var node = findNodeByPath(jsTree, tagText);
                if (node) {
                    jsTree.select_node(node.id);
                }
            });

            //wenn Tag entfernt wird, wird auch der Knoten abgewählt
            tagify.on('remove', function (e) {
                var tagText = e.detail.data.value;
                var jsTree = $(config.jsTreeId).jstree(true);
                var node = findNodeByPath(jsTree, tagText);
                if (node) {
                    jsTree.deselect_node(node.id);
                }
            });

            function findNodeByPath(jsTree, path) {
                return jsTree.get_json("#", { flat: true }).find(function (n) {
                    return jsTree.get_path(n, " > ") === path;
                });
            }
        }

        $.getJSON(config.jsonFile)
            .done(loadKeywords)
            .fail(function () {
                alert(`${config.jsonFile} does not exist! Please run the appropriate API and consider setting up a CronJob for it.`);
            });
    }

    keywordConfigurations.forEach(initializeKeywordInput);
});