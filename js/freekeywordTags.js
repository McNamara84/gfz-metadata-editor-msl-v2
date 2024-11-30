document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('input-freekeyword');

    // Tagify-Instanz erstellen
    var tagify = new Tagify(input, {
        whitelist: [], 
        dropdown: {
            maxItems: 50,
            closeOnSelect: true,
            highlightFirst: false,
            hideOnEmpty: true,
            enabled: 3,
        }
    });

    function loadKeywordsFromAPI() {
        var apiUrl = 'api.php?action=getKeywords&curationType=isCurated'; // Relativer Pfad zur aktuellen Datei

        $.ajax({
            url: apiUrl,
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                try {
                    if (!Array.isArray(data)) {
                        console.error('Die API hat ein unerwartetes Format zurückgegeben.');
                        return;
                    }

                    // Direktes Zuweisen der Keywords zur Tagify-Whitelist
                    var jsonWhitelist = data.map(keyword => keyword.free_keyword);

                    tagify.settings.whitelist = jsonWhitelist;

                } catch (error) {
                    console.error('Fehler beim Verarbeiten der JSON-Daten:', error);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Fehler beim Abrufen der Keywords:', textStatus, errorThrown);
                console.log('Response Text:', jqXHR.responseText);
                console.log('Status Code:', jqXHR.status);
                console.log('Status Text:', jqXHR.statusText);
            }
        });
    }

    loadKeywordsFromAPI();
});