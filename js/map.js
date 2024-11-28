$(document).ready(function () {
  /** @type {google.maps.Map} */
  var map;
  /** @type {Array<Object>} */
  var drawnOverlays = [];

  /**
   * Event listener for Map buttons within the #tscGroup.
   * Stores the current row data when the map modal is opened and adjusts the map.
   */
  $("#tscGroup").on("click", "[data-bs-target='#mapModal']", function () {
    var $currentRow = $(this).closest("[tsc-row]");
    var rowId = $currentRow.attr("tsc-row-id");

    // Store current row reference and ID in the modal
    $("#mapModal")
      .data("current-row", $currentRow)
      .data("tsc-row-id", rowId);

    // Adjust the map when the modal is shown
    $("#mapModal").one("shown.bs.modal", function () {
      google.maps.event.trigger(map, "resize");
      fitMapBounds();
    });
  });

  /**
   * Event listener for the "Cancel Coordinates" button.
   * Clears coordinate inputs and removes any drawn overlays for the current row.
   */
  $("#cancelCoords").click(function () {
    var $currentRow = $("#mapModal").data("current-row");
    if ($currentRow && $currentRow.length) {
      $currentRow.find("[id^=tscLatitudeMax]").val("");
      $currentRow.find("[id^=tscLongitudeMax]").val("");
      $currentRow.find("[id^=tscLatitudeMin]").val("");
      $currentRow.find("[id^=tscLongitudeMin]").val("");

      var rowId = $currentRow.attr("tsc-row-id");
      deleteDrawnOverlaysForRow(rowId);
    }
  });

  /**
   * Event listener for the "Send Coordinates" button.
   * Hides the map modal.
   */
  $("#sendCoords").click(function () {
    $("#mapModal").modal("hide");
  });

  /**
   * Initializes the Google Map and Drawing Manager.
   * Sets up event listeners for drawing rectangles and markers on the map.
   */
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
        drawingModes: [
          google.maps.drawing.OverlayType.RECTANGLE,
          google.maps.drawing.OverlayType.MARKER,
        ],
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

    /**
     * Event listener for when a rectangle is completed.
     * Updates the coordinate inputs and draws overlays on the map.
     *
     * @param {google.maps.Rectangle} rectangle - The completed rectangle.
     */
    google.maps.event.addListener(
      drawingManager,
      "rectanglecomplete",
      function (rectangle) {
        var $currentRow = $("#mapModal").data("current-row");
        if (!$currentRow || !$currentRow.length) return;

        var rowId = $currentRow.attr("tsc-row-id");
        deleteDrawnOverlaysForRow(rowId);

        var bounds = rectangle.getBounds();
        var ne = bounds.getNorthEast();
        var sw = bounds.getSouthWest();

        $currentRow.find("[id^=tscLatitudeMax]").val(ne.lat());
        $currentRow.find("[id^=tscLongitudeMax]").val(ne.lng());
        $currentRow.find("[id^=tscLatitudeMin]").val(sw.lat());
        $currentRow.find("[id^=tscLongitudeMin]").val(sw.lng());

        // Get the display number based on the row's position
        var displayNumber = $currentRow.index() + 1;

        var label = new google.maps.Marker({
          position: bounds.getCenter(),
          label: displayNumber.toString(),
          map: map,
        });

        drawnOverlays.push({ rowId: rowId, overlay: rectangle, labelOverlay: label });
      }
    );

    /**
     * Event listener for when a marker is completed.
     * Updates the coordinate inputs and draws overlays on the map.
     *
     * @param {google.maps.Marker} marker - The completed marker.
     */
    google.maps.event.addListener(
      drawingManager,
      "markercomplete",
      function (marker) {
        var $currentRow = $("#mapModal").data("current-row");
        if (!$currentRow || !$currentRow.length) return;

        var rowId = $currentRow.attr("tsc-row-id");
        deleteDrawnOverlaysForRow(rowId);

        var position = marker.getPosition();
        $currentRow.find("[id^=tscLatitudeMin]").val(position.lat());
        $currentRow.find("[id^=tscLongitudeMin]").val(position.lng());
        $currentRow.find("[id^=tscLatitudeMax]").val("");
        $currentRow.find("[id^=tscLongitudeMax]").val("");

        // Get the display number based on the row's position
        var displayNumber = $currentRow.index() + 1;

        marker.setLabel(displayNumber.toString());
        drawnOverlays.push({ rowId: rowId, overlay: marker });
      }
    );
  }

  /**
   * Event listener for changes in the coordinate input fields.
   * Updates the map overlays based on the input values.
   */
  $("#tscGroup").on(
    "input",
    "[tsc-row] [id^=tscLatitude], [tsc-row] [id^=tscLongitude]",
    function () {
      var $row = $(this).closest("[tsc-row]");
      var currentRowId = $row.attr("tsc-row-id");

      var latMax = $row.find("[id^=tscLatitudeMax]").val();
      var lngMax = $row.find("[id^=tscLongitudeMax]").val();
      var latMin = $row.find("[id^=tscLatitudeMin]").val();
      var lngMin = $row.find("[id^=tscLongitudeMin]").val();

      updateMapOverlay(currentRowId, latMax, lngMax, latMin, lngMin);
    }
  );

  /**
   * Updates the labels on the overlays to match the current row numbering.
   */
  function updateOverlayLabels() {
    drawnOverlays.forEach(function (item) {
      var rowId = item.rowId;

      // Find the row with this tsc-row-id
      var $row = $("#tscGroup").find("[tsc-row-id='" + rowId + "']");

      if ($row.length > 0) {
        var displayNumber = $row.index() + 1; // Since index is zero-based

        // Update the label on the overlay
        if (item.overlay instanceof google.maps.Rectangle) {
          if (item.labelOverlay) {
            item.labelOverlay.setLabel(displayNumber.toString());
          }
        } else if (item.overlay instanceof google.maps.Marker) {
          item.overlay.setLabel(displayNumber.toString());
        }
      } else {
        // If the row no longer exists, remove the overlay
        item.overlay.setMap(null);
        if (item.labelOverlay) {
          item.labelOverlay.setMap(null);
        }
      }
    });

    // Remove any overlays that are no longer associated with existing rows
    drawnOverlays = drawnOverlays.filter(function (item) {
      var $row = $("#tscGroup").find("[tsc-row-id='" + item.rowId + "']");
      return $row.length > 0;
    });
  }

  /**
   * Updates the map overlays based on the provided coordinates.
   * Draws rectangles or markers on the map depending on the inputs.
   *
   * @param {string} currentRowId - The ID of the current row.
   * @param {string} latMax - The maximum latitude value.
   * @param {string} lngMax - The maximum longitude value.
   * @param {string} latMin - The minimum latitude value.
   * @param {string} lngMin - The minimum longitude value.
   */
  function updateMapOverlay(currentRowId, latMax, lngMax, latMin, lngMin) {
    deleteDrawnOverlaysForRow(currentRowId);

    var $row = $("#tscGroup").find("[tsc-row-id='" + currentRowId + "']");
    var displayNumber = $row.index() + 1;

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
        label: displayNumber.toString(),
        map: map,
      });

      drawnOverlays.push({ rowId: currentRowId, overlay: rectangle, labelOverlay: label });
    } else if (latMin && lngMin) {
      var position = new google.maps.LatLng(
        parseFloat(latMin),
        parseFloat(lngMin)
      );
      var marker = new google.maps.Marker({
        position: position,
        label: displayNumber.toString(),
        map: map,
      });

      drawnOverlays.push({ rowId: currentRowId, overlay: marker });
    }

    fitMapBounds();
  }

  /**
   * Deletes all drawn overlays (markers and rectangles) for a specific row ID.
   *
   * @param {string} rowId - The ID of the row whose overlays should be deleted.
   */
  function deleteDrawnOverlaysForRow(rowId) {
    drawnOverlays = drawnOverlays.filter((item) => {
      if (item.rowId === rowId) {
        item.overlay.setMap(null);
        if (item.labelOverlay) {
          item.labelOverlay.setMap(null);
        }
        return false;
      }
      return true;
    });
  }

  /**
   * Adjusts the map's viewport to fit all drawn overlays with a 50% buffer.
   */
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
      // Zoom with 50% buffer
      var ne = bounds.getNorthEast();
      var sw = bounds.getSouthWest();
      var lat_buffer = (ne.lat() - sw.lat()) * 0.5;
      var lng_buffer = (ne.lng() - sw.lng()) * 0.5;
      bounds.extend(
        new google.maps.LatLng(ne.lat() + lat_buffer, ne.lng() + lng_buffer)
      );
      bounds.extend(
        new google.maps.LatLng(sw.lat() - lat_buffer, sw.lng() - lng_buffer)
      );
      map.fitBounds(bounds);
    }
  }

  /**
   * Loads the Google Maps API dynamically using the provided API key.
   * This function is adapted from the Google Maps JavaScript API documentation.
   *
   * @param {string} apiKey - The API key for Google Maps.
   */
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
      d[l]
        ? console.warn(p + " only loads once. Ignoring:", g)
        : (d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n)));
    })({
      key: apiKey,
      v: "weekly",
    });
  }

  // Fetch the API key via AJAX request and initialize the map
  fetch("settings.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    .then((data) => {
      if (data.apiKey) {
        loadGoogleMapsApi(data.apiKey);
        // Load the map
        google.maps.importLibrary("maps").then(initMap);
      } else {
        console.error("API key not found in the response");
      }
    })
    .catch((error) => {
      console.error("Error fetching the API key:", error);
    });

  // Make functions globally accessible
  window.deleteDrawnOverlaysForRow = deleteDrawnOverlaysForRow;
  window.fitMapBounds = fitMapBounds;
  window.updateOverlayLabels = updateOverlayLabels;
});
