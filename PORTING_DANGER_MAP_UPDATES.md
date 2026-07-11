# Porting the danger-map/popup updates to Gulmarg and Argentina

This documents the map and popup changes made to `avalanche_danger_map`
(the generic module in this distribution) so they can be manually applied
to the two **live, read-only reference codebases**:

- `~/Siesta_Solutions/Gulmarg/backdrop/gulmarg-backdrop/modules/gulmarg_danger_map/`
- `~/Siesta_Solutions/Argentina/backdrop/argentina-backdrop/modules/argentina_danger_map/`

Those two codebases are **not** touched by this repo or this guide — per
`CLAUDE.md` they're read-only references. This is a manual porting
checklist for whoever applies the equivalent changes directly to the live
sites.

§1-7 are the danger-map/popup changes. §8 is a separate theme bug (unrelated
to the danger map) that surfaced while building the distribution's Conditions
Alerts display — it's parked here because this file is already the "apply
these fixes to the live sites too" checklist.

**Scope note**: this only ports the concrete UI/UX changes and bug fixes
below. It does *not* port the config-driven preset architecture
(`avalanche_danger_map_presets()`, `avalanche_center.settings` overrides,
etc.) — that's a bigger architectural change that belongs to the eventual
full migration (plan §11, Phase 8), not this checklist. Each snippet below
is written to drop into each site's existing hardcoded-single-palette
structure as-is.

## Summary

| # | Change | Gulmarg | Argentina |
|---|--------|---------|-----------|
| 1 | Canonical NAC color correction | **Yes** — current palette has wrong hex on 3 of 6 levels | No — SAC palette is unaffected |
| 2 | PHP 8 `hook_menu_alter()` fix | **Yes** — currently missing | No — already has it |
| 3 | Legend was never rendered | **Yes** — `gulmarg_danger_map_legend()` exists but `block_view()` never calls it | No — already calls it |
| 4 | Danger-level icons in popup | Yes | Yes |
| 5 | Popup redesign (two-line label, fixed black/white text, no shadow) | Yes | Yes (needs a Spanish caption — suggested text below, please confirm wording) |
| 6 | Legend: fixed black/white text, drop `:nth-child` rules + shadow, add defensive border | Yes | Yes |
| 7 | Level 0 ("No Rating"/"Sin pronóstico") gets real travel-advice text + link | Yes | Yes (Spanish text below, please confirm wording) |
| 8 | Theme bug: `field__taxonomy_term_reference` prints attribute arrays as `Array` | **Yes** — `gulmarg_modern` | **Yes** — `argentina_modern` |

---

## 1. Canonical NAC colors (Gulmarg only)

Gulmarg's `GULMARG_DANGER_COLORS` constant has three wrong hex values
compared to the official scale
(https://github.com/NationalAvalancheCenter/north-american-public-avalanche-danger-scale/blob/main/COLORS.md,
independently re-verified). Level 0 isn't part of the official scale (site
convention only) — `#939598` is what this distribution settled on; keeping
Gulmarg's existing `#cccccc` is also fine if you'd rather not change it.

**File**: `gulmarg_danger_map.module`

```php
// Before:
define('GULMARG_DANGER_COLORS', serialize(array(
  0 => '#cccccc',
  1 => '#50b849',
  2 => '#fff200',
  3 => '#f7941d',
  4 => '#ed1c24',
  5 => '#000000',
)));

// After:
define('GULMARG_DANGER_COLORS', serialize(array(
  0 => '#939598',
  1 => '#50B848',
  2 => '#FFF200',
  3 => '#F7941E',
  4 => '#ED1C24',
  5 => '#231F20',
)));
```

---

## 2. PHP 8 `hook_menu_alter()` fix (Gulmarg only)

Argentina already has this fix; Gulmarg doesn't, so this TypeError is
presumably still logging on every observation/incident node view or edit
in production. Root cause and fix are unchanged from Argentina's version —
just add it under the `gulmarg_danger_map_` prefix.

