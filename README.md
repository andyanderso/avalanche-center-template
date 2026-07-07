# Avalanche Center

Installable [Backdrop CMS](https://backdropcms.org) distribution for
avalanche forecast center websites — danger maps, observations, advisories,
and glossary content, genericized from two production sites (Gulmarg and
Argentina) into one profile any center can install and configure without
forking code.

See `AVALANCHE_CENTER_DISTRIBUTION_PLAN.md` for the full implementation plan,
and `CLAUDE.md` for working conventions.

## Status

Phase 1 (repo skeleton + canonical baseline) — see the plan's phased
implementation table (§11) for what's next.

## Layout

- `profiles/avalanche_center/` — the distribution profile: `.info`,
  install-time hooks, and structural config shipped to every install.
- `modules/` — custom modules (`avalanche_danger_map`, `avalanche_glossary`,
  `avalanche_social_meta`, `danger_rose`) plus shared contrib modules.
- `themes/` — `avalanche_modern` (base theme), `responsive_sac`,
  `responsive_bartik`.
