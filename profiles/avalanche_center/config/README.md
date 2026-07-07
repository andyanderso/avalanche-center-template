# Structural config

659 files, exported in Phase 5 from the structural (identical-across-centers)
config identified in Phase 4's classification pass (`AVALANCHE_CENTER_DISTRIBUTION_PLAN.md`
§16). Ships via `config_install_default_config()` at install time — see plan §4.

Known gap: `field_map` (the `map` content type's core field) is exported
as-is but is **not currently functional** — its field type is
`gmap_polygon_field`, a module that's disabled on both reference sites and
absent from both codebases entirely. Needs a real decision (convert to
`geofield`, or drop the `map` content type) before a fresh install would
show a working map field on that content type. See plan §18 (Phase 5 log).
