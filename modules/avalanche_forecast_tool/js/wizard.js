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

  // Unique id source (used for radio-group names, so each group is distinct and
  // options within a group share one name — otherwise multiple would select).
  var UID = 0;
  function uid() { return 'aft-g' + (++UID); }

  // An info "?" button that toggles a help excerpt (e.g. from Statham 2018).
  function helpButton(key, cfg) {
    var text = (cfg.help && cfg.help[key]) ? cfg.help[key] : '';
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

  // Statham Fig 2: distribution x sensitivity -> likelihood key ('1'..'5').
  Wizard.prototype.likelihoodFor = function (p) {
    if (!p.distribution || !p.sensitivity) { return ''; }
    var row = this.cfg.matrix[p.distribution];
    return row ? (row[p.sensitivity] || '') : '';
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
        var size = p.sizeMax || p.sizeMin;
        if (!like || !size) { return; }
        if (!self.bandsOf(p)[band]) { return; }
        var d = self.dangerFor(like, size);
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
    var la = parseInt(this.likelihoodFor(a), 10), lb = parseInt(this.likelihoodFor(b), 10);
    if (!la || !lb) { return false; }
    return Math.abs(la - lb) <= 1; // within one likelihood level
  };
  Wizard.prototype.sizeOverlap = function (a, b) {
    var a1 = parseInt(a.sizeMin || a.sizeMax, 10), a2 = parseInt(a.sizeMax || a.sizeMin, 10);
    var b1 = parseInt(b.sizeMin || b.sizeMax, 10), b2 = parseInt(b.sizeMax || b.sizeMin, 10);
    if (!a1 || !b1) { return false; }
    return a1 <= b2 && b1 <= a2; // ranges intersect
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
    this.problems.push({ type: typeKey, rose: {}, sensitivity: '', distribution: '', sizeMin: '', sizeMax: '' });
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
    card.appendChild(el('div', { class: 'aft-problem-head' }, [
      el('h3', { text: Backdrop.t('Problem @n: ', { '@n': idx + 1 }) + (def.label || '') }),
      el('button', { type: 'button', class: 'aft-remove', title: Backdrop.t('Remove'), onclick: function () { self.removeProblem(idx); } }, ['×'])
    ]));
    if (def.desc) { card.appendChild(el('p', { class: 'aft-hint', text: def.desc })); }

    // Location rose (clickable NAC octagon, matching the published forecast).
    card.appendChild(el('h4', { text: Backdrop.t('Location (aspect & elevation)') }));
    card.appendChild(this.renderRose(p));

    // Sensitivity + distribution -> likelihood.
    var likeRow = el('div', { class: 'aft-likelihood-row' });
    card.appendChild(heading('h4', Backdrop.t('Sensitivity & distribution'), 'likelihood', this.cfg));
    var grid = el('div', { class: 'aft-two-col' }, [
      this.renderChoice(Backdrop.t('Sensitivity to triggers'), this.cfg.sensitivity, p.sensitivity, function (v) {
        p.sensitivity = v; self.updateLikelihood(p, likeRow); self.renderOutput();
      }, 'sensitivity'),
      this.renderChoice(Backdrop.t('Spatial distribution'), this.cfg.distribution, p.distribution, function (v) {
        p.distribution = v; self.updateLikelihood(p, likeRow); self.renderOutput();
      }, 'distribution')
    ]);
    card.appendChild(grid);
    card.appendChild(likeRow);
    this.updateLikelihood(p, likeRow);

    // Size range.
    card.appendChild(heading('h4', Backdrop.t('Expected size (destructive)'), 'size', this.cfg));
    card.appendChild(this.renderSize(p));

    return card;
  };

  // Clickable NAC aspect/elevation rose (same octagon the published forecast
  // uses). Outer ring = below treeline, middle = near, centre = above; N up.
  Wizard.prototype.renderRose = function (p) {
    var self = this;
    var svgNS = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('class', 'aft-rose-svg');
    svg.setAttribute('viewBox', '0 0 1050 1050');
    (this.cfg.rosePaths || []).forEach(function (seg) {
      var cellKey = seg.band + ':' + seg.aspect;
      var path = document.createElementNS(svgNS, 'path');
      path.setAttribute('d', seg.d);
      function paint() {
        var fill = p.rose[cellKey] ? 'rgb(77,184,248)' : 'rgb(255,255,255)';
        path.setAttribute('style', 'cursor:pointer;stroke:rgb(81,85,88);stroke-width:10px;fill:' + fill + ';');
      }
      paint();
      path.addEventListener('click', function () {
        p.rose[cellKey] = !p.rose[cellKey];
        paint();
        self.renderOutput();
      });
      svg.appendChild(path);
    });
    var wrap = el('div', { class: 'aft-rose-wrap' });
    wrap.appendChild(svg);
    wrap.appendChild(el('p', { class: 'aft-rose-legend', text: Backdrop.t('Click sectors to mark where the problem exists. Outer ring = below treeline, middle = near treeline, centre = above treeline; north is up.') }));
    return wrap;
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

  Wizard.prototype.updateLikelihood = function (p, host) {
    host.innerHTML = '';
    var like = this.likelihoodFor(p);
    if (!like) {
      host.appendChild(el('span', { class: 'aft-like aft-like--pending', text: Backdrop.t('Likelihood: choose sensitivity and distribution') }));
      return;
    }
    host.appendChild(el('span', { class: 'aft-like', html: Backdrop.t('Likelihood (from the Fig 2 matrix): ') + '<strong>' + this.cfg.likelihoodLabels[like] + '</strong>' }));
  };

  Wizard.prototype.renderSize = function (p) {
    var self = this;
    function sizeSelect(label, key) {
      var sel = el('select', {
        class: 'aft-size-select',
        onchange: function () { p[key] = sel.value; self.clampSize(p); self.renderOutput(); }
      });
      sel.appendChild(el('option', { value: '', text: '—' }));
      self.cfg.sizes.forEach(function (s) {
        sel.appendChild(el('option', { value: s.key, text: s.label + ' — ' + s.desc, selected: p[key] === s.key ? 'selected' : null }));
      });
      return el('label', { class: 'aft-size' }, [el('span', { text: label }), sel]);
    }
    return el('div', { class: 'aft-size-row' }, [
      sizeSelect(Backdrop.t('Smallest expected'), 'sizeMin'),
      sizeSelect(Backdrop.t('Largest expected'), 'sizeMax')
    ]);
  };

  Wizard.prototype.clampSize = function (p) {
    var mn = parseInt(p.sizeMin, 10), mx = parseInt(p.sizeMax, 10);
    if (mn && mx && mn > mx) { p.sizeMin = p.sizeMax; }
  };

  /* ---- Output: hazard chart + danger + pairing + approve ---------------- */

  Wizard.prototype.renderOutput = function () {
    if (!this.outputHost) { return; }
    this.outputHost.innerHTML = '';
    if (!this.problems.length) { return; }

    this.outputHost.appendChild(this.renderPairing());

    var chartCard = el('section', { class: 'aft-card' }, [
      heading('h2', Backdrop.t('Hazard chart'), 'hazardChart', this.cfg),
      el('p', { class: 'aft-hint', text: Backdrop.t('Each problem plotted by likelihood and size (Statham Fig 3). Shading shows the danger the hazard chart implies.') })
    ]);
    chartCard.appendChild(this.renderChart());
    this.outputHost.appendChild(chartCard);

    this.outputHost.appendChild(this.renderDanger());
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

    // Background cells shaded by implied danger.
    for (var li = 1; li <= rows; li++) {           // likelihood 1..5 (bottom..top)
      for (var si = 1; si <= cols; si++) {         // size 1..5 (left..right)
        var label = self.dangerFor(String(li), String(si));
        var x = padL + (si - 1) * cw;
        var y = padT + (rows - li) * ch;
        svg.appendChild(s('rect', {
          x: x, y: y, width: cw, height: ch,
          fill: self.cfg.dangerColors[label] || '#eee', 'fill-opacity': '0.85',
          stroke: '#fff', 'stroke-width': '1'
        }));
      }
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

    // Plot each problem: a line spanning its size range at its likelihood, with a marker at the upper size.
    self.problems.forEach(function (p, idx) {
      var like = parseInt(self.likelihoodFor(p), 10);
      var mn = parseInt(p.sizeMin || p.sizeMax, 10);
      var mx = parseInt(p.sizeMax || p.sizeMin, 10);
      if (!like || !mx) { return; }
      var y = padT + (rows - like) * ch + ch / 2;
      var x1 = padL + (mn - 1) * cw + cw / 2;
      var x2 = padL + (mx - 1) * cw + cw / 2;
      if (x2 > x1) {
        svg.appendChild(s('line', { x1: x1, y1: y, x2: x2, y2: y, stroke: '#111', 'stroke-width': '2', 'stroke-linecap': 'round' }));
      }
      var g = s('g', {});
      g.appendChild(s('circle', { cx: x2, cy: y, r: '11', fill: '#fff', stroke: '#111', 'stroke-width': '2' }));
      var num = s('text', { x: x2, y: y + 4, 'text-anchor': 'middle', class: 'aft-marker' });
      num.textContent = String(idx + 1);
      g.appendChild(num);
      svg.appendChild(g);
    });

    // Legend keying markers to problems.
    var legend = el('ul', { class: 'aft-chart-legend' });
    self.problems.forEach(function (p, idx) {
      legend.appendChild(el('li', {}, [el('span', { class: 'aft-legend-num', text: String(idx + 1) }), ' ' + (self.problemByKey[p.type] ? self.problemByKey[p.type].label : '')]));
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

    var layout = el('div', { class: 'aft-danger-layout' });
    layout.appendChild(this.renderPyramid(sugg));

    // Band rows top-to-bottom (above, near, below) + overall, matching the form.
    var rows = el('div', { class: 'aft-danger-band-rows' });
    [['above', Backdrop.t('Above Treeline')], ['near', Backdrop.t('Near Treeline')], ['below', Backdrop.t('Below Treeline')], ['overall', Backdrop.t('Overall')]].forEach(function (b) {
      var label = sugg[b[0]];
      rows.appendChild(el('div', { class: 'aft-danger-band-row' }, [
        el('span', { class: 'aft-danger-band-name', text: b[1] }),
        el('span', {
          class: 'aft-danger-pill',
          style: 'background:' + (self.cfg.dangerColors[label] || '#ccc') + ';color:' + (label === 'Extreme' ? '#fff' : '#111') + ';',
          text: self.cfg.dangerLabels[label] || label
        })
      ]));
    });
    layout.appendChild(rows);
    card.appendChild(layout);
    return card;
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
      return !p.type || !this.likelihoodFor(p) || !(p.sizeMax || p.sizeMin) || !Object.keys(p.rose).some(function (c) { return p.rose[c]; });
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
        return {
          type: p.type,
          likelihood: self.likelihoodFor(p),
          sizeMin: p.sizeMin || p.sizeMax,
          sizeMax: p.sizeMax || p.sizeMin,
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