**File**: `gulmarg_danger_map.module` — add near the top of the file:

```php
/**
 * Implements hook_menu_alter().
 *
 * PHP 8 compatibility fix. Core's node_page_title() type-hints its argument
 * as `Node`, but the menu system translates the `node/%` router item (for
 * the View default local task and the node's contextual links) using the
 * raw nid string from the URL map, not a loaded Node object. Under PHP 8
 * that raw string trips the strict type hint and throws a TypeError
 * (logged on every observation view/edit). Swap in a tolerant wrapper that
 * loads the node when needed.
 */
function gulmarg_danger_map_menu_alter(&$items) {
  if (isset($items['node/%node']['title callback']) && $items['node/%node']['title callback'] === 'node_page_title') {
    $items['node/%node']['title callback'] = 'gulmarg_danger_map_node_page_title';
  }
}

/**
 * Title callback: tolerant replacement for node_page_title().
 */
function gulmarg_danger_map_node_page_title($node) {
  if (!is_object($node)) {
    $node = node_load((int) $node);
  }
  return (is_object($node) && isset($node->title)) ? $node->title : '';
}
```

---

## 3. Gulmarg never renders its own legend (Gulmarg only)

`gulmarg_danger_map_legend()` is fully implemented but `block_view()` never
calls it — Gulmarg's map has shipped without a legend the whole time.
Argentina's `block_view()` already does `$content .= argentina_danger_map_legend();`.

**File**: `gulmarg_danger_map.module`, in `gulmarg_danger_map_block_view()`:

```php
// Before:
  $content = '<div id="' . $map_id . '" class="gulmarg-danger-map" style="width:100%;height:400px;"></div>';

  return array(
    'subject' => t('Avalanche Danger from the Gulmarg Avalanche Center'),
    'content' => $content,
  );

// After:
  $content = '<div id="' . $map_id . '" class="gulmarg-danger-map" style="width:100%;height:400px;"></div>';
  $content .= gulmarg_danger_map_legend();

  return array(
    'subject' => t('Avalanche Danger from the Gulmarg Avalanche Center'),
    'content' => $content,
  );
```

---

## 4. Danger-level icons in the popup (both sites)

Vendored from the National Avalanche Center's public repo
(https://github.com/NationalAvalancheCenter/north-american-public-avalanche-danger-scale,
"3. Icons/For web/SVG/With number/" for levels 1-5, "Standard/No-Rating.svg"
for level 0). Same license caveat as this distribution: **no LICENSE file
exists upstream**; their README frames the repo as intended for avalanche
centers to reuse, but this hasn't been independently confirmed with NAC —
worth checking before shipping to production if that matters for either
site.

1. Copy the six files from this repo's
   `modules/avalanche_danger_map/icons/level-0.svg` through `level-5.svg`
   into a new `icons/` directory in each site's module (same files, both
   sites — the icon artwork already bakes in the canonical NAC colors
   regardless of which color preset a site uses).
2. Add an icon-URL helper (adapt the prefix per site):

```php
/**
 * Builds the URL of the vendored NAPADS icon for a danger level.
 */
function gulmarg_danger_map_icon_url($level) {
  $level = (int) $level;
  if ($level < 0 || $level > 5) {
    return '';
  }
  $module_path = backdrop_get_path('module', 'gulmarg_danger_map');
  return url($module_path . '/icons/level-' . $level . '.svg');
}
```

3. Add an `'icon_url'` key to each region row in `*_get_data()`:

```php
$result[] = array(
  'tid' => $region->tid,
  'name' => $region->name,
  'geojson' => $geojson,
  'danger_rating' => $danger_rating,
  'color' => isset($colors[$danger_rating]) ? $colors[$danger_rating] : $colors[0],
  'label' => isset($labels[$danger_rating]) ? $labels[$danger_rating] : $labels[0],
  'icon_url' => gulmarg_danger_map_icon_url($danger_rating),  // <-- add this line
  'travel_advice' => isset($travel_advice[$danger_rating]) ? $travel_advice[$danger_rating] : '',
  'advisory_url' => $advisory_url,
  'expired' => !empty($advisory_info['expired']),
);
```

