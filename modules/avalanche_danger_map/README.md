# avalanche_danger_map

Generic replacement for `gulmarg_danger_map` / `argentina_danger_map`.

Leaflet-based avalanche danger rating map block. Danger scale (NAC/SAC),
colors, labels, travel advice, and legend link are config-driven via
`avalanche_center.settings` (`danger_scale`, with optional per-key
`danger_colors` / `danger_labels` / `danger_travel_advice` /
`danger_legend_url` overrides), not hardcoded per center. Map center/zoom
read from `map_center_lat` / `map_center_lng` / `map_zoom`.

Includes the PHP 8 `hook_menu_alter()` node-title-callback fix (originally
Argentina-only; now the shared default). Legend text color is computed from
each color's luminance rather than assumed by CSS position, so it stays
legible regardless of which preset (or override) is active; the map-popup
rating badge uses the same contrast computation.

Map popups show the region's NAPADS danger-level icon (`icons/level-0.svg`
through `level-5.svg`, vendored from the National Avalanche Center's public
danger-scale repo — see `icons/SOURCE.md`) alongside the color-coded rating
and travel advice text.

See `AVALANCHE_CENTER_DISTRIBUTION_PLAN.md` §6, §13, §15.
