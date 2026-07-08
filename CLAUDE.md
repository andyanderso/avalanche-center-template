# Avalanche Center Distribution

Backdrop CMS distribution merging two near-identical avalanche center
sites (Argentina + Gulmarg) into one generic installable profile.
Full spec: AVALANCHE_CENTER_DISTRIBUTION_PLAN.md — read it before
any structural work. PORTING_DANGER_MAP_UPDATES.md documents the
avalanche_danger_map map/popup UI changes as a manual checklist for
applying the equivalent fixes to the live Gulmarg/Argentina sites.

## Reference codebases (READ-ONLY — never edit these)
- Gulmarg (canonical baseline): ~/Siesta_Solutions/Gulmarg/backdrop/gulmarg-backdrop/
- Argentina(Spanish Language Version with Multiple forecast regions): ~/Siesta_Solutions/Argentina/backdrop/argentina-backdrop/

## Key facts
- Backdrop 1.x (Drupal 7 API-compatible). Config = JSON files in files/config_active/.
- Baseline decisions: English/NAC preset, Gulmarg content model canonical.
- danger_rose module is identical in both references — copy as-is.
- 3 site-prefixed custom modules exist per site, all need genericizing:
  *_danger_map, *_glossary, *_social_meta (gulmarg_social_meta /
  argentina_social_meta — token provider for Metatag og:image/twitter:image,
  no admin form). `danger_rose` is a 4th custom module but is shared/unprefixed
  and already identical — copy as-is, no genericizing needed.
  See AVALANCHE_CENTER_DISTRIBUTION_PLAN.md §7.
- Canonical NAC danger-scale colors (levels 1-5 only; there is no official
  level 0) come from https://github.com/NationalAvalancheCenter/north-american-public-avalanche-danger-scale/blob/main/COLORS.md
  — use these exact hex values, not either site's hardcoded module palette.
  Re-verified directly against this URL during Phase 3 double-check; matches
  what's shipped exactly. See AVALANCHE_CENTER_DISTRIBUTION_PLAN.md §6.
- Both sites use a single unified `observation` content type at
  /node/add/observation. The old /node/add/snowobs and /node/add/avyobs
  paths are vestigial theme-setting defaults (gulmarg_modern.info,
  responsive_sac.info on both sites) — don't carry them forward.
- Site-specifics go through 3 layers: install-profile setup form,
  admin settings, content. Never hardcode a center's values.
- All display strings through t(). Spanish = translation, not fork.

## Environment
- DDEV for local; this repo will get its own DDEV project for the
  greenfield demo install (Phase 7).
