# Avalanche Center

Installable [Backdrop CMS](https://backdropcms.org) distribution for
avalanche forecast center websites — danger maps, observations, advisories,
and glossary content, genericized from two production sites (Gulmarg and
Argentina) into one profile any center can install and configure without
forking code.

See `AVALANCHE_CENTER_DISTRIBUTION_PLAN.md` for the full implementation plan
and `CLAUDE.md` for working conventions. Both are written for people working
on this codebase, not for someone just installing a site — this file is that
second thing.

## Status

Phases 1-7 complete: the profile, its 4 custom modules, ~32 vendored contrib
modules, and 3 themes are genericized, exported, and verified end-to-end
against a real Backdrop install (fresh database through a rendered front
page with a live danger map). See the plan's phased implementation table
(§11) for what's next (Phase 8: migrating the original Gulmarg/Argentina
sites onto this distribution).

## What you need before you start

- A **Backdrop CMS 1.x core** checkout — this repo is a *profile*
  distribution, not a full Backdrop install. It does not include
  `core/`, `index.php`, or `settings.php`. Download core separately from
  [backdropcms.org/download](https://backdropcms.org/download) or clone
  [backdrop/backdrop](https://github.com/backdrop/backdrop).
- PHP 8.1, and a MySQL/MariaDB database.
- [DDEV](https://ddev.com) is the easiest way to get both of those locally
  and is what this distribution has been tested with. Any standard
  Backdrop-compatible LAMP/LEMP stack works too.

## Installing

1. **Set up a Backdrop core webroot.** If you're using DDEV in an empty
   project directory:

   ```sh
   ddev config --project-type=backdrop --docroot=. --php-version=8.1 --webserver-type=nginx-fpm
   ddev start
   ```

   Then place a full Backdrop core checkout at the project root (`core/`,
   `index.php`, `.htaccess`, `robots.txt`, `LICENSE.txt`, `sites/`, and a
   `settings.php` — Backdrop core ships a `sites/default/default.settings.php`
   you copy and rename).

2. **Add this distribution on top.** Copy this repo's `profiles/`,
   `modules/`, `themes/`, and `layouts/` directories into the Backdrop
   webroot, merging with (not replacing) the same top-level directories
   Backdrop core already ships:

   ```sh
   cp -r profiles/avalanche_center  /path/to/backdrop/profiles/
   cp -r modules/*                  /path/to/backdrop/modules/
   cp -r themes/*                   /path/to/backdrop/themes/
   cp -r layouts/*                  /path/to/backdrop/layouts/
   ```

3. **Run the installer** by visiting your site in a browser. Choose
   **Avalanche Center** as the installation profile when prompted, then let
   it run through the normal module-install steps (this needs a real
   browser — the batch progress step is JS-driven and won't complete under
   something like a plain `curl` request).

4. **Fill out the "Center setup" form.** This is the one custom install
   step the profile adds, right after Backdrop's own site-configuration
   step and before the install finishes. It asks for:

   | Field | What it does |
   |---|---|
   | Avalanche center name | Becomes the site name and shows in page titles. |
   | Language | English or Spanish. Spanish currently only records the preference — see [Known limitations](#known-limitations). |
   | Danger-scale preset | **NAC** (North American) or **SAC** (South American) — picks the color palette and travel-advice text the danger map and legend use. |
   | Map center latitude/longitude/zoom | Where the danger map centers by default. |
   | Weather service name/URL *(optional)* | Shown in the theme header/footer. |
   | Social media URLs *(optional)* | Facebook, Twitter, YouTube, Instagram, email signup — shown wherever the theme surfaces social links. |

   Submitting this form also creates one demo forecast-zone region (centered
   on the map coordinates you gave) and one demo advisory, and points the
   site's front page at that demo advisory so the danger map has something
   to show immediately. Delete both once you have real content (see below).

That's it — the site is live. All 63 modules (4 custom + ~32 contrib +
Backdrop core dependencies) enable automatically; nothing further needs
installing by hand.

## Configuring your new center

**Replace the demo content.** Go to *Content* and delete the "Demo
Avalanche Advisory" node and its "Demo Forecast Zone" term once you've
created real ones (see next point) — otherwise your real content won't be
what visitors land on.

**Add your real forecast zones.** Forecast zones are terms in the
*Forecast Region* taxonomy vocabulary (admin path: `admin/structure/taxonomy/
region`). Each term has a map-geometry field for drawing that zone's
boundary — draw it directly in the term-edit form's map widget, or paste in
WKT/GeoJSON if you already have the boundary data. Advisories reference one
of these terms to say which zone they're forecasting for.

**Publish your first real advisory.** Go to *Content > Add content >
Advisory*. This is the node type the danger map and front page read from —
danger ratings (by elevation band), the bottom-line summary, and the
forecast-region reference all live here.

**Point the front page at your own content.** The danger map block only
shows on `<front>`. If your front page should be a real advisory (or a
view, or anything else) rather than the demo one, set it under
*Configuration > System > Basic site settings > Front page*.

**Pick a theme.** Two are shipped ready to use: `avalanche_modern` (the
default) and `responsive_sac`. Both were verified to work as the active
theme end-to-end. Switch under *Appearance*; branding (logo, name/slogan
toggles, NWS name/URL, social links, elevation-band labels) is all under
that theme's own *Settings* link, not re-entered per theme — both shipped
themes' settings get populated from the setup form values so switching
later doesn't land on a blank settings page.

**Add glossary terms.** The avalanche-terms glossary at `/avalanche-terms`
is driven by the *Avalanche Terms* taxonomy vocabulary — add/edit terms
there, no code involved.

**Grant staff access.** Create user accounts and assign them the
*Editor* role (or a more specific role, if your center's permission set
needs more granularity) under *People* — Editor covers day-to-day advisory/
observation/content publishing without full administrator access.

**Changing the danger-scale preset or map defaults after install.** These
currently have no dedicated settings page — the "Center setup" form only
runs once, at install time. Change them by editing the
`avalanche_center.settings` config object directly (e.g. `drush config-set
avalanche_center.settings danger_scale SAC`, or export/edit/import the
underlying JSON under *Configuration > Development > Configuration
management*).

## Known limitations

- **Spanish isn't functional yet.** Selecting Spanish in the setup form
  only records the preference (`avalanche_center.settings.language`) — no
  `.po` translation ships, and `locale` isn't a profile dependency, so the
  interface stays in English regardless. Real Spanish support is planned
  for Phase 8, sourced from the actual Argentina site's live translations
  rather than invented ahead of time (see plan §20 for why).
- **No settings page for the danger-scale preset / map defaults** — see
  above, use `drush config-set` or config import/export instead.

## Layout

- `profiles/avalanche_center/` — the distribution profile: `.info`,
  install-time hooks, and structural config shipped to every install.
- `modules/` — custom modules (`avalanche_danger_map`, `avalanche_glossary`,
  `avalanche_social_meta`, `danger_rose`) plus ~32 shared contrib modules.
- `themes/` — `avalanche_modern` (base theme, default), `responsive_sac`,
  `responsive_bartik`.
- `layouts/` — the custom `responsive_sac` layout template (region layout
  for node/default pages) — must live at this top-level path, not nested
  inside a theme, or Backdrop's layout-plugin scanner won't find it.
