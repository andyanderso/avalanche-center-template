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
        scrollWheelZoom: false
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
            '<div class="danger-map-popup-rating" style="background:' + region.color + ';">' +
              Backdrop.t('Danger Rating:') + ' <strong>' + region.label + '</strong>' +
            '</div>';

          if (region.travel_advice) {
            popupHtml += '<div class="danger-map-popup-advice">' +
              '<em>' + Backdrop.t('Travel Advice:') + '</em><br/>' + region.travel_advice +
            '</div>';
          }

          if (region.advisory_url) {
            var linkText = region.expired ? Backdrop.t('Get more information') : Backdrop.t('Read Full Advisory');
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

      if (bounds.length > 0) {
        var combined = bounds[0];
        for (var i = 1; i < bounds.length; i++) {
          combined.extend(bounds[i]);
        }
        map.fitBounds(combined, { padding: [20, 20] });
      }
    }
  };
})(jQuery);