- Current phase: 7 done (greenfield validation: fresh DDEV install of a
  "demo" center end-to-end — the first real test of everything Phases 1-6
  built, against `~/Siesta_Solutions/avalanche-center-demo/`, its own
  DDEV project, not part of this git repo). Found and fixed 10 real bugs
  invisible to static analysis: `leaflet_widget`'s empty
  `hook_requirements()`, `danger_rose` never actually copied, ~32 contrib
  modules + `email` never vendored, `css_injector`'s premature directory
  check, the unfetchable `gmap_polygon_field` dependency, a `field_name`/
  `field_collection_item` naming collision (renamed to
  `field_observer_name`, 7 role files' stale permission strings included),
  `danger_rose`'s NOT-NULL rose-field columns, anonymous/authenticated
  role permissions silently discarded by `config_install_default_config()`
  never overwriting core's own empty defaults for those two role names, no
  theme ever enabled/defaulted, a missing `search` module dependency
  (`avalanche_modern` calls `backdrop_get_form('search_form')`
  unconditionally), the `responsive_sac` layout template living somewhere
  Backdrop's plugin scanner never looks (relocated from
  `themes/responsive_sac/layouts/` to top-level `layouts/`), and the
  danger map block having no front-page configuration to actually render
  under. Full install now reproducible end-to-end: all 63 modules enable,
  all 4 key pages (`/`, `/node/1`, `/avalanche-terms`,
  `/node/add/observation`) return 200, danger map renders on the front
  page with the demo forecast zone. Also verified `responsive_sac` works
  correctly when switched to be the *default* theme (not just enabled to
  supply the layout template) - all 4 pages 200, no errors, assets load,
  danger map unaffected; reverted back to `avalanche_modern` as canonical
  default afterward. Also verified the SAC color preset live (switched
  `danger_scale` to SAC, confirmed the correct hex values reached the
  rendered map/legend HTML, reverted to NAC). Investigated Spanish support
  and confirmed it can't go further than Phase 6 left it yet: neither
  reference codebase ships a `.po` file (Backdrop's `locale` module stores
  translated strings in the database, not in any file this static export
  can see), so building one now would mean inventing unverified
  translations — real Spanish support is deferred to Phase 8, against a
  live DB dump of Argentina's actual site. See
  AVALANCHE_CENTER_DISTRIBUTION_PLAN.md §20 for the full log. Phase 7 is
  now fully closed out.
  Phase 1 done: repo skeleton scaffolded; structural node-type list
  corrected from 2 to 22 types (independently re-verified against
  node.type.*.json on both sites); NAC level-0 grey set to `#939598`.
  Phase 2 done: avalanche_modern merged from gulmarg_modern (renamed,
  t()-wrapped, canonical NAC colors applied); responsive_sac/
  responsive_bartik copied with vestigial/instance-specific files
  stripped — a post-Phase-3 double-check found this pass had been
  interrupted mid-way (battery died) and missed a whole separate body of
  work on responsive_sac's larger advisory templates/inc file; that's now
  fixed (see §14's "Phase 2 remediation" addendum). Phase 3 done:
  avalanche_danger_map (config-driven NAC/SAC presets, PHP-8
  hook_menu_alter fix, fixed black/white text rule for legend + popup —
  white only on level 5 "Extreme" — replacing an earlier computed-contrast
  approach), avalanche_glossary (single /avalanche-terms path, no language
  fork), avalanche_social_meta (fallback logo filename now config, not
  hardcoded) genericized under modules/; map popup later got NAPADS danger
  icons + a No-Rating travel-advice message (see
  PORTING_DANGER_MAP_UPDATES.md to carry these back to the live sites).
  Phase 4 done: full config classification against the actual ~1,650
  active config files on both sites (not just §5's summary) — corrected
  the role-permission-cleanup scope from 2 roles to 9 of 12, found dead
  config from fully-disabled Commerce/registration modules and legacy
  pre-Leaflet OpenLayers danger-map views that need excluding, and a
  reconstructable (not just deletable) `Avalanche_LIst` view. Phase 5
  done: 659 structural config files exported to
  profiles/avalanche_center/config/ — beyond the mechanical copy, found
  and fixed stale `gulmarg_danger_map`/`gulmarg_topo_satellite` machine-name
  references (would have silently broken a block placement + map
  displays), several more site-specific default values/allowed-values
  lists and view page titles Phase 4's family-level pass couldn't catch,
  and 8 more disabled-module field/widget references beyond
  Commerce/registration — all but one (`field_map`'s `gmap_polygon_field`
  type, flagged unresolved) fixed with safe fallback widgets. Phase 6
  done: `avalanche_center.profile`'s "Center setup" install-task form +
  `avalanche_center.install`'s demo-content creation (one forecast zone +
  one advisory) — moved the setup form's config-writing out of
  hook_install() into the form's own submit handler once it became clear
  hook_install() runs before the custom install-task form does, so it
  can't see values that haven't been submitted yet. **Entirely unverified
  against a live install** — no DDEV environment exists yet; every fix
  came from reading Backdrop core's function signatures, not from testing.
  Phase 7 is where this code ran for the first time (see above). See
  AVALANCHE_CENTER_DISTRIBUTION_PLAN.md §13-20 for the full logs.

## Conventions
- Machine names must match Gulmarg's existing field/vocab names.
- Structural config ships in profiles/avalanche_center/config/;
  site-specific config never ships.
