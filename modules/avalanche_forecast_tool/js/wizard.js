/**
 * @file
 * The guided avalanche forecast wizard.
 *
 * A self-contained client-side app: pick avalanche problems (guided by observed
 * conditions), set each problem's location / sensitivity / distribution (which
 * derives Likelihood from the Statham Fig 2 matrix) and size, see a live hazard
 * chart and a *suggested* danger rating per elevation band, get flagged for
 * discouraged problem pairings, then approve to pre-fill the forecast form.
 *
 * Nothing here writes the danger rating — the forecaster always enters that.
 */
(function ($, Backdrop) {
  'use strict';

  Backdrop.behaviors.avalancheForecastTool = {
    attach: function (context, settings) {
      var mount = document.getElementById('aft-wizard');
      if (!mount || mount.getAttribute('data-aft-ready')) {
        return;
      }
      var cfg = settings.avalancheForecastTool;
      if (!cfg) {
        return;
      }
      mount.setAttribute('data-aft-ready', '1');
      new Wizard(mount, cfg).render();
    }
  };

  /* ---------------------------------------------------------------------- *
   * Small helpers
   * ---------------------------------------------------------------------- */

  function el(tag, attrs, children) {
    var node = document.createElement(tag);
    attrs = attrs || {};
    Object.keys(attrs).forEach(function (k) {
      if (k === 'class') { node.className = attrs[k]; }
      else if (k === 'html') { node.innerHTML = attrs[k]; }
      else if (k === 'text') { node.appendChild(document.createTextNode(attrs[k])); }
      else if (k.slice(0, 2) === 'on' && typeof attrs[k] === 'function') {
        node.addEventListener(k.slice(2), attrs[k]);
      }
      // Skip null/undefined/false so `disabled: cond ? 'disabled' : null`
      // doesn't set the attribute to the string "null" (which is truthy).
      else if (attrs[k] != null && attrs[k] !== false) { node.setAttribute(k, attrs[k]); }
    });
    (children || []).forEach(function (c) {
      if (c == null) { return; }
      node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
    });
    return node;
  }

  var DANGER_RANK = {
    'No Rating': 0, 'Low': 1, 'Moderate': 2, 'Considerable': 3, 'High': 4, 'Extreme': 5
  };

  // The danger-rating suggestion (mountain + band rows + overall) is hidden for
  // now — it still needs work. Set to true to bring it back. The hazard-chart
  // gradient stays visible regardless.
  var SHOW_DANGER_SUGGESTION = false;

  function hexToRgb(hex) {
    hex = String(hex).replace('#', '');
    if (hex.length === 3) { hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2]; }
    return [parseInt(hex.slice(0, 2), 16), parseInt(hex.slice(2, 4), 16), parseInt(hex.slice(4, 6), 16)];
  }
  function lerp(a, b, t) { return a + (b - a) * t; }
  function mixRgb(c1, c2, t) {
    return [Math.round(lerp(c1[0], c2[0], t)), Math.round(lerp(c1[1], c2[1], t)), Math.round(lerp(c1[2], c2[2], t))];
  }
  function rgbStr(c) { return 'rgb(' + c[0] + ',' + c[1] + ',' + c[2] + ')'; }

  // Unique id source (used for radio-group names, so each group is distinct and
  // options within a group share one name — otherwise multiple would select).
  var UID = 0;
  function uid() { return 'aft-g' + (++UID); }

  // True when a slider position value is actually set. Position 0 is a valid
  // value (the lowest term), so a plain falsiness check won't do.
  function numSet(v) { return v !== '' && v != null && !isNaN(Number(v)); }

  // Discrete dual-thumb range slider over an ordered option list, so a forecaster
  // can express a RANGE (and therefore uncertainty) rather than one value.
  //   opts = { options:[{key,label}], min:key|'', max:key|'',
  //            onChange:fn(minKey,maxKey), placeholder:string }
  function rangeSlider(opts) {
    var options = opts.options, n = options.length;
    var minV = (opts.min === '' || opts.min == null) ? null : Number(opts.min);
    var maxV = (opts.max === '' || opts.max == null) ? null : Number(opts.max);
    var set = (minV != null && maxV != null && !isNaN(minV) && !isNaN(maxV));
    // Continuous position 0..n-1. Position i aligns with term row i's centre;
    // rows are stacked highest-at-top, so higher value sits nearer the top.
    function topPct(pos) { return n <= 1 ? 0 : (1 - (pos + 0.5) / n) * 100; }

    var root = el('div', { class: 'aft-vslider' });
    var body = el('div', { class: 'aft-vslider-body' });
    var track = el('div', { class: 'aft-vslider-track' });
    var fill = el('div', { class: 'aft-vslider-fill' });
    track.appendChild(fill);
    function mkThumb(cls) {
      return el('div', {
        class: 'aft-vslider-thumb ' + cls, tabindex: '0', role: 'slider',
        'aria-valuemin': '0', 'aria-valuemax': String(n - 1)
      });
    }
    var thumbMin = mkThumb('aft-vslider-thumb--min');
    var thumbMax = mkThumb('aft-vslider-thumb--max');
    track.appendChild(thumbMin);
    track.appendChild(thumbMax);

    // Term rows (label + description), highest value first (top).
    var terms = el('ul', { class: 'aft-vslider-terms' });
    var rowEls = [];
    for (var ri = n - 1; ri >= 0; ri--) {
      var o = options[ri];
      var kids = [el('span', { class: 'aft-vterm-label', text: o.label })];
      if (o.desc) { kids.push(el('span', { class: 'aft-vterm-desc', text: o.desc })); }
      if (o.scale) { kids.push(el('span', { class: 'aft-vterm-scale', text: o.scale })); }
      var li = el('li', { class: 'aft-vterm' }, kids);
      (function (idx) { li.addEventListener('click', function () { setPos('near', idx); }); })(ri);
      terms.appendChild(li);
      rowEls[ri] = li;
    }
    body.appendChild(track);
    body.appendChild(terms);
    var caption = el('div', { class: 'aft-vslider-caption' });
    root.appendChild(body);
    root.appendChild(caption);

    function paint() {
      root.classList.toggle('aft-vslider--unset', !set);
      rowEls.forEach(function (li, i) {
        var on = set && (i - 0.5 < maxV && i + 0.5 > minV);
        li.classList.toggle('aft-vterm--on', !!on);
      });
      if (!set) {
        caption.textContent = opts.placeholder || Backdrop.t('Drag the handles to set a range');
        return;
      }
      thumbMax.style.top = topPct(maxV) + '%';
      thumbMin.style.top = topPct(minV) + '%';
      fill.style.top = topPct(maxV) + '%';
      fill.style.height = (topPct(minV) - topPct(maxV)) + '%';
      thumbMin.setAttribute('aria-valuenow', minV.toFixed(2));
      thumbMin.setAttribute('aria-valuetext', options[Math.round(minV)].label);
      thumbMax.setAttribute('aria-valuenow', maxV.toFixed(2));
      thumbMax.setAttribute('aria-valuetext', options[Math.round(maxV)].label);
      caption.innerHTML = '';
      var lo = options[Math.round(minV)].label, hi = options[Math.round(maxV)].label;
      caption.appendChild(el('strong', { text: lo === hi ? lo : (lo + ' – ' + hi) }));
    }
    function commit() { opts.onChange(minV, maxV); }
    function posFromClientY(clientY) {
      var r = track.getBoundingClientRect();
      var f = r.height ? (clientY - r.top) / r.height : 0; // 0 top, 1 bottom
      f = Math.max(0, Math.min(1, f));
      return Math.max(0, Math.min(n - 1, (1 - f) * n - 0.5));
    }
    function setPos(which, pos) {
      pos = Math.max(0, Math.min(n - 1, pos));
      if (!set) { minV = maxV = pos; set = true; }
      else if (which === 'min') { minV = Math.min(pos, maxV); }
      else if (which === 'max') { maxV = Math.max(pos, minV); }
      else if (pos > maxV) { maxV = pos; }
      else if (pos < minV) { minV = pos; }
      else if (Math.abs(pos - minV) <= Math.abs(pos - maxV)) { minV = pos; }
      else { maxV = pos; }
      paint(); commit();
    }
    function startDrag(which, e) {
      e.preventDefault();
      function move(ev) { setPos(which, posFromClientY(ev.clientY)); }
      function up() {
        document.removeEventListener('pointermove', move);
        document.removeEventListener('pointerup', up);
      }
      document.addEventListener('pointermove', move);
      document.addEventListener('pointerup', up);
    }
    thumbMin.addEventListener('pointerdown', function (e) { startDrag('min', e); });
    thumbMax.addEventListener('pointerdown', function (e) { startDrag('max', e); });
    track.addEventListener('pointerdown', function (e) {
      if (e.target === thumbMin || e.target === thumbMax) { return; }
      setPos('near', posFromClientY(e.clientY));
    });
    function keyMove(which, e) {
      var d = (e.key === 'ArrowUp' || e.key === 'ArrowRight') ? 0.25
        : (e.key === 'ArrowDown' || e.key === 'ArrowLeft') ? -0.25 : 0;
      if (!d) { return; }
      e.preventDefault();
      if (!set) { setPos(which, which === 'min' ? 0 : n - 1); return; }
      setPos(which, (which === 'min' ? minV : maxV) + d);
    }
    thumbMin.addEventListener('keydown', function (e) { keyMove('min', e); });
    thumbMax.addEventListener('keydown', function (e) { keyMove('max', e); });

    paint();
    return root;
  }

  // An info "?" button that toggles a help excerpt from raw text.
  function helpPopup(text) {
    if (!text) { return null; }
    var pop = el('span', { class: 'aft-help-pop', role: 'note', text: text });
    var btn = el('button', {
      type: 'button', class: 'aft-help', 'aria-expanded': 'false',
      'aria-label': Backdrop.t('More information'),
      onclick: function (e) {
        e.preventDefault();
        var open = pop.classList.toggle('aft-help-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      }
    }, ['?']);
    return el('span', { class: 'aft-help-wrap' }, [btn, pop]);
  }
  // Help button sourced from cfg.help[key] (e.g. the Statham 2018 excerpts).
  function helpButton(key, cfg) {
    return helpPopup((cfg.help && cfg.help[key]) ? cfg.help[key] : '');
  }

  // A heading (h2/h3/h4) with an optional trailing help button.
  function heading(tag, text, helpKey, cfg) {
    var kids = [el('span', { text: text })];
    var h = helpButton(helpKey, cfg);
    if (h) { kids.push(h); }
    return el(tag, { class: 'aft-heading' }, kids);
  }

  /* ---------------------------------------------------------------------- *
   * Wizard
   * ---------------------------------------------------------------------- */

  function Wizard(mount, cfg) {
    this.mount = mount;
    this.cfg = cfg;
    // Lookup helpers.
    this.problemByKey = {};
    cfg.problems.forEach(function (p) { this.problemByKey[p.key] = p; }, this);
    // State.
    this.conditions = {};        // condition id -> bool
    this.problems = [];          // list of problem-builder states
    this.notes = {};             // pairing key -> justification text
  }

  Wizard.prototype.problemLabel = function (key) {
    return this.problemByKey[key] ? this.problemByKey[key].label : key;
  };

  /* ---- Derived algorithm ------------------------------------------------ */

  // Bilinearly interpolate the Fig 2 matrix at CONTINUOUS distribution/sensitivity
  // positions (0-indexed floats), so a value part-way between two terms yields a
  // fractional likelihood (e.g. between Stubborn and Reactive -> ~2.5, "Possible
  // and part of Likely").
  Wizard.prototype.interpLikelihood = function (distPos, sensPos) {
    var sens = this.cfg.sensitivity, dist = this.cfg.distribution, m = this.cfg.matrix;
    var d0 = Math.floor(distPos), d1 = Math.min(dist.length - 1, d0 + 1), fd = distPos - d0;
    var s0 = Math.floor(sensPos), s1 = Math.min(sens.length - 1, s0 + 1), fs = sensPos - s0;
    function cell(di, si) { return parseInt((m[dist[di].key] || {})[sens[si].key] || '0', 10); }
    var bottom = cell(d0, s0) * (1 - fs) + cell(d0, s1) * fs;
    var top = cell(d1, s0) * (1 - fs) + cell(d1, s1) * fs;
    return bottom * (1 - fd) + top * fd;
  };

  // The likelihood RANGE (fractional) from the sensitivity/distribution ranges.
  // The interpolated surface is monotonic in both, so the low end is the low
  // corner and the high end the high corner. Returns {min,max} as floats in 1..5.
  Wizard.prototype.likelihoodRange = function (p) {
    if (!numSet(p.distMin) || !numSet(p.sensMin) || !numSet(p.distMax) || !numSet(p.sensMax)) { return null; }
    var lo = this.interpLikelihood(Number(p.distMin), Number(p.sensMin));
    var hi = this.interpLikelihood(Number(p.distMax), Number(p.sensMax));
    return { min: Math.min(lo, hi), max: Math.max(lo, hi) };
  };

  // The single likelihood written to the published forecast: the UPPER bound of
  // the range, rounded to a whole level (conservative). The wizard shows the full
  // fractional range for context.
  Wizard.prototype.likelihoodFor = function (p) {
    var r = this.likelihoodRange(p);
    return r ? String(Math.max(1, Math.min(5, Math.round(r.max)))) : '';
  };

  // Danger table lookup: likelihood('1'..'5') x size key('1'..'5') -> label.
  Wizard.prototype.dangerFor = function (likelihood, sizeKey) {
    var row = this.cfg.dangerTable[likelihood];
    if (!row) { return 'No Rating'; }
    var idx = parseInt(sizeKey, 10) - 1;
    return row[idx] || 'No Rating';
  };

  // Which elevation bands a problem affects (has any rose cell in).
  Wizard.prototype.bandsOf = function (p) {
    var bands = {};
    Object.keys(p.rose).forEach(function (cell) {
      if (p.rose[cell]) { bands[cell.split(':')[0]] = true; }
    });
    return bands;
  };

  // Suggested danger per band + overall, from the problems affecting each band.
  Wizard.prototype.dangerSuggestions = function () {
    var self = this;
    var out = { above: 'No Rating', near: 'No Rating', below: 'No Rating', overall: 'No Rating' };
    ['above', 'near', 'below'].forEach(function (band) {
      var best = 'No Rating';
      self.problems.forEach(function (p) {
        var like = self.likelihoodFor(p);
        if (!like || !numSet(p.sizeMax)) { return; }
        if (!self.bandsOf(p)[band]) { return; }
        var sizeKey = String(Math.max(1, Math.min(5, Math.round(Number(p.sizeMax)) + 1)));
        var d = self.dangerFor(like, sizeKey);
        if (DANGER_RANK[d] > DANGER_RANK[best]) { best = d; }
      });
      out[band] = best;
      if (DANGER_RANK[best] > DANGER_RANK[out.overall]) { out.overall = best; }
    });
    return out;
  };

  // Discouraged-pairing check. Returns list of {rule, overlap:{location,likelihood,size}, strong}.
  Wizard.prototype.pairingFlags = function () {
    var self = this;
    var present = {};
    self.problems.forEach(function (p, i) { if (p.type) { present[p.type] = i; } });
    var flags = [];
    self.cfg.pairing.forEach(function (rule) {
      if (!(rule.a in present) || !(rule.b in present)) { return; }
      var pa = self.problems[present[rule.a]];
      var pb = self.problems[present[rule.b]];
      var overlap = {
        location: self.sharesBand(pa, pb),
        likelihood: self.likeOverlap(pa, pb),
        size: self.sizeOverlap(pa, pb)
      };
      // "Strong" (needs justification) when they overlap in every dimension.
      var strong = overlap.location && overlap.likelihood && overlap.size;
      flags.push({ rule: rule, key: rule.a + '-' + rule.b, overlap: overlap, strong: strong });
    });
    return flags;
  };

  Wizard.prototype.sharesBand = function (a, b) {
    var ba = this.bandsOf(a), bb = this.bandsOf(b);
    return Object.keys(ba).some(function (band) { return bb[band]; });
  };
  Wizard.prototype.likeOverlap = function (a, b) {
    var ra = this.likelihoodRange(a), rb = this.likelihoodRange(b);
    if (!ra || !rb) { return false; }
    // Fractional ranges intersect (allowing a one-level touch, as before).
    return ra.min <= rb.max + 1 && rb.min <= ra.max + 1;
  };
  Wizard.prototype.sizeOverlap = function (a, b) {
    if (!numSet(a.sizeMin) || !numSet(b.sizeMin)) { return false; }
    var a1 = Number(a.sizeMin), a2 = Number(a.sizeMax);
    var b1 = Number(b.sizeMin), b2 = Number(b.sizeMax);
    return a1 <= b2 && b1 <= a2; // fractional ranges intersect
  };

  /* ---- Render ----------------------------------------------------------- */

  Wizard.prototype.render = function () {
    this.mount.innerHTML = '';
    this.mount.appendChild(this.renderConditions());
    this.problemsHost = el('div', { class: 'aft-problems' });
    this.mount.appendChild(this.problemsHost);
    this.mount.appendChild(this.renderAddProblem());
    this.outputHost = el('div', { class: 'aft-output' });
    this.mount.appendChild(this.outputHost);
    this.renderProblems();
    this.renderOutput();
  };

  // Step 1: observed conditions -> suggested problem types.
  Wizard.prototype.renderConditions = function () {
    var self = this;
    var box = el('section', { class: 'aft-card aft-conditions' }, [
      heading('h2', Backdrop.t('1. What are you seeing?'), 'problems', this.cfg),
      el('p', { class: 'aft-hint', text: Backdrop.t('Check the conditions you have evidence for. The tool suggests the matching avalanche problems — you decide which to add.') })
    ]);
    var list = el('div', { class: 'aft-condition-list' });
    this.cfg.conditions.forEach(function (c) {
      var id = 'aft-cond-' + c.id;
      var cb = el('input', {
        type: 'checkbox', id: id,
        onchange: function () { self.conditions[c.id] = cb.checked; self.renderSuggestions(); }
      });
      list.appendChild(el('label', { class: 'aft-condition', 'for': id }, [cb, el('span', { text: c.label })]));
    });
    box.appendChild(list);
    this.suggestHost = el('div', { class: 'aft-suggestions' });
    box.appendChild(this.suggestHost);
    return box;
  };

  Wizard.prototype.renderSuggestions = function () {
    var self = this;
    this.suggestHost.innerHTML = '';
    var suggested = {};
    this.cfg.conditions.forEach(function (c) {
      if (self.conditions[c.id]) {
        c.suggests.forEach(function (k) { suggested[k] = true; });
      }
    });
    var keys = Object.keys(suggested);
    if (!keys.length) { return; }
    this.suggestHost.appendChild(el('p', { class: 'aft-suggest-label', text: Backdrop.t('Suggested problems:') }));
    var row = el('div', { class: 'aft-suggest-chips' });
    keys.forEach(function (k) {
      var already = self.problems.some(function (p) { return p.type === k; });
      var full = self.problems.length >= self.cfg.maxProblems;
      var btn = el('button', {
        type: 'button',
        class: 'aft-chip' + (already ? ' aft-chip--added' : ''),
        disabled: (already || full) ? 'disabled' : null,
        onclick: function () { self.addProblem(k); }
      }, [self.problemLabel(k) + (already ? ' ✓' : ' +')]);
      row.appendChild(btn);
    });
    this.suggestHost.appendChild(row);
  };

  Wizard.prototype.renderAddProblem = function () {
    var self = this;
    var wrap = el('div', { class: 'aft-add-problem' });
    var select = el('select', { class: 'aft-add-select' });
    select.appendChild(el('option', { value: '', text: Backdrop.t('— choose a problem —') }));
    this.cfg.problems.forEach(function (p) {
      select.appendChild(el('option', { value: p.key, text: p.label }));
    });
    var btn = el('button', {
      type: 'button', class: 'button',
      onclick: function () { if (select.value) { self.addProblem(select.value); select.value = ''; } }
    }, [Backdrop.t('Add problem')]);
    wrap.appendChild(el('span', { class: 'aft-add-label', text: Backdrop.t('Or add a problem manually:') }));
    wrap.appendChild(select);
    wrap.appendChild(btn);
    this.addProblemControls = { select: select, button: btn };
    return wrap;
  };

  Wizard.prototype.addProblem = function (typeKey) {
    if (this.problems.length >= this.cfg.maxProblems) {
      alert(Backdrop.t('You can add up to @n avalanche problems.').replace('@n', this.cfg.maxProblems));
      return;
    }
    if (this.problems.some(function (p) { return p.type === typeKey; })) { return; }
    this.problems.push({ type: typeKey, rose: {}, sensMin: '', sensMax: '', distMin: '', distMax: '', sizeMin: '', sizeMax: '' });
    this.renderProblems();
    this.renderSuggestions();
    this.renderOutput();
  };

  Wizard.prototype.removeProblem = function (idx) {
    this.problems.splice(idx, 1);
    this.renderProblems();
    this.renderSuggestions();
    this.renderOutput();
  };

  Wizard.prototype.renderProblems = function () {
    this.problemsHost.innerHTML = '';
    if (!this.problems.length) {
      this.problemsHost.appendChild(el('p', { class: 'aft-empty', text: Backdrop.t('No problems added yet. Check conditions above or add one manually.') }));
    }
    this.problems.forEach(function (p, i) {
      this.problemsHost.appendChild(this.renderProblemCard(p, i));
    }, this);
    // Toggle the manual add control when full.
    if (this.addProblemControls) {
      var full = this.problems.length >= this.cfg.maxProblems;
      this.addProblemControls.select.disabled = full;
      this.addProblemControls.button.disabled = full;
    }
  };

  Wizard.prototype.renderProblemCard = function (p, idx) {
    var self = this;
    var def = this.problemByKey[p.type] || {};
    var card = el('section', { class: 'aft-card aft-problem' });
    var titleRow = el('div', { class: 'aft-problem-title' }, [
      el('h3', { text: Backdrop.t('Problem @n: ', { '@n': idx + 1 }) + (def.label || '') })
    ]);
    var ph = helpPopup(this.cfg.problemHelp && this.cfg.problemHelp[p.type]);
    if (ph) { titleRow.appendChild(ph); }
    card.appendChild(el('div', { class: 'aft-problem-head' }, [
      titleRow,
      el('button', { type: 'button', class: 'aft-remove', title: Backdrop.t('Remove'), onclick: function () { self.removeProblem(idx); } }, ['×'])
    ]));
    if (def.desc) { card.appendChild(el('p', { class: 'aft-hint', text: def.desc })); }

    // Location rose (clickable NAC octagon, matching the published forecast).
    card.appendChild(el('h4', { text: Backdrop.t('Location (aspect & elevation)') }));
    card.appendChild(this.renderRose(p));

    // Sensitivity + distribution -> likelihood. Both are RANGES (dual-thumb
    // sliders) so the forecaster can express uncertainty; likelihood becomes a
    // range too, shown below and as the region's height on the hazard chart.
    var likeRow = el('div', { class: 'aft-likelihood-row' });
    card.appendChild(heading('h4', Backdrop.t('Sensitivity & distribution'), 'likelihood', this.cfg));
    var grid = el('div', { class: 'aft-two-col' }, [
      this.rangeField(Backdrop.t('Sensitivity to triggers'), this.cfg.sensitivity, p.sensMin, p.sensMax, function (mn, mx) {
        p.sensMin = mn; p.sensMax = mx; self.updateLikelihood(p, likeRow); self.renderOutput();
      }, 'sensitivity', Backdrop.t('Drag the two handles to set how easily it could be triggered — widen them if you are unsure.')),
      this.rangeField(Backdrop.t('Spatial distribution'), this.cfg.distribution, p.distMin, p.distMax, function (mn, mx) {
        p.distMin = mn; p.distMax = mx; self.updateLikelihood(p, likeRow); self.renderOutput();
      }, 'distribution', Backdrop.t('Drag the two handles to set how widespread it is — widen them if you are unsure.'))
    ]);
    card.appendChild(grid);
    card.appendChild(likeRow);
    this.updateLikelihood(p, likeRow);

    // Size range (also a dual-thumb slider, smallest → largest expected).
    card.appendChild(heading('h4', Backdrop.t('Expected size (destructive)'), 'size', this.cfg));
    card.appendChild(this.renderSize(p));

    return card;
  };

  // A titled range slider (with optional help "?" button) for a per-problem
  // factor. onChange(minKey, maxKey).
  Wizard.prototype.rangeField = function (title, options, minKey, maxKey, onChange, helpKey, placeholder) {
    var titleRow = el('div', { class: 'aft-choice-title' }, [el('span', { text: title })]);
    var h = helpButton(helpKey, this.cfg);
    if (h) { titleRow.appendChild(h); }
    var slider = rangeSlider({
      options: options, min: minKey, max: maxKey, onChange: onChange, placeholder: placeholder
    });
    return el('div', { class: 'aft-choice aft-range-field' }, [titleRow, slider]);
  };

  // Clickable NAC aspect/elevation rose (same octagon the published forecast
  // uses), with aspect labels + elevation-band buttons and the same multi-select
  // shortcuts as the forecast form: click an aspect label to toggle that whole
  // aspect spoke; click a band button (or Alt-click a sector) for a whole ring.
  // Outer ring = below treeline, centre = above; N up.
  Wizard.prototype.renderRose = function (p) {
    var self = this;
    var svgNS = 'http://www.w3.org/2000/svg';
    var C = 527;
    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('class', 'aft-rose-svg');
    svg.setAttribute('viewBox', '-170 -170 1394 1394');

    var pathByCell = {};
    function paint(cellKey) {
      var path = pathByCell[cellKey];
      if (!path) { return; }
      var fill = p.rose[cellKey] ? 'rgb(77,184,248)' : 'rgb(255,255,255)';
      path.setAttribute('style', 'cursor:pointer;stroke:rgb(81,85,88);stroke-width:10px;fill:' + fill + ';');
    }
    (this.cfg.rosePaths || []).forEach(function (seg) {
      var cellKey = seg.band + ':' + seg.aspect;
      var path = document.createElementNS(svgNS, 'path');
      path.setAttribute('d', seg.d);
      pathByCell[cellKey] = path;
      paint(cellKey);
      path.addEventListener('click', function (ev) {
        if (ev.altKey || ev.metaKey) {
          self.toggleGroup(p, self.bandKeys(seg.band), paint);
        }
        else {
          p.rose[cellKey] = !p.rose[cellKey];
          paint(cellKey);
          self.renderOutput();
        }
      });
      svg.appendChild(path);
    });

    // Aspect labels around the octagon; click = toggle that whole aspect spoke.
    var R = 615;
    this.cfg.aspects.forEach(function (a, ai) {
      var th = ai * Math.PI / 4;
      var txt = document.createElementNS(svgNS, 'text');
      txt.setAttribute('x', C + R * Math.sin(th));
      txt.setAttribute('y', C - R * Math.cos(th));
      txt.setAttribute('text-anchor', 'middle');
      txt.setAttribute('dominant-baseline', 'central');
      txt.setAttribute('class', 'aft-rose-aspect');
      txt.textContent = a;
      txt.addEventListener('click', function () { self.toggleGroup(p, self.aspectKeys(ai), paint); });
      svg.appendChild(txt);
    });

    // Diagram (octagon + band buttons) on the left; instructions on the right.
    var left = el('div', { class: 'aft-rose-left' });
    left.appendChild(svg);

    // Elevation-band quick-select buttons (whole ring), labelled like the form.
    var bandRow = el('div', { class: 'aft-rose-bands' });
    this.cfg.bands.forEach(function (b) {
      bandRow.appendChild(el('button', {
        type: 'button', class: 'aft-rose-band-btn',
        onclick: function () { self.toggleGroup(p, self.bandKeys(b.key), paint); }
      }, [b.label]));
    });
    left.appendChild(bandRow);

    var wrap = el('div', { class: 'aft-rose-wrap' });
    wrap.appendChild(left);
    wrap.appendChild(el('p', { class: 'aft-rose-legend', text: Backdrop.t('Click a sector to toggle it, an aspect label for a whole aspect, or a band button (or Alt-click a sector) for a whole elevation band. Outer ring = below treeline, centre = above; north is up.') }));
    return wrap;
  };

  Wizard.prototype.bandKeys = function (band) {
    var keys = [];
    for (var a = 0; a < 8; a++) { keys.push(band + ':' + a); }
    return keys;
  };
  Wizard.prototype.aspectKeys = function (aspectIdx) {
    return ['above', 'near', 'below'].map(function (b) { return b + ':' + aspectIdx; });
  };
  // Toggle a group of cells together: if any is off, turn all on; else all off.
  Wizard.prototype.toggleGroup = function (p, keys, paint) {
    var anyOff = keys.some(function (k) { return !p.rose[k]; });
    keys.forEach(function (k) { p.rose[k] = anyOff; paint(k); });
    this.renderOutput();
  };

  Wizard.prototype.renderChoice = function (title, options, current, onpick, helpKey) {
    var groupName = uid();
    var titleRow = el('div', { class: 'aft-choice-title' }, [el('span', { text: title })]);
    var h = helpButton(helpKey, this.cfg);
    if (h) { titleRow.appendChild(h); }
    var col = el('div', { class: 'aft-choice' }, [titleRow]);
    options.forEach(function (o) {
      var id = groupName + '-' + o.key;
      var radio = el('input', {
        type: 'radio', name: groupName, id: id, value: o.key,
        checked: current === o.key ? 'checked' : null,
        onchange: function () { onpick(o.key); }
      });
      col.appendChild(el('label', { class: 'aft-radio', 'for': id }, [
        radio,
        el('span', {}, [el('strong', { text: o.label }), el('span', { class: 'aft-radio-desc', text: ' — ' + o.desc })])
      ]));
    });
    return col;
  };

  // Describe one fractional likelihood value: a whole level, or "part of" the
  // next level when the value falls partway between (e.g. 2.5 -> "part of Likely").
  Wizard.prototype.likelihoodBoundLabel = function (v) {
    var labels = this.cfg.likelihoodLabels;
    var L = Math.max(1, Math.min(5, Math.floor(v)));
    var f = v - Math.floor(v);
    if (f < 0.15) { return labels[String(L)]; }
    if (f > 0.85) { return labels[String(Math.min(5, L + 1))]; }
    return Backdrop.t('part of @lvl', { '@lvl': labels[String(Math.min(5, L + 1))] });
  };

  Wizard.prototype.updateLikelihood = function (p, host) {
    host.innerHTML = '';
    host.appendChild(this.renderLikelihoodMatrix(p));
    var r = this.likelihoodRange(p);
    if (!r) {
      host.appendChild(el('span', { class: 'aft-like aft-like--pending', text: Backdrop.t('Likelihood: set the sensitivity and distribution ranges') }));
      return;
    }
    var lo = this.likelihoodBoundLabel(r.min), hi = this.likelihoodBoundLabel(r.max);
    var span = el('span', { class: 'aft-like' });
    span.appendChild(document.createTextNode(Backdrop.t('Likelihood (from the Fig 2 matrix): ')));
    span.appendChild(el('strong', { text: lo }));
    if (Math.abs(r.max - r.min) >= 0.05 && hi !== lo) {
      span.appendChild(document.createTextNode(' – '));
      span.appendChild(el('strong', { text: hi }));
    }
    host.appendChild(span);
  };

  // Visualise the Fig 2 likelihood matrix for this problem: distribution (rows,
  // widespread on top) x sensitivity (cols), each cell its resulting likelihood.
  // The whole selected RANGE of cells (the sensitivity x distribution rectangle)
  // is highlighted.
  Wizard.prototype.renderLikelihoodMatrix = function (p) {
    var self = this;
    var sens = this.cfg.sensitivity;                 // low -> high (cols)
    var dist = this.cfg.distribution.slice().reverse(); // widespread on top
    var distKeys = this.cfg.distribution.map(function (o) { return o.key; });
    var sensKeys = sens.map(function (o) { return o.key; });
    // Positions are continuous now; light up every whole cell the range overlaps.
    var haveSel = numSet(p.sensMin) && numSet(p.distMin);
    var sLo = haveSel ? Math.floor(p.sensMin) : -1, sHi = haveSel ? Math.ceil(p.sensMax) : -1;
    var dLo = haveSel ? Math.floor(p.distMin) : -1, dHi = haveSel ? Math.ceil(p.distMax) : -1;
    var table = el('table', { class: 'aft-likematrix' });
    var head = el('tr', {}, [el('th', { class: 'aft-lm-corner' }, [Backdrop.t('Likelihood')])]);
    sens.forEach(function (s) { head.appendChild(el('th', { class: 'aft-lm-col', text: s.label })); });
    table.appendChild(head);
    dist.forEach(function (d) {
      var dIdx = distKeys.indexOf(d.key);
      var tr = el('tr', {}, [el('th', { class: 'aft-lm-row', text: d.label })]);
      sens.forEach(function (s) {
        var sIdx = sensKeys.indexOf(s.key);
        var lk = (self.cfg.matrix[d.key] || {})[s.key] || '';
        var selected = haveSel && dIdx >= dLo && dIdx <= dHi && sIdx >= sLo && sIdx <= sHi;
        tr.appendChild(el('td', {
          class: 'aft-lm-cell' + (selected ? ' aft-lm-selected' : ''),
          text: lk ? self.cfg.likelihoodLabels[lk] : ''
        }));
      });
      table.appendChild(tr);
    });
    return table;
  };

  Wizard.prototype.renderSize = function (p) {
    var self = this;
    // The size classes carry a `scale` (typical length + mass), which the
    // vertical slider shows on each term row — no separate reference needed.
    return rangeSlider({
      options: this.cfg.sizes, min: p.sizeMin, max: p.sizeMax,
      onChange: function (mn, mx) { p.sizeMin = mn; p.sizeMax = mx; self.renderOutput(); },
      placeholder: Backdrop.t('Drag the two handles to set the smallest and largest expected size.')
    });
  };

  /* ---- Output: hazard chart + danger + pairing + approve ---------------- */

  Wizard.prototype.renderOutput = function () {
    if (!this.outputHost) { return; }
    this.outputHost.innerHTML = '';
    if (!this.problems.length) { return; }

    this.outputHost.appendChild(this.renderPairing());

    var chartCard = el('section', { class: 'aft-card' }, [
      heading('h2', Backdrop.t('Hazard chart'), 'hazardChart', this.cfg),
      el('p', { class: 'aft-hint', text: Backdrop.t('Each problem is a region spanning its size and likelihood ranges (Statham Fig 3) — a bigger region means more uncertainty. Background shading shows the danger the hazard chart implies.') })
    ]);
    chartCard.appendChild(this.renderChart());
    this.outputHost.appendChild(chartCard);

    // Danger-rating suggestion (mountain + band rows + overall) is hidden for
    // now — the logic needs more work. Keep computing it so the approve payload
    // stays intact, and flip SHOW_DANGER_SUGGESTION back to true to re-show it.
    this.currentSuggestions = this.dangerSuggestions();
    if (SHOW_DANGER_SUGGESTION) {
      this.outputHost.appendChild(this.renderDanger());
    }
    this.outputHost.appendChild(this.renderApprove());
  };

  Wizard.prototype.renderChart = function () {
    var self = this;
    var W = 520, H = 360, padL = 60, padB = 44, padT = 16, padR = 16;
    var plotW = W - padL - padR, plotH = H - padT - padB;
    var cols = 5, rows = 5; // D1..D5 x Unlikely..Certain
    var cw = plotW / cols, ch = plotH / rows;
    var svgNS = 'http://www.w3.org/2000/svg';
    function s(tag, attrs) {
      var n = document.createElementNS(svgNS, tag);
      Object.keys(attrs || {}).forEach(function (k) { n.setAttribute(k, attrs[k]); });
      return n;
    }
    var svg = s('svg', { viewBox: '0 0 ' + W + ' ' + H, class: 'aft-chart' });

    // Colour at a continuous (likelihood, size) point by bilinearly interpolating
    // the four surrounding grid cells (li/si in 1..5). Each grid cell's colour
    // comes from the gradient grid — a two-level cell is a 50/50 blend.
    function cellRgb(li, si) {
      var row = self.cfg.gradientTable && self.cfg.gradientTable[String(li)];
      var cell = row ? row[si - 1] : null;
      if (!cell || !cell.length) { cell = [self.dangerFor(String(li), String(si))]; }
      var rs = 0, gs = 0, bs = 0, n = 0;
      cell.forEach(function (label) {
        var c = hexToRgb(self.cfg.dangerColors[label] || '#cccccc');
        rs += c[0]; gs += c[1]; bs += c[2]; n++;
      });
      return n ? [Math.round(rs / n), Math.round(gs / n), Math.round(bs / n)] : [204, 204, 204];
    }
    function colorAt(liF, siF) {
      var si0 = Math.max(1, Math.min(4, Math.floor(siF))), fs = siF - si0;
      var li0 = Math.max(1, Math.min(4, Math.floor(liF))), fl = liF - li0;
      var bottom = mixRgb(cellRgb(li0, si0), cellRgb(li0, si0 + 1), fs);
      var top = mixRgb(cellRgb(li0 + 1, si0), cellRgb(li0 + 1, si0 + 1), fs);
      return mixRgb(bottom, top, fl);
    }
    // Smooth gradient background: a fine mesh of rects, no boxes.
    var mx = 56, my = 44, rw = plotW / mx, rh = plotH / my;
    for (var gx = 0; gx < mx; gx++) {
      for (var gy = 0; gy < my; gy++) {
        var siF = 1 + ((gx + 0.5) / mx) * 4;          // left D1 -> right D5
        var liF = 1 + (1 - (gy + 0.5) / my) * 4;      // top Certain -> bottom Unlikely
        svg.appendChild(s('rect', {
          x: padL + gx * rw, y: padT + gy * rh, width: rw + 0.6, height: rh + 0.6,
          fill: rgbStr(colorAt(liF, siF)), 'shape-rendering': 'crispEdges'
        }));
      }
    }
    // Faint gridlines at the cell boundaries so positions are still readable.
    for (var gi = 0; gi <= cols; gi++) {
      svg.appendChild(s('line', { x1: padL + gi * cw, y1: padT, x2: padL + gi * cw, y2: padT + plotH, stroke: 'rgba(255,255,255,0.35)', 'stroke-width': '1' }));
      svg.appendChild(s('line', { x1: padL, y1: padT + gi * ch, x2: padL + plotW, y2: padT + gi * ch, stroke: 'rgba(255,255,255,0.35)', 'stroke-width': '1' }));
    }
    // Axes labels.
    self.cfg.sizes.forEach(function (sz, i) {
      var tx = s('text', { x: padL + i * cw + cw / 2, y: H - padB + 18, 'text-anchor': 'middle', class: 'aft-axis' });
      tx.textContent = sz.label; svg.appendChild(tx);
    });
    ['1', '2', '3', '4', '5'].forEach(function (lk, i) {
      var ty = s('text', { x: padL - 8, y: padT + (rows - parseInt(lk, 10)) * ch + ch / 2 + 4, 'text-anchor': 'end', class: 'aft-axis' });
      ty.textContent = self.cfg.likelihoodLabels[lk]; svg.appendChild(ty);
    });
    var xlab = s('text', { x: padL + plotW / 2, y: H - 6, 'text-anchor': 'middle', class: 'aft-axis-title' });
    xlab.textContent = Backdrop.t('Destructive size'); svg.appendChild(xlab);

    // Plot each problem as a REGION: a rectangle spanning its size range (x) and
    // its likelihood range (y). A bigger rectangle = more uncertainty. Regions
    // are semi-transparent so overlaps between problems read through.
    var PALETTE = ['#14507a', '#b5341f', '#5b3a8c'];
    self.problems.forEach(function (p, idx) {
      var r = self.likelihoodRange(p);
      if (!r || !numSet(p.sizeMin) || !numSet(p.sizeMax)) { return; }
      // Positions are continuous (0-indexed): size pos maps to columns, the
      // fractional likelihood range maps to rows — so a partway value covers
      // only PART of a box.
      var szMin = Number(p.sizeMin), szMax = Number(p.sizeMax);
      var color = PALETTE[idx % PALETTE.length];
      var inset = 3;
      var x1 = padL + szMin * cw + inset;
      var x2 = padL + (szMax + 1) * cw - inset;
      var yTop = padT + (rows - r.max) * ch + inset;
      var yBot = padT + (rows - r.min + 1) * ch - inset;
      svg.appendChild(s('rect', {
        x: x1, y: yTop, width: Math.max(2, x2 - x1), height: Math.max(2, yBot - yTop),
        rx: 6, fill: color, 'fill-opacity': '0.22', stroke: color, 'stroke-width': '2.5'
      }));
      // Numbered badge at the region's top-left corner (stays visible on overlap).
      var bx = x1 + 13, by = yTop + 13;
      var g = s('g', {});
      g.appendChild(s('circle', { cx: bx, cy: by, r: '11', fill: '#fff', stroke: color, 'stroke-width': '2' }));
      var num = s('text', { x: bx, y: by + 4, 'text-anchor': 'middle', class: 'aft-marker' });
      num.textContent = String(idx + 1);
      g.appendChild(num);
      svg.appendChild(g);
    });

    // Legend keying region colours/numbers to problems.
    var legend = el('ul', { class: 'aft-chart-legend' });
    self.problems.forEach(function (p, idx) {
      var color = PALETTE[idx % PALETTE.length];
      var badge = el('span', { class: 'aft-legend-num', text: String(idx + 1) });
      badge.style.borderColor = color;
      badge.style.color = color;
      legend.appendChild(el('li', {}, [badge, ' ' + (self.problemByKey[p.type] ? self.problemByKey[p.type].label : '')]));
    });
    var wrap = el('div', { class: 'aft-chart-wrap' });
    wrap.appendChild(svg);
    wrap.appendChild(legend);
    return wrap;
  };

  Wizard.prototype.renderDanger = function () {
    var self = this;
    var sugg = this.dangerSuggestions();
    this.currentSuggestions = sugg;
    var card = el('section', { class: 'aft-card aft-danger' }, [
      heading('h2', Backdrop.t('Suggested danger rating'), 'danger', this.cfg),
      el('p', { class: 'aft-hint', text: Backdrop.t('A suggestion from the hazard chart — danger is a judgment call, so review it and enter the actual rating yourself on the forecast form.') })
    ]);

    function pill(label) {
      return el('span', {
        class: 'aft-danger-pill',
        style: 'background:' + (self.cfg.dangerColors[label] || '#ccc') + ';color:' + (label === 'Extreme' ? '#fff' : '#111') + ';',
        text: self.cfg.dangerLabels[label] || label
      });
    }

    // The mountain (three elevation bands only) + their band rows.
    var layout = el('div', { class: 'aft-danger-layout' });
    layout.appendChild(this.renderPyramid(sugg));
    var rows = el('div', { class: 'aft-danger-band-rows' });
    [['above', this.bandLabel('above')], ['near', this.bandLabel('near')], ['below', this.bandLabel('below')]].forEach(function (b) {
      rows.appendChild(el('div', { class: 'aft-danger-band-row' }, [
        el('span', { class: 'aft-danger-band-name', text: b[1] }),
        pill(sugg[b[0]])
      ]));
    });
    layout.appendChild(rows);
    card.appendChild(layout);

    // Overall danger — shown separately from the by-elevation mountain.
    card.appendChild(el('div', { class: 'aft-danger-overall' }, [
      el('span', { class: 'aft-danger-overall-name', text: Backdrop.t('Overall danger') }),
      pill(sugg.overall)
    ]));
    return card;
  };

  Wizard.prototype.bandLabel = function (key) {
    var found = null;
    (this.cfg.bands || []).forEach(function (b) { if (b.key === key) { found = b.label; } });
    return found || key;
  };

  // The NAC danger "mountain" (same pyramid the forecast form/display uses),
  // each elevation band filled by its suggested danger colour.
  Wizard.prototype.renderPyramid = function (sugg) {
    var self = this;
    var svgNS = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('class', 'aft-pyramid');
    svg.setAttribute('viewBox', '0 0 250 300');
    var paths = this.cfg.pyramidPaths || {};
    // lower = below, mid = near, upper = above.
    [['lower', 'below'], ['mid', 'near'], ['upper', 'above']].forEach(function (m) {
      if (!paths[m[0]]) { return; }
      var label = sugg[m[1]];
      var fill = (self.cfg.dangerColors[label] && label !== 'No Rating') ? self.cfg.dangerColors[label] : '#939598';
      var path = document.createElementNS(svgNS, 'path');
      path.setAttribute('d', paths[m[0]]);
      path.setAttribute('style', 'fill:' + fill + ';');
      svg.appendChild(path);
    });
    return el('div', { class: 'aft-pyramid-wrap' }, [svg]);
  };

  // Phase E: discouraged-pairing warnings with justification capture.
  Wizard.prototype.renderPairing = function () {
    var self = this;
    var flags = this.pairingFlags();
    var host = el('div', { class: 'aft-pairing-host' });
    // Drop stored justifications for pairs that no longer apply.
    Object.keys(this.notes).forEach(function (k) {
      if (!flags.some(function (f) { return f.key === k; })) { delete self.notes[k]; }
    });
    flags.forEach(function (f) {
      var dims = [];
      if (f.overlap.location) { dims.push(Backdrop.t('location')); }
      if (f.overlap.likelihood) { dims.push(Backdrop.t('likelihood')); }
      if (f.overlap.size) { dims.push(Backdrop.t('size')); }
      var cls = 'aft-card aft-pairing ' + (f.strong ? 'aft-pairing--strong messages warning' : 'aft-pairing--soft messages status');
      var box = el('section', { class: cls });
      box.appendChild(el('h3', { text: Backdrop.t('Check this pairing: @a + @b', { '@a': self.problemLabel(f.rule.a), '@b': self.problemLabel(f.rule.b) }) }));
      box.appendChild(el('p', { text: f.rule.reason }));
      if (dims.length) {
        box.appendChild(el('p', { class: 'aft-overlap', html: Backdrop.t('These two currently overlap in: ') + '<strong>' + dims.join(', ') + '</strong>.' }));
      }
      if (f.strong) {
        box.appendChild(el('p', { class: 'aft-pairing-ask', text: Backdrop.t('Because they overlap in location, likelihood and size, please explain why you are forecasting both:') }));
        var ta = el('textarea', {
          class: 'aft-justify', rows: '2',
          placeholder: Backdrop.t('Why are both problems needed here?'),
          oninput: function () { self.notes[f.key] = ta.value; self.updateApproveState(); }
        });
        ta.value = self.notes[f.key] || '';
        box.appendChild(ta);
      }
      host.appendChild(box);
    });
    return host;
  };

  Wizard.prototype.renderApprove = function () {
    var self = this;
    var card = el('section', { class: 'aft-card aft-approve' });
    card.appendChild(el('p', { class: 'aft-hint', text: Backdrop.t('When you approve, the forecast form opens with the problems, likelihood and size filled in. Review everything and enter the danger rating before saving.') }));
    this.approveBtn = el('button', {
      type: 'button', class: 'button button-primary aft-approve-btn',
      onclick: function () { self.approve(); }
    }, [Backdrop.t('Approve & pre-fill the forecast form')]);
    this.approveMsg = el('span', { class: 'aft-approve-msg' });
    card.appendChild(this.approveBtn);
    card.appendChild(this.approveMsg);
    this.updateApproveState();
    return card;
  };

  // Block approval only until required justifications are provided.
  Wizard.prototype.updateApproveState = function () {
    if (!this.approveBtn) { return; }
    var flags = this.pairingFlags();
    var missing = flags.some(function (f) {
      return f.strong && !(this.notes[f.key] && this.notes[f.key].trim());
    }, this);
    var incomplete = this.problems.some(function (p) {
      return !p.type || !this.likelihoodFor(p) || !numSet(p.sizeMax) || !Object.keys(p.rose).some(function (c) { return p.rose[c]; });
    }, this);
    this.approveBtn.disabled = missing || incomplete || !this.problems.length;
    this.approveMsg.textContent = missing
      ? Backdrop.t('Add a justification for the flagged pairing above to continue.')
      : (incomplete ? Backdrop.t('Finish each problem (location, likelihood, size) to continue.') : '');
  };

  Wizard.prototype.approve = function () {
    var self = this;
    var payload = {
      problems: this.problems.map(function (p) {
        var rose = Object.keys(p.rose).filter(function (c) { return p.rose[c]; });
        // Size positions are 0-indexed floats; the form takes discrete D-class
        // keys ('1'..'5'), so round each bound to the nearest whole class.
        function sizeKey(v) { return String(Math.max(1, Math.min(5, Math.round(Number(v)) + 1))); }
        return {
          type: p.type,
          likelihood: self.likelihoodFor(p),
          sizeMin: sizeKey(numSet(p.sizeMin) ? p.sizeMin : p.sizeMax),
          sizeMax: sizeKey(numSet(p.sizeMax) ? p.sizeMax : p.sizeMin),
          rose: rose
        };
      }),
      suggestions: this.currentSuggestions || this.dangerSuggestions(),
      notes: Object.keys(this.notes).map(function (k) { return self.notes[k]; }).filter(Boolean)
    };
    this.approveBtn.disabled = true;
    this.approveMsg.textContent = Backdrop.t('Opening the forecast form…');

    var form = new FormData();
    form.append('token', this.cfg.token);
    form.append('payload', JSON.stringify(payload));
    fetch(this.cfg.applyUrl, { method: 'POST', body: form, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data && data.redirect) { window.location.href = data.redirect; }
        else { self.approveMsg.textContent = Backdrop.t('Could not save. Please try again.'); self.updateApproveState(); }
      })
      .catch(function () { self.approveMsg.textContent = Backdrop.t('Could not save. Please try again.'); self.updateApproveState(); });
  };

})(jQuery, Backdrop);
