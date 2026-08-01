/**
 * @file
 * Pre-fills the advisory (forecast) add form from an approved wizard run.
 *
 * Reads Backdrop.settings.avalancheForecastToolPrefill (stashed server-side when
 * the forecaster approved the wizard) and fills the problem type, likelihood,
 * size and location-rose fields. The danger rating is NEVER written — it is
 * shown only as a suggestion in a summary banner for the forecaster to consider.
 */
(function ($, Backdrop) {
  'use strict';

  // danger_rose stores color_idx*2 per rose element; colors[6] ("#4db8f8") is
  // the blue "location" marker used by the loc_* pickers. 6 * 2 = 12.
  var ROSE_LOCATION_VALUE = 12;

  Backdrop.behaviors.avalancheForecastToolPrefill = {
    attach: function (context, settings) {
      var data = settings.avalancheForecastToolPrefill;
      if (!data || !data.problems || this._done) { return; }
      this._done = true;

      var form = document.querySelector('form.node-form, form[id^="advisory-node-form"], form.node-advisory-form');
      if (!form) { form = document; }

      data.problems.forEach(function (p, i) {
        var n = i + 1; // fields are field_*_1 / _2 / _3
        setSelect(form, 'field_type_' + n, p.type);
        setSelect(form, 'field_likelihood_' + n, p.likelihood);
        // Size is a multi-value checkboxes widget (field_size_N[und][1..5]);
        // check every size in the expected min–max range.
        checkSizeRange(form, 'field_size_' + n, p.sizeMin, p.sizeMax);
        fillRose(form, 'field_rose_' + n, p.rose || []);
      });

      showBanner(form, data);
    }
  };

  // Set a single-value options_select field to `value` and fire change events.
  function setSelect(form, fieldName, value) {
    if (!value) { return; }
    var sel = form.querySelector('select[name^="' + fieldName + '["]') ||
              form.querySelector('[name^="' + fieldName + '["]');
    if (!sel) { return; }
    if (sel.tagName === 'SELECT') {
      var found = false;
      for (var i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === String(value)) { sel.selectedIndex = i; found = true; break; }
      }
      if (!found) { return; }
    }
    else {
      sel.value = value;
    }
    fire(sel, 'change');
    if (window.jQuery) { window.jQuery(sel).trigger('change'); }
  }

  // Check the size checkboxes for every D-size in the [min, max] range.
  function checkSizeRange(form, fieldName, minKey, maxKey) {
    var mn = parseInt(minKey, 10) || parseInt(maxKey, 10);
    var mx = parseInt(maxKey, 10) || parseInt(minKey, 10);
    if (!mn || !mx) { return; }
    if (mn > mx) { var s = mn; mn = mx; mx = s; }
    for (var k = mn; k <= mx; k++) {
      var box = form.querySelector('input[type="checkbox"][name="' + fieldName + '[und][' + k + ']"]') ||
                form.querySelector('input[type="checkbox"][name$="[' + k + ']"][name^="' + fieldName + '["]');
      if (box && !box.checked) { box.checked = true; fire(box, 'change'); }
    }
  }

  // Mark the location-rose cells: set hidden inputs + recolor the SVG if reachable.
  function fillRose(form, fieldName, cells) {
    if (!cells || !cells.length) { return; }
    // cells are "band:aspectIndex"; map to danger_rose element index 0..23.
    // Column groups: above 0-7, near 8-15, below 16-23 (see question #2).
    var bandOffset = { above: 0, near: 8, below: 16 };
    cells.forEach(function (cell) {
      var parts = String(cell).split(':');
      var offset = bandOffset[parts[0]];
      var aspect = parseInt(parts[1], 10);
      if (offset === undefined || isNaN(aspect)) { return; }
      var idx = offset + aspect;
      var input = form.querySelector('input[name$="[' + idx + ']"][name^="' + fieldName + '["]');
      if (input) {
        input.value = ROSE_LOCATION_VALUE;
        recolorSvgElement(fieldName, idx);
      }
    });
  }

  // Best-effort: recolor the matching element inside the embedded rose SVG so the
  // forecaster sees the location visually. If the SVG is not reachable
  // (cross-origin/embed timing), the hidden values are still correct on save.
  function recolorSvgElement(fieldName, idx) {
    try {
      var embeds = document.querySelectorAll('embed, object, iframe');
      for (var e = 0; e < embeds.length; e++) {
        var doc = embeds[e].contentDocument || (embeds[e].getSVGDocument && embeds[e].getSVGDocument());
        if (!doc) { continue; }
        var params = doc.getElementsByTagName('param');
        var base = params.length ? params[0].value : '';
        if (base.indexOf(fieldName) !== 0) { continue; }
        var node = doc.getElementById(String(idx));
        if (node) { node.setAttribute('fill', '#4db8f8'); }
      }
    }
    catch (err) { /* embedded SVG not reachable; hidden values still saved */ }
  }

  function fire(node, type) {
    var ev;
    if (typeof Event === 'function') { ev = new Event(type, { bubbles: true }); }
    else { ev = document.createEvent('HTMLEvents'); ev.initEvent(type, true, false); }
    node.dispatchEvent(ev);
  }

  // A summary banner: what was filled + the danger SUGGESTION (never written).
  function showBanner(form, data) {
    var wrap = document.createElement('div');
    wrap.className = 'aft-prefill-banner messages status';
    var html = '<p><strong>' + t('The guided tool filled in the problem type, likelihood, size and location fields below.') +
      '</strong> ' + t('Please review everything, then set the danger rating yourself.') + '</p>';

    if (data.suggestions) {
      var map = { above: t('Above Treeline'), near: t('Near Treeline'), below: t('Below Treeline'), overall: t('Overall') };
      var parts = [];
      ['above', 'near', 'below', 'overall'].forEach(function (b) {
        if (data.suggestions[b]) { parts.push(map[b] + ': <strong>' + esc(data.suggestions[b]) + '</strong>'); }
      });
      if (parts.length) {
        html += '<p>' + t('Suggested danger rating (for your consideration — not entered): ') + parts.join(' · ') + '</p>';
      }
    }
    if (data.notes && data.notes.length) {
      html += '<p>' + t('Pairing justification you noted: ') + esc(data.notes.join(' — ')) + '</p>';
    }
    wrap.innerHTML = html;
    var target = form.querySelector ? form : document.body;
    (target.firstChild ? target.insertBefore(wrap, target.firstChild) : target.appendChild(wrap));
  }

  function t(s) { return (Backdrop.t ? Backdrop.t(s) : s); }
  function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

})(jQuery, Backdrop);
