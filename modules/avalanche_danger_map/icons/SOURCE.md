# Source

Vendored from the National Avalanche Center's public repo:
https://github.com/NationalAvalancheCenter/north-american-public-avalanche-danger-scale
("3. Icons/For web/SVG/With number/" for levels 1-5, "3. Icons/For web/SVG/Standard/No-Rating.svg" for level 0 — no "with number" variant exists for the no-rating icon).

`level-N.svg` corresponds to NAPADS danger level N (0 = No Rating, 1 = Low,
2 = Moderate, 3 = Considerable, 4 = High, 5 = Extreme). Colors are baked into
the SVGs and match the canonical NAC palette used elsewhere in this module
(`avalanche_danger_map_presets()`).

No LICENSE file exists in the upstream repo as of this writing. The
project's README frames the repo as intended for avalanche centers to
download and reuse for exactly this purpose (public danger-scale display).
If reuse terms matter for your deployment, confirm with the National
Avalanche Center (nac@avalanche.org) before redistributing.
