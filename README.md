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

These steps assume [DDEV](https://ddev.com) and were tested exactly as
written. `$PROJECT` is wherever you want the site to live and `$REPO` is
wherever you've cloned/downloaded *this* repo — set them once and every
command below is copy-paste as-is.

```sh
export PROJECT=~/sites/my-avalanche-center   # change to taste
export REPO=/path/to/avalanche-center-template   # this repo's checkout
```

### 1. Create an empty DDEV project

```sh
mkdir -p "$PROJECT"
cd "$PROJECT"
ddev config --project-type=backdrop --docroot=. --php-version=8.1 --webserver-type=nginx-fpm
ddev start
```

This alone creates `settings.php`/`settings.ddev.php` (and a `drushrc.php`)
for you — don't copy those from anywhere else. `$PROJECT` should now
contain just `.ddev/` plus those files, nothing from Backdrop itself yet.

### 2. Add Backdrop CMS core

This repo is a *profile* distribution, not a full Backdrop install — it
has no `core/`, `index.php`, or `sites/` of its own. Clone core into a
scratch location, copy the pieces you need into `$PROJECT`, then discard
the scratch clone:

```sh
git clone --depth 1 https://github.com/backdrop/backdrop.git /tmp/backdrop-core
cp -r /tmp/backdrop-core/core        "$PROJECT/"
cp -r /tmp/backdrop-core/sites       "$PROJECT/"
cp    /tmp/backdrop-core/index.php   "$PROJECT/"
cp    /tmp/backdrop-core/.htaccess   "$PROJECT/"
cp    /tmp/backdrop-core/robots.txt  "$PROJECT/"
rm -rf /tmp/backdrop-core
```

### 3. Add this distribution

`mkdir -p` first so every `cp` below has a real directory to land in —
skipping this step is what silently dumps everything as loose siblings at
the project root instead of nesting it correctly (ask us how we know):

```sh
mkdir -p "$PROJECT/modules" "$PROJECT/themes" "$PROJECT/profiles" "$PROJECT/layouts"
cp -r "$REPO"/modules/*             "$PROJECT/modules/"
cp -r "$REPO"/themes/*              "$PROJECT/themes/"
cp -r "$REPO"/layouts/*             "$PROJECT/layouts/"
cp -r "$REPO"/profiles/avalanche_center  "$PROJECT/profiles/"
```

**Checkpoint — before moving on, confirm `$PROJECT` looks like this**
(`ls "$PROJECT"`):

```
core/  layouts/  modules/  profiles/  settings.ddev.php  sites/
index.php  .htaccess  robots.txt  settings.php  themes/
```

`modules/`, `themes/`, `layouts/`, and `profiles/` must each be a
*directory containing things* (`ls "$PROJECT/profiles"` should show
`avalanche_center`, not `.info`/`.install`/`.profile` files directly at
`$PROJECT`'s own root). If `avalanche_center.info` or a module name like
`leaflet` shows up loose at `$PROJECT`'s top level instead of nested one
level down, something in step 3 landed in the wrong place — delete
`modules/ themes/ profiles/ layouts/` under `$PROJECT` and redo step 3.

### 4. Run the installer

Visit `https://my-avalanche-center.ddev.site` (or whatever `ddev
describe` reports) in a real browser — the batch install step is
JS-driven and won't complete under something like a plain `curl` request.
Choose **Avalanche Center** as the installation profile when prompted,
fill out Backdrop's own site-configuration step (creates your admin
account), then you'll land on this profile's one extra step:

### 5. Fill out the "Center setup" form

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
