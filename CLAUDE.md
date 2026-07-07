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
- Current phase: 5 (export structural config into
  profiles/avalanche_center/config/, per Phase 4's classification).
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
  reconstructable (not just deletable) `Avalanche_LIst` view. See
  AVALANCHE_CENTER_DISTRIBUTION_PLAN.md §13-16 for the full logs.

## Conventions
- Machine names must match Gulmarg's existing field/vocab names.
- Structural config ships in profiles/avalanche_center/config/;
  site-specific config never ships.
