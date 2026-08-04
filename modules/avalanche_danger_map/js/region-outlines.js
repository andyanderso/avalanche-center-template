/**
 * Overlays forecast-region boundaries (outline only, no fill) onto other
 * Leaflet maps - the observations & incidents map views and the Field Work
 * Plan map. Enabled per-page by avalanche_danger_map_enable_region_outlines(),
 * which sets Backdrop.settings.avalancheRegionOutlines.url. The boundaries are
 * fetched once and shared across every map on the page.
 */
(function ($) {
  'use strict';

  var cachedGeo = null;
  var fetchPending = false;
  var waiting = [];

  function outlineStyle() {
    // No fill - just the zone boundary line, so it never obscures the markers
    // or the map underneath.
    return { fill: false, color: '#c0392b', weight: 2, opacity: 0.9 };
  }

  function addTo(lMap) {
    if (!lMap || typeof L === 'undefined' || !cachedGeo) {
      return;
    }
    try {
      // interactive:false so the outlines don't swallow clicks meant for the
      // observation/incident markers beneath them.
      L.geoJSON(cachedGeo, { style: outlineStyle, interactive: false }).addTo(lMap);
    }
    catch (e) {
      if (window.console) {
        window.console.warn('Region outline overlay failed:', e);
      }
    }
  }

  $(document).on('leaflet.map', function (e, mapDef, lMap) {
    var s = Backdrop.settings.avalancheRegionOutlines;
    if (!s || !s.url || !lMap) {
      return;
    }
    if (cachedGeo) {
      addTo(lMap);
      return;
    }
    waiting.push(lMap);
    if (fetchPending) {
      return;
    }
    fetchPending = true;
    $.getJSON(s.url).done(function (data) {
      cachedGeo = data;
      $.each(waiting, function (i, m) { addTo(m); });
      waiting = [];
    }).fail(function () {
      fetchPending = false;
    });
  });
})(jQuery);
