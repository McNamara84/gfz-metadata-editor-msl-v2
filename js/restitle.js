$(document).ready(function () {

    function resizeElements() {
        // Verkleinerung des Titels
        var title = document.getElementById("headtitle");

        // Greife auf alle Buttons im Header über den Header-Selektor und die Klasse "btn" zu
        var headerButtons = document.querySelectorAll("header .btn");

        // Für mobile Geräte
        if (window.innerWidth < 768) {
            // Verkleinerung des Titels
            title.style.fontSize = "16px";

            // Verkleinere nur die Buttons im Header
            headerButtons.forEach(function (button) {
                button.style.fontSize = "10px";
                button.style.padding = "6px 12px";
            });

        } else { // Für Desktop
            title.style.fontSize = "20px";

            // Setzung der Buttons im Header auf ihre Standardgröße zurück
            headerButtons.forEach(function (button) {
                button.style.fontSize = "16px";
                button.style.padding = "10px 20px";
            });
        }
    }

    // Aufruf beim Laden der Seite
    resizeElements();

    // Event-Listener für das Änderung der Fenstergröße
    window.addEventListener('resize', resizeElements);
});
