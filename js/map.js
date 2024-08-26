$(document).ready(function () {
  var map;
  var drawnOverlays = [];
  var rowCounter = $("#tscGroup [tsc-row]").length;

  // Event Listener für Map-Buttons
  $("#tscGroup").on("click", "[data-bs-target='#mapModal']", function () {
    var rowId = $(this).closest("[tsc-row]").attr("tsc-row-id");
    $("#mapModal").data("tsc-row-id", rowId);

    // Karte anpassen, wenn Modal geöffnet wird
    $("#mapModal").on("shown.bs.modal", function () {
      google.maps.event.trigger(map, "resize");
      fitMapBounds();
    });
  });

  // Event Listener für Button mit ID cancelCoords
  $("#cancelCoords").click(function () {
    var rowId = $("#mapModal").data("tsc-row-id");
    // Eingabefelder leeren
    $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLatitudeMax]`).val("");
    $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLongitudeMax]`).val("");
    $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLatitudeMin]`).val("");
    $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLongitudeMin]`).val("");

    // Gezeichnete Marker/Rechtecke löschen
    deleteDrawnOverlaysForRow(rowId);
  });

  // Event-Listener für Button mit ID sendCoords
  $("#sendCoords").click(function () {
    // Modal schließen
    $("#mapModal").modal("hide");
  });

  // Initialisierung der Karte
  async function initMap() {
    const { Map } = await google.maps.importLibrary("maps");
    const { DrawingManager } = await google.maps.importLibrary("drawing");

    map = new Map(document.getElementById("map"), {
      center: { lat: 52.37929540757325, lng: 13.065966655404743 },
      zoom: 2,
      mapTypeId: google.maps.MapTypeId.SATELLITE,
    });

    const drawingManager = new DrawingManager({
      drawingMode: google.maps.drawing.OverlayType.MARKER,
      drawingControl: true,
      drawingControlOptions: {
        position: google.maps.ControlPosition.TOP_CENTER,
        drawingModes: [google.maps.drawing.OverlayType.RECTANGLE, google.maps.drawing.OverlayType.MARKER],
      },
      rectangleOptions: {
        strokeColor: "#FF0000",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#FF0000",
        fillOpacity: 0.35,
      },
    });

    drawingManager.setMap(map);

    // Koordinaten des Rechtecks übergeben als Wert für Eingabefelder
    google.maps.event.addListener(drawingManager, "rectanglecomplete", function (rectangle) {
      var rowId = $("#mapModal").data("tsc-row-id");

      // Löschen aller vorhandenen Overlays für diese Zeile
      deleteDrawnOverlaysForRow(rowId);

      var bounds = rectangle.getBounds();
      var ne = bounds.getNorthEast();
      var sw = bounds.getSouthWest();
      $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLatitudeMax]`).val(ne.lat());
      $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLongitudeMax]`).val(ne.lng());
      $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLatitudeMin]`).val(sw.lat());
      $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLongitudeMin]`).val(sw.lng());

      // Label für Rechteck ergänzen (Workaround mit Marker, da Rechtecke keine Labels unterstützen)
      var label = new google.maps.Marker({
        position: rectangle.getBounds().getCenter(),
        label: rowId,
        map: map,
      });

      drawnOverlays.push({ rowId: rowId, overlay: rectangle });
      drawnOverlays.push({ rowId: rowId, overlay: label });
    });

    // Koordinaten des Markers übergeben als Wert für Eingabefeld mit ID tscLatitudeMin und tscLongitudeMin
    google.maps.event.addListener(drawingManager, "markercomplete", function (marker) {
      var rowId = $("#mapModal").data("tsc-row-id");

      // Löschen aller vorhandenen Marker/Rechtecke für diese Zeile
      deleteDrawnOverlaysForRow(rowId);

      var position = marker.getPosition();
      $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLatitudeMin]`).val(position.lat());
      $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLongitudeMin]`).val(position.lng());
      $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLatitudeMax]`).val("");
      $(`[tsc-row][tsc-row-id="${rowId}"] [id^=tscLongitudeMax]`).val("");

      marker.setLabel(rowId);

      drawnOverlays.push({ rowId: rowId, overlay: marker });
    });
  }

  // Event Listener für Änderungen in den Eingabefeldern
  $("#tscGroup").on("input", "[tsc-row] [id^=tscLatitude], [tsc-row] [id^=tscLongitude]", function () {
    var $row = $(this).closest("[tsc-row]");
    var currentRowId = $row.attr("tsc-row-id");

    var latMax = $row.find("[id^=tscLatitudeMax]").val();
    var lngMax = $row.find("[id^=tscLongitudeMax]").val();
    var latMin = $row.find("[id^=tscLatitudeMin]").val();
    var lngMin = $row.find("[id^=tscLongitudeMin]").val();

    updateMapOverlay(currentRowId, latMax, lngMax, latMin, lngMin);
  });

  // Funktion zum Aktualisieren der Zeilen-IDs und Marker/Rechteck-Labels
  function updateRowIdsAndLabels() {
    $("#tscGroup [tsc-row]").each(function (index) {
      var newRowId = (index + 1).toString();
      var oldRowId = $(this).attr("tsc-row-id");

      // Zeilen-IDs aktualisieren
      $(this).attr("tsc-row-id", newRowId);

      // IDs der Eingabefelder aktualisieren
      $(this)
        .find("input, select, textarea")
        .each(function () {
          var oldId = $(this).attr("id");
          var newId = oldId.replace(/_\d+$/, "_" + newRowId);
          $(this).attr("id", newId);
        });

      // Aktualisierund der Rechtecke/Marker
      drawnOverlays.forEach(function (item) {
        if (item.rowId === oldRowId) {
          item.rowId = newRowId;
          if (item.overlay instanceof google.maps.Marker) {
            item.overlay.setLabel(newRowId);
          } else if (item.overlay instanceof google.maps.Rectangle) {
            // Label-Overlays für Rwechtecke finden und aktualisieren
            var centerMarker = drawnOverlays.find((m) => m.rowId === newRowId && m.overlay instanceof google.maps.Marker);
            if (centerMarker) {
              centerMarker.overlay.setLabel(newRowId);
            }
          }
        }
      });
    });
  }

  function updateMapOverlay(currentRowId, latMax, lngMax, latMin, lngMin) {
    deleteDrawnOverlaysForRow(currentRowId);

    if (latMax && lngMax && latMin && lngMin) {
      var bounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(parseFloat(latMin), parseFloat(lngMin)),
        new google.maps.LatLng(parseFloat(latMax), parseFloat(lngMax))
      );
      var rectangle = new google.maps.Rectangle({
        bounds: bounds,
        strokeColor: "#FF0000",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#FF0000",
        fillOpacity: 0.35,
        map: map,
      });

      var label = new google.maps.Marker({
        position: bounds.getCenter(),
        label: currentRowId,
        map: map,
      });

      drawnOverlays.push({ rowId: currentRowId, overlay: rectangle });
      drawnOverlays.push({ rowId: currentRowId, overlay: label });
    } else if (latMin && lngMin) {
      var position = new google.maps.LatLng(parseFloat(latMin), parseFloat(lngMin));
      var marker = new google.maps.Marker({
        position: position,
        label: currentRowId,
        map: map,
      });

      drawnOverlays.push({ rowId: currentRowId, overlay: marker });
    }

    fitMapBounds();
  }

  function deleteDrawnOverlaysForRow(rowId) {
    drawnOverlays = drawnOverlays.filter((item) => {
      if (item.rowId === rowId) {
        item.overlay.setMap(null);
        return false;
      }
      return true;
    });
  }

  function deleteDrawnOverlays() {
    drawnOverlays.forEach((item) => item.overlay.setMap(null));
    drawnOverlays = [];
  }

  function fitMapBounds() {
    var bounds = new google.maps.LatLngBounds();
    drawnOverlays.forEach((item) => {
      if (item.overlay.getBounds) {
        bounds.union(item.overlay.getBounds());
      } else if (item.overlay.getPosition) {
        bounds.extend(item.overlay.getPosition());
      }
    });

    if (!bounds.isEmpty()) {
      // Zoom mit 50% Puffer
      var ne = bounds.getNorthEast();
      var sw = bounds.getSouthWest();
      var lat_buffer = (ne.lat() - sw.lat()) * 0.5;
      var lng_buffer = (ne.lng() - sw.lng()) * 0.5;
      bounds.extend(new google.maps.LatLng(ne.lat() + lat_buffer, ne.lng() + lng_buffer));
      bounds.extend(new google.maps.LatLng(sw.lat() - lat_buffer, sw.lng() - lng_buffer));
      map.fitBounds(bounds);
    }
  }

  // Funktion übernommen aus der Google Maps JavaScript API
  function loadGoogleMapsApi(apiKey) {
    ((g) => {
      var h,
        a,
        k,
        p = "The Google Maps JavaScript API",
        c = "google",
        l = "importLibrary",
        q = "__ib__",
        m = document,
        b = window;
      b = b[c] || (b[c] = {});
      var d = b.maps || (b.maps = {}),
        r = new Set(),
        e = new URLSearchParams(),
        u = () =>
          h ||
          (h = new Promise(async (f, n) => {
            await (a = m.createElement("script"));
            e.set("libraries", [...r] + "");
            for (k in g)
              e.set(
                k.replace(/[A-Z]/g, (t) => "_" + t[0].toLowerCase()),
                g[k]
              );
            e.set("callback", c + ".maps." + q);
            a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
            d[q] = f;
            a.onerror = () => (h = n(Error(p + " could not load.")));
            a.nonce = m.querySelector("script[nonce]")?.nonce || "";
            m.head.append(a);
          }));
      d[l] ? console.warn(p + " only loads once. Ignoring:", g) : (d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n)));
    })({
      key: apiKey,
      v: "weekly",
    });
  }

  // AJAX-Anfrage, um den API-Key zu holen
  fetch("settings.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error("Netzwerkantwort war nicht ok");
      }
      return response.json();
    })
    .then((data) => {
      if (data.apiKey) {
        loadGoogleMapsApi(data.apiKey);
        // Karte laden
        google.maps.importLibrary("maps").then(initMap);
      } else {
        console.error("API-Schlüssel nicht in der Antwort gefunden");
      }
    })
    .catch((error) => {
      console.error("Fehler beim Abrufen des API-Schlüssels:", error);
    });

  // Initialisieren der Buttons
  function initializeButtons() {
    var $rows = $("#tscGroup [tsc-row]");
    rowCounter = $rows.length;

    $rows.each(function (index) {
      var $row = $(this);
      var $deleteButton = $row.find("[data-action='delete-tsc-row']");
      var $addButton = $row.find("[data-action='add-tsc-row']");

      // Logik für Delete-Buttons
      if (index === rowCounter - 1 || rowCounter === 1) {
        // Verstecke den Delete-Button in der letzten Zeile oder wenn es nur eine Zeile gibt
        $deleteButton.addClass("d-none");
      } else {
        $deleteButton.removeClass("d-none");
      }

      // Logik für Add-Buttons
      if (index === rowCounter - 1) {
        // Nur der letzte Add-Button ist sichtbar
        $addButton.removeClass("d-none");
      } else {
        $addButton.addClass("d-none");
      }
    });
  }

  // Hinzufügen einer neuen Zeile
  function addNewRow() {
    rowCounter++;
    var newRowId = rowCounter.toString();
    var $lastRow = $("#tscGroup [tsc-row]").last();
    var $newRow = $lastRow.clone();

    $newRow.attr("tsc-row-id", newRowId);
    $newRow
      .find("input, select, textarea")
      .val("")
      .each(function () {
        var oldId = $(this).attr("id");
        var newId = oldId.split("_")[0] + "_" + newRowId;
        $(this).attr("id", newId);
      });

    $lastRow.after($newRow);

    initializeButtons();
    updateMapOverlay(newRowId, "", "", "", "");
  }

  // Löschen einer Zeile
  function deleteRow(button) {
    var $row = $(button).closest("[tsc-row]");
    var rowId = $row.attr("tsc-row-id");

    deleteDrawnOverlaysForRow(rowId);
    $row.remove();

    updateRowIdsAndLabels(); // Aktualisiere die Nummerierung
    initializeButtons();
    fitMapBounds();
  }

  // Event Listener für den Add-Button
  $(document).on("click", "[data-action='add-tsc-row']", function (e) {
    e.preventDefault();
    addNewRow();
  });

  // Event Listener für den Delete-Button
  $(document).on("click", "[data-action='delete-tsc-row']", function (e) {
    e.preventDefault();
    if (!$(this).hasClass("d-none")) {
      deleteRow(this);
    }
  });

  // Entfernen Sie alle vorherigen Click-Event-Listener von den Buttons
  $(".tscAddButton, .tscDeleteButton").off("click");

  // Initialisiere die Buttons beim Laden der Seite
  initializeButtons();
});