(Argentina's `*_get_data()` uses the same shape with different spacing —
same one-line addition.)

---

## 5. Popup redesign: icon + fixed black/white text + two-line label (both sites)

Replaces the single-line `"Danger Rating: **1 - Low**"` /
`"Peligro: **Bajo**"` with an icon next to a two-line badge, drops the
text-shadow, and uses a fixed rule (white text only on level 5 "Extreme"/
"Extremo", black everywhere else) instead of leaving it unset.

### Gulmarg — `gulmarg_danger_map.js`

```js
// Before:
          var popupHtml = '<div class="danger-map-popup">' +
            '<h4>' + region.name + '</h4>' +
            '<div class="danger-map-popup-rating" style="background:' + region.color + ';">' +
              'Danger Rating: <strong>' + region.label + '</strong>' +
            '</div>';

// After:
          var popupHtml = '<div class="danger-map-popup">' +
            '<h4>' + region.name + '</h4>' +
            '<div class="danger-map-popup-header">';

          if (region.icon_url) {
            popupHtml += '<img class="danger-map-popup-icon" src="' + region.icon_url + '" alt="">';
          }

          var textColor = (region.danger_rating === 5) ? '#fff' : '#000';
          popupHtml += '<div class="danger-map-popup-rating" style="background:' + region.color + ';color:' + textColor + ';">' +
              '<span class="danger-map-popup-rating-level">' + region.label.toUpperCase() + '</span>' +
              '<span class="danger-map-popup-rating-caption">Avalanche Danger</span>' +
            '</div>' +
          '</div>';
```

### Argentina — `argentina_danger_map.js`

Same structure; the caption needs a Spanish translation. Argentina's
existing strings use "Peligro" for "Danger" — **"Peligro de Avalancha" is
a reasonable translation for the "Avalanche Danger" caption, but please
have a Spanish speaker confirm the wording** before shipping it.

```js
// Before:
          var popupHtml = '<div class="danger-map-popup">' +
            '<h4>' + region.name + '</h4>' +
            '<div class="danger-map-popup-rating" style="background:' + region.color + ';">' +
              'Peligro: <strong>' + region.label + '</strong>' +
            '</div>';

// After:
          var popupHtml = '<div class="danger-map-popup">' +
            '<h4>' + region.name + '</h4>' +
            '<div class="danger-map-popup-header">';

          if (region.icon_url) {
            popupHtml += '<img class="danger-map-popup-icon" src="' + region.icon_url + '" alt="">';
          }

          var textColor = (region.danger_rating === 5) ? '#fff' : '#000';
          popupHtml += '<div class="danger-map-popup-rating" style="background:' + region.color + ';color:' + textColor + ';">' +
              '<span class="danger-map-popup-rating-level">' + region.label.toUpperCase() + '</span>' +
              '<span class="danger-map-popup-rating-caption">Peligro de Avalancha</span>' +
            '</div>' +
          '</div>';
```

### Both sites — CSS (`gulmarg_danger_map.css` / `argentina_danger_map.css`)

```css
/* Before: */
.danger-map-popup-rating {
  padding: 4px 8px;
  border-radius: 3px;
  color: #fff;
  font-size: 0.95em;
  margin-bottom: 8px;
  text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

/* After: */
.danger-map-popup-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}
.danger-map-popup-icon {
  flex: none;
  height: 30px;
  width: auto;
}
.danger-map-popup-rating {
  flex: 1;
  padding: 4px 8px;
  border-radius: 3px;
  font-size: 0.95em;
  display: flex;
  flex-direction: column;
  line-height: 1.25;
  border: 1px solid rgba(128,128,128,0.35);
}
.danger-map-popup-rating-level {
  font-weight: bold;
  text-transform: uppercase;
  font-size: 1.05em;
}
.danger-map-popup-rating-caption {
  font-size: 0.8em;
}
```

