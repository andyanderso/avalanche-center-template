timezones.geojson — IANA timezone boundaries (current/"now" variant), from
evansiroky/timezone-boundary-builder release 2026c (timezones-now.geojson),
simplified with mapshaper (-simplify 4% keep-shapes, precision 0.01) to ~1.7MB.
The "now" dataset merges zones that currently share identical UTC offset + DST
rules into one representative zone, so a point resolves to a zone with the
correct current offset/DST (the name may be a representative, e.g. an Argentine
point resolves to America/Sao_Paulo). Used by avalanche_fwp_timezone_from_coords().
Regenerate on a new tz release; re-run mapshaper.
