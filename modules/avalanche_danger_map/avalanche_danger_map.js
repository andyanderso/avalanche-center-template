(function ($) {
  Backdrop.behaviors.avalancheDangerMap = {
    attach: function (context, settings) {
      var mapSettings = settings.avalancheDangerMap;
      if (!mapSettings || !mapSettings.mapId) return;
      var mapId = mapSettings.mapId;
      var $mapEl = $('#' + mapId, context);
      if (!$mapEl.length || $mapEl.data('leaflet-map-initialized')) return;
      $mapEl.data('leaflet-map-initialized', true);

      var map = L.map(mapId, {
        // Zoom with the mouse wheel / trackpad gesture, not only the +/-
        // buttons. touchZoom (pinch) is on by default for mobile.
        scrollWheelZoom: true
      }).setView(mapSettings.center, mapSettings.zoom);

      // Base layers come from the server (avalanche_danger_map_leaflet_map_info),
      // so the layer list lives in one place (the PHP map definition). The first
      // layer is the default. The hardcoded set below is only a fallback.
      var baseLayers = {};
      var defaultLayer = null;
      if (mapSettings.layers && mapSettings.layers.length) {
        $.each(mapSettings.layers, function (i, def) {
          if (!def.urlTemplate) return;
          var layer = L.tileLayer(def.urlTemplate, def.options || {});
          baseLayers[def.name] = layer;
          if (i === 0) defaultLayer = layer;
        });
      }
      if (!defaultLayer) {
        defaultLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
          maxZoom: 19
        });
        baseLayers['Street Map'] = defaultLayer;
      }
      defaultLayer.addTo(map);
      L.control.layers(baseLayers, {}, { collapsed: true }).addTo(map);

      var bounds = [];
      $.each(mapSettings.regions, function (i, region) {
        if (!region.geojson) return;
        try {
          var geoData = typeof region.geojson === 'string' ? JSON.parse(region.geojson) : region.geojson;
          var layer = L.geoJSON(geoData, {
            style: {
              fillColor: region.color,
              fillOpacity: 0.65,
              color: '#333',
              weight: 2,
              opacity: 0.8
            }
          });

          var popupHtml = '<div class="danger-map-popup">' +
            '<h4>' + region.name + '</h4>' +
            '<div class="danger-map-popup-header">';

          if (region.icon_url) {
            popupHtml += '<img class="danger-map-popup-icon" src="' + region.icon_url + '" alt="">';
          }

          var textColor = region.text_color || '#000';
          popupHtml += '<div class="danger-map-popup-rating" style="background:' + region.color + ';color:' + textColor + ';">' +
              '<span class="danger-map-popup-rating-level">' + region.label.toUpperCase() + '</span>' +
              '<span class="danger-map-popup-rating-caption">' + Backdrop.t('Avalanche Danger') + '</span>' +
            '</div>' +
          '</div>';

          if (region.travel_advice) {
            popupHtml += '<div class="danger-map-popup-advice">' +
              '<em>' + Backdrop.t('Travel Advice:') + '</em><br/>' + region.travel_advice +
            '</div>';
          }

          // Level 0's travel advice already links to the same advisory_url
          // inline ("Get more information"), so skip the redundant link
          // below it.
          if (region.advisory_url && region.danger_rating !== 0) {
            var linkText = region.expired ? Backdrop.t('Get more information') : Backdrop.t('Read Full Forecast');
            popupHtml += '<div class="danger-map-popup-link">' +
              '<a href="' + region.advisory_url + '">' + linkText + ' &raquo;</a>' +
            '</div>';
          }

          popupHtml += '</div>';

          layer.bindPopup(popupHtml, { maxWidth: 300 });
          layer.addTo(map);
          bounds.push(layer.getBounds());
        } catch (e) {
          console.warn('GeoJSON parse error for region ' + region.name + ':', e);
        }
      });

      // Default view is the configured center/zoom (set via setView above).
      // If the visitor shares their location, zoom in to the forecast zones
      // nearest them instead. Falls back silently to the default center on
      // denial, error, timeout, or when geolocation is unavailable.
      if (bounds.length > 0 && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (pos) {
          var here = L.latLng(pos.coords.latitude, pos.coords.longitude);
          var ranked = bounds.map(function (b, idx) {
            return { idx: idx, dist: here.distanceTo(b.getCenter()) };
          }).sort(function (a, b) { return a.dist - b.dist; });
          // Only recenter on the visitor when they're reasonably close to a
          // forecast region. If the nearest region is more than 100 miles
          // (~160,934 m) away, keep the configured site-default center/zoom so
          // a distant visitor isn't thrown across the map to a zone they're
          // nowhere near.
          var HUNDRED_MILES_M = 160934;
          if (ranked[0].dist > HUNDRED_MILES_M) return;
          var k = Math.min(3, ranked.length);
          var near = L.latLngBounds(
            bounds[ranked[0].idx].getSouthWest(),
            bounds[ranked[0].idx].getNorthEast()
          );
          for (var j = 1; j < k; j++) {
            near.extend(bounds[ranked[j].idx]);
          }
          map.fitBounds(near, { padding: [30, 30], maxZoom: 11 });
        }, function () {}, { timeout: 8000, maximumAge: 600000 });
      }
    }
  };
})(jQuery);