Note the icons have different natural aspect ratios (levels 0-2 are ~1:1;
levels 4-5 widen to ~1.39:1 as more triangles stack in the icon design) —
`height: 30px; width: auto` keeps them from stretching/squashing; don't
set a fixed `width` on `.danger-map-popup-icon`.

---

## 6. Legend: fixed text color, drop `:nth-child`/shadow, add defensive border (both sites)

Both sites currently pick legend text color by CSS position
(`:first-child`, `:nth-child(2)`/`(3)`/`(4)`). That's fragile — it assumes
a specific color always lands in a specific position, which happens to
work today only because neither site's palette has changed. Interestingly,
**Gulmarg's own CSS comment already has a bug**: `:nth-child(2)` is
commented `/* Low danger (yellow background) needs dark text */`, but
Gulmarg's 2nd item (level 1, "Low") is green, not yellow — the comment
and/or the rule appears to be a leftover from an earlier palette. Replacing
with a fixed rule sidesteps this entirely.

### Both sites — `.module` file, in `*_legend()`

```php
// Gulmarg — before:
  foreach ($labels as $level => $label) {
    $out .= '<span class="legend-item" style="background:' . $colors[$level] . ';">' . check_plain($label) . '</span>';
  }

// Gulmarg — after:
  foreach ($labels as $level => $label) {
    $text_color = ($level === 5) ? '#fff' : '#000';
    $out .= '<span class="legend-item" style="background:' . $colors[$level] . ';color:' . $text_color . ';">' . check_plain($label) . '</span>';
  }
```

