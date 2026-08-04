# Gulmarg → Template Migration Runbook

Migrate the live Gulmarg Avalanche Center site onto the avalanche-center-template
distribution and deploy it to Gulmarg's HostingRaja server, preserving all content.

## Approach: "lift and reconcile"

Backdrop keeps **content in the database** and **structure in `files/config`**.
So the migration is: run **Gulmarg's database + uploaded files** (the content) on
the **template's codebase + config** (the structure). Because the config is in
files, swapping the config directory *automatically* gives Gulmarg's content the
template's clean structure — and drops the Sierra-Avalanche-Center leftover
blocks/layouts/views and dead Commerce/fwp/comment config for free. We only add
back config for the three keep-extras and set Gulmarg's real site values.

- **Core version:** build the target on **1.34.0** (matching Gulmarg's DB) so no
  `update.php` is needed during the risky content step. Update to 1.35 later as a
  separate maintenance task.
- **Local-first:** build + verify in DDEV, then stage on HostingRaja, then cut over.
  The live site stays untouched until the final flip.

## Scope

- **Migrate (content):** 493 advisories, 40 observations, 24 pages, ~18 misc nodes
  (incl. **3 media galleries, 3 map nodes, 2 class nodes**), glossary (71 terms) +
  other taxonomy, the **1 forecast region**, **3 users**, uploaded files, URL aliases.
- **Add back to template config** (template lacks these): `field_map` (+ `node/map`
  instance), file/media entity config (`file_display.*`, media fields, media_gallery
  field instances), `class` node fields + `class_type` vocab.
- **Drop:** Commerce (0 data), field-work-plan/trip-planning (0 data), comments (0),
  legacy OpenLayers views, junk views, all 17 SAC custom blocks, the home/dashboard
  layouts (use the template's), SAC theme cruft.

---

## Phase 0 — Backups & prerequisites
- [ ] Gulmarg DB dump — `ddev export-db` from `gulmarg-backdrop` (READ-ONLY source).
- [ ] Gulmarg uploaded files snapshot — `files/` minus `config_*`, `css`, `js`, `styles`.
- [ ] Template repo checked out; Backdrop **core 1.34.0** downloaded.
- [ ] New DDEV project for the target (e.g. `gulmarg-migrate`) — never touch the source.

## Phase 1 — Build the target codebase (local DDEV)
- [ ] New DDEV project; PHP to match HostingRaja (confirm; 7.4/8.x).
- [ ] Assemble docroot: Backdrop core 1.34.0 + template distribution
      (`modules/ themes/ layouts/ profiles/` rsynced from the template repo).
- [ ] `settings.php` incl. **`$settings['backdrop_drupal_compatibility'] = TRUE;`**.

## Phase 2 — Seed clean template config + Gulmarg site values
- [ ] Fresh template install → generates the template's config in `files/config`
      (clean blocks/layouts/views/structure).
- [ ] Run the Center-setup form with **Gulmarg's real values**: name
      "Gulmarg Avalanche Center", **NAC / English**, map center ≈ 34.05 N / 74.38 E
      (confirm), logo/branding.
- [ ] Note the demo content this creates — it's replaced wholesale in Phase 4.

## Phase 3 — Add-back config for the 3 keep-extras
- [ ] Extract from Gulmarg config and place into the target config dir:
      `field.field.field_map` + `field.instance.node.map.field_map`;
      `file_display.*` + media fields (`media_title`, `media_description`,
      `field_file_image_alt_text`/`title_text`) + `media_gallery` field instances;
      `node/class` field instances + `class_type` vocab.
- [ ] **Sanitize `field_map`:** its `gmap_polygon_field` type is unresolved in the
      template — retype to a supported geo field/widget (e.g. leaflet_widget) so the
      3 map nodes render.

## Phase 4 — Content lift (DB + files) + reconciliation
- [ ] Import Gulmarg's DB into the target DB (drop + recreate + load). The template
      config in `files/config` survives (it's files, not DB).
- [ ] Copy Gulmarg's uploaded `files/` into the target `files/` (preserve paths so
      `file_managed` URIs resolve).
- [ ] **Reconciliation checklist** (iterate in DDEV):
  - [ ] `drush updb` — clear any pending 1.34 updates (should be clean, same core).
  - [ ] **Field rename:** Gulmarg DB has `field_*_field_name`; the template renamed
        it to `field_observer_name` (collision fix). Rename the DB tables/columns
        (`field_name` → `field_observer_name`) *or* keep `field_name` in target config.
  - [ ] Set `system.core`: `site_name`, `site_frontpage` (Gulmarg's real front page),
        timezone — overwrite the Phase-2 demo values.
  - [ ] Confirm the `region` vocabulary = Gulmarg's 1 real region (from the DB), and
        the danger map front-page block points at it.
  - [ ] `menu_links` table: drop/repoint SAC + dead-path links; keep real ones.
  - [ ] (Optional cleanup) drop orphaned dead-field data tables
        (`field_data_field_*` for Commerce/fwp/comment).
  - [ ] `drush cc all` (registry + all caches).

## Phase 5 — Local verification
- [ ] Advisories: spot-check oldest / newest / random of the 493 → 200, danger rose
      + elevation ratings render.
- [ ] Front page danger map renders with the region + correct current rating.
- [ ] Glossary `/avalanche-terms` = 71 terms; 40 observations render.
- [ ] **Keep-extras:** 3 media galleries show images; 3 map nodes show `field_map`;
      2 class nodes render.
- [ ] 3 users log in; roles/permissions intact; `/admin/people/create` 200 (no
      `field_default_token` fatal — fix is in the template).
- [ ] `og:description` on an advisory = danger-scale travel advice (new behavior).
- [ ] URL aliases preserved (advisory URLs match production); no fatals in watchdog.

## Phase 6 — Deploy to HostingRaja (stage first)
- [ ] Determine access: **SSH** (automate like the Linode) vs **cPanel/phpMyAdmin/FTP**
      (manual). Confirm PHP (7.4+/8.x) + MySQL versions.
- [ ] **Staging** (subdomain or subdir, NOT over live):
  - [ ] Upload codebase (git/rsync if SSH; zip+FTP if cPanel).
  - [ ] Create DB + user; import the migrated DB (SSH `mysql` for the large dump, or
        phpMyAdmin — watch upload-size caps; gzip / split if needed).
  - [ ] Upload `files/`.
  - [ ] `settings.php` with HostingRaja DB creds + `backdrop_drupal_compatibility=TRUE`
        + `trusted_host_patterns`.
  - [ ] Re-run the Phase 5 checks on the real host.
- [ ] **Cutover:** point the Gulmarg domain/docroot at the migrated site; TLS
      (Let's Encrypt / HostingRaja SSL).

## Phase 7 — Post-cutover
- [ ] Gulmarg replaces the SAC-leftover blocks with real Kashmir content on the clean
      template (their newsletter, local weather links, classes).
- [ ] Optional: update core 1.34 → 1.35 as a separate step.
- [ ] Monitor logs for a few days.

## Risks & rollback
- Original Gulmarg site stays live and untouched until cutover is verified → rollback
  is just pointing the domain back.
- **Field rename** mismatch → advisories miss the observer name; handled in Phase 4.
- **`gmap_polygon_field`** on `field_map` → map nodes; handled by retyping in Phase 3.
- **DB import limits** on cPanel/phpMyAdmin → use SSH `mysql` or split the dump.
- Building on **1.34** (not 1.35) avoids a schema update during the content lift; the
  1.35 bump becomes an isolated later step.