(Argentina's `*_legend()` has the identical loop shape — same fix.)

### Both sites — CSS

```css
/* Before: */
.danger-map-legend .legend-item {
  flex: 1;
  padding: 5px 4px;
  color: #fff;
  font-weight: bold;
  text-align: center;
  text-shadow: 0 1px 2px rgba(0,0,0,0.6);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
  transition: opacity 0.15s;
}
.danger-map-legend .legend-item:first-child {
  color: #555;
  text-shadow: none;
}
/* Gulmarg also has a :nth-child(2) rule; Argentina has :nth-child(3) and :nth-child(4) — delete all of these */

/* After: */
.danger-map-legend {
  border: 1px solid rgba(128,128,128,0.35);   /* add this line to the existing .danger-map-legend rule */
}
.danger-map-legend .legend-item {
  flex: 1;
  padding: 5px 4px;
  font-weight: bold;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
  transition: opacity 0.15s;
  box-shadow: inset -1px 0 0 rgba(128,128,128,0.35);
}
.danger-map-legend .legend-item:last-child {
  box-shadow: none;
}
```

Delete the old `:first-child` / `:nth-child(...)` color overrides entirely
— the fixed rule in the PHP function now sets `color` inline per item, and
the border addition is defensive: level 5's near-black fill has no edge of
its own, so on a dark surrounding page it can blend in completely and
disappear. Doesn't matter on either site's current (light) theme, but it's
a one-line, no-downside fix.

---

## 7. Level 0 ("No Rating"/"Sin pronóstico") gets real travel advice (both sites)

Currently `travel_advice[0]` is an empty string on both sites, so no advice
block renders at all when a region has no current rating. Add a message
that explains why, with a link to whatever advisory is available (even an
expired one — "more information" still make sense pointing at the last
thing published).

### Gulmarg — `gulmarg_danger_map_get_data()`

```php
// Before:
    $advisory_info = gulmarg_danger_map_get_advisory_info($region->tid);
    $danger_rating = $advisory_info['rating'];
    $advisory_url = $advisory_info['url'];

    $result[] = array(
      'tid' => $region->tid,
      'name' => $region->name,
      'geojson' => $geojson,
      'danger_rating' => $danger_rating,
      'color' => isset($colors[$danger_rating]) ? $colors[$danger_rating] : $colors[0],
      'label' => isset($labels[$danger_rating]) ? $labels[$danger_rating] : $labels[0],
      'travel_advice' => isset($travel_advice[$danger_rating]) ? $travel_advice[$danger_rating] : '',
      'advisory_url' => $advisory_url,
      'expired' => !empty($advisory_info['expired']),
    );

// After:
    $advisory_info = gulmarg_danger_map_get_advisory_info($region->tid);
    $danger_rating = $advisory_info['rating'];
    $advisory_url = $advisory_info['url'];

    if ($danger_rating === 0) {
      $region_travel_advice = t('Insufficient data exists to issue a danger rating.');
      if (!empty($advisory_url)) {
        $region_travel_advice .= ' <a href="' . check_url($advisory_url) . '">' . t('Get more information') . '</a>.';
      }
    }
    else {
      $region_travel_advice = isset($travel_advice[$danger_rating]) ? $travel_advice[$danger_rating] : '';
    }

    $result[] = array(
      'tid' => $region->tid,
      'name' => $region->name,
      'geojson' => $geojson,
      'danger_rating' => $danger_rating,
      'color' => isset($colors[$danger_rating]) ? $colors[$danger_rating] : $colors[0],
      'label' => isset($labels[$danger_rating]) ? $labels[$danger_rating] : $labels[0],
      'icon_url' => gulmarg_danger_map_icon_url($danger_rating),
      'travel_advice' => $region_travel_advice,
      'advisory_url' => $advisory_url,
      'expired' => !empty($advisory_info['expired']),
    );
```

### Argentina — `argentina_danger_map_get_data()`

Same structure. Argentina already uses "Más información" as its
"more information" link text elsewhere in the JS — **suggested Spanish
wording below, please have a Spanish speaker confirm before shipping**:

```php
    if ($danger_rating === 0) {
      $region_travel_advice = 'No existen datos suficientes para emitir un pronóstico de peligro.';
      if (!empty($advisory_url)) {
        $region_travel_advice .= ' <a href="' . check_url($advisory_url) . '">Más información</a>.';
      }
    }
    else {
      $region_travel_advice = isset($travel_advice[$danger_rating]) ? $travel_advice[$danger_rating] : '';
    }
```

### Both sites — JS: skip the redundant bottom link for level 0

Since level 0's travel advice now carries its own "more information" link
inline, suppress the separate bottom link block for that case so it isn't
shown twice:

```js
// Before:
          if (region.advisory_url) {
            var linkText = region.expired ? 'Get more information' : 'Read Full Advisory';
            popupHtml += '<div class="danger-map-popup-link">' +
              '<a href="' + region.advisory_url + '">' + linkText + ' &raquo;</a>' +
            '</div>';
          }

// After:
          if (region.advisory_url && region.danger_rating !== 0) {
            var linkText = region.expired ? 'Get more information' : 'Read Full Advisory';
            popupHtml += '<div class="danger-map-popup-link">' +
              '<a href="' + region.advisory_url + '">' + linkText + ' &raquo;</a>' +
            '</div>';
          }
```

(Argentina's equivalent block uses "Más información" / "Leer boletín
completo" for `linkText` — same one-line `&& region.danger_rating !== 0`
addition to the `if`.)

---

## 8. Theme bug: taxonomy-term-reference fields print `Array` (both sites)

Not a danger-map change — this surfaced while wiring up the distribution's
Conditions Alerts display, but it's a real, pre-existing bug on both live
sites, so it belongs on the port list.

`gulmarg_modern_field__taxonomy_term_reference()` (and Argentina's
`argentina_modern_field__taxonomy_term_reference()`) concatenate
`$variables['item_attributes'][$delta]` and `$variables['attributes']`
directly into the markup string. Under Backdrop those are **arrays**, not
pre-rendered attribute strings, so PHP emits an `Array to string conversion`
warning and drops a literal `Array` into the `<li>`/`<div>` tag:

```html
<li class="taxonomy-term-reference-0"Array>
<div class="field ... clearfix"Array>
```

This fires for **every** taxonomy-term-reference field this theme renders
(tags, region references, conditions alerts, etc.), so it's likely already
logging warnings in production wherever such a field is displayed. It just
isn't very visible because the stray `Array` lands inside a tag rather than
in readable text. The `responsive_sac` and `responsive_bartik` copies on
each site carry the same function and same bug if they're ever made active.

**File**: `themes/gulmarg_modern/template.php` (Argentina:
`themes/argentina_modern/template.php`) — same line numbers (~196, ~203) on
both sites.

```php
// Before:
  foreach ($variables['items'] as $delta => $item) {
    $item_attr = isset($variables['item_attributes'][$delta]) ? $variables['item_attributes'][$delta] : '';
    $output .= '<li class="taxonomy-term-reference-' . $delta . '"' . $item_attr . '>' . backdrop_render($item) . '</li>';
  }
  $output .= '</ul>';

  $classes_array = isset($variables['classes_array']) && is_array($variables['classes_array']) ? $variables['classes_array'] : array();
  $clearfix = in_array('clearfix', $classes_array) ? '' : ' clearfix';
  $output = '<div class="' . $variables['classes'] . $clearfix . '"' . $variables['attributes'] . '>' . $output . '</div>';

// After:
  foreach ($variables['items'] as $delta => $item) {
    $item_attr = isset($variables['item_attributes'][$delta]) ? $variables['item_attributes'][$delta] : '';
    // Backdrop passes attributes as arrays; coerce to an attribute string.
    if (is_array($item_attr)) {
      $item_attr = backdrop_attributes($item_attr);
    }
    $output .= '<li class="taxonomy-term-reference-' . $delta . '"' . $item_attr . '>' . backdrop_render($item) . '</li>';
  }
  $output .= '</ul>';

  $classes_array = isset($variables['classes_array']) && is_array($variables['classes_array']) ? $variables['classes_array'] : array();
  $clearfix = in_array('clearfix', $classes_array) ? '' : ' clearfix';
  $attributes = isset($variables['attributes']) ? $variables['attributes'] : '';
  if (is_array($attributes)) {
    $attributes = backdrop_attributes($attributes);
  }
  $output = '<div class="' . $variables['classes'] . $clearfix . '"' . $attributes . '>' . $output . '</div>';
```

(Use each site's own function-name prefix; the body is identical.)

### Not a port item: the Conditions Alerts formatter

For the record, so nobody "ports" it by mistake: this distribution also
swapped the Conditions Alerts field's display formatter from
`hs_taxonomy_term_reference_hierarchical_text` to a small custom
grouped-plain-text formatter. That was **only** necessary because the
distribution doesn't vendor the `hierarchical_select` module, so its
formatter didn't exist and the field fell back to linked term output. The
**live sites do have `hierarchical_select` installed**, so their Conditions
Alerts display already works — no change needed there unless you ever remove
that module.

---

## Suggested order of operations

1. Colors + PHP8 fix + missing legend call (Gulmarg-only, §1-3) — small,
   independent, no visual risk.
2. Icons (§4) — vendor files, add one PHP function, one array key per
   site.
3. Popup redesign (§5) — JS + CSS together per site; test in a browser
   before moving on, since this is the most visible change.
4. Legend fix (§6) — JS-free, CSS + one PHP loop per site.
5. No-Rating advice (§7) — needs a native Spanish speaker to confirm the
   Argentina wording before it ships.
6. Theme attribute-array fix (§8) — independent of everything above; a
   two-spot edit in each site's `*_modern/template.php`.

Each step is independently testable and revertable — no need to do all
eight in one sitting per site.
