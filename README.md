# Avalanche Center

[![Repo: andyanderso/avalanche-center-template](https://img.shields.io/badge/GitHub-avalanche--center--template-181717?logo=github)](https://github.com/andyanderso/avalanche-center-template)

Installable [Backdrop CMS](https://backdropcms.org) distribution for
avalanche forecast center websites — danger maps, observations, advisories,
and glossary content, genericized from two production sites (Gulmarg and
Argentina) into one profile any center can install and configure without
forking code.

See `AVALANCHE_CENTER_DISTRIBUTION_PLAN.md` for the full implementation plan
and `CLAUDE.md` for working conventions. Both are written for people working
on this codebase, not for someone just installing a site — this file is that
second thing.

> **Handing a ready-to-install site to a non-technical center owner?** Share
> [`SETUP-GUIDE.md`](SETUP-GUIDE.md) instead of this file. It's a standalone,
> email-friendly walkthrough that starts at the installer (this README's steps
> 4–5) and covers configuring the center — no developer/hosting steps.

## Status

Phases 1-8 complete: the profile, its 4 custom modules, ~32 vendored contrib
modules, and 3 themes are genericized, exported, and verified end-to-end
against a real Backdrop install (fresh database through a rendered front
page with a live danger map). Both danger-scale presets (NAC and SAC) are
verified live, and the **Spanish install option is fully functional** —
choosing Spanish at setup produces a Spanish-language site end-to-end
(interface strings, navigation, reference pages, danger scale, and demo
content), sourced from the real Argentina site's translations rather than
invented. See the plan's phased implementation table (§11) for the full
history.

## What you need before you start

- PHP 8.1, and a MySQL/MariaDB database.
- [DDEV](https://ddev.com) is the easiest way to get both of those locally
  and is what this distribution has been tested with. Any standard
  Backdrop-compatible LAMP/LEMP stack works too.

You don't need to download Backdrop core ahead of time — this repo is a
*profile* distribution, not a full Backdrop install, and the install steps
below fetch core for you (step 2).

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
has no `core/`, `index.php`, or `sites/` of its own. Clone a **stable**
release of core into a scratch location, copy the pieces you need into
`$PROJECT`, then discard the scratch clone:

```sh
# Pin to the current stable Backdrop release. Check the latest tag at
# https://github.com/backdrop/backdrop/releases (1.34.3 at time of writing).
BACKDROP_VERSION=1.34.3
git clone --depth 1 --branch "$BACKDROP_VERSION" https://github.com/backdrop/backdrop.git /tmp/backdrop-core
cp -r /tmp/backdrop-core/core        "$PROJECT/"
cp -r /tmp/backdrop-core/sites       "$PROJECT/"
cp    /tmp/backdrop-core/index.php   "$PROJECT/"
cp    /tmp/backdrop-core/.htaccess   "$PROJECT/"
cp    /tmp/backdrop-core/robots.txt  "$PROJECT/"
rm -rf /tmp/backdrop-core
```

> **Always pass `--branch <version>`.** A plain `git clone …/backdrop.git`
> checks out the **development branch** (e.g. `1.35.x-dev`), not a released
> version — you do not want an unreleased dev snapshot on a real site.
> To move an existing site to a newer stable release later, see
> "Using this repo as your production code base" below.

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
(use `ls -a "$PROJECT"` so hidden files show):

```
core/  layouts/  modules/  profiles/  settings.ddev.php  sites/
index.php  .htaccess  robots.txt  settings.php  themes/
```

The trailing `/` just marks directories — it's not part of the name. Two
things you may also see that are fine: `.htaccess` is hidden from a plain
`ls` (that's why the checkpoint uses `ls -a`), and DDEV leaves a
`drushrc.php` here from step 1 — neither is a problem.

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

> **On the first "Choose language" screen, pick English** — even if you
> want a Spanish site. That screen sets only the *installer's own* UI
> language; picking a non-English language there makes Backdrop core try to
> download a matching installer translation and emits harmless
> `Attempt to read property "langcode" on bool` warnings. **The site's
> language is chosen later, in the "Center setup" form (step 5)** — that's
> what actually produces a Spanish site. If you already picked Español here,
> just reload at `.../core/install.php?langcode=en` to switch back.

Choose **Avalanche Center** as the installation profile when prompted,
fill out Backdrop's own site-configuration step (creates your admin
account), then you'll land on this profile's one extra step:

### 5. Fill out the "Center setup" form

| Field | What it does |
|---|---|
| Avalanche center name | Becomes the site name and shows in page titles. **Pre-filled from the site name you entered on Backdrop's "Configure site" step** — leave it or edit it, either way this value wins. |
| Logo *(optional)* | Upload your center's logo (PNG/JPG/GIF/SVG); it's installed as the site logo. Leave empty to use the theme default. Changeable later under *Appearance*. |
| Language | English or Spanish. Spanish produces a fully translated site (interface, navigation, reference pages, danger scale, demo content) and — unless you override it — also switches the danger-scale preset to **SAC**, the pairing typical of a South American center. |
| Danger-scale preset | **NAC** (North American) or **SAC** (South American) — picks the color palette, travel-advice text, and danger-scale reference page the danger map and legend use. Defaults follow the language choice (English → NAC, Spanish → SAC) but you can set either independently. |
| Map center latitude/longitude/zoom | Where the danger map centers by default. Enter latitude and longitude in **separate** boxes (a stray comma from a pasted "lat, lng" is trimmed automatically). |
| Weather service name/URL *(optional)* | Shown in the theme header/footer. |
| Social media URLs *(optional)* | Facebook, Twitter, YouTube, Instagram, email signup — shown wherever the theme surfaces social links. |

Submitting this form also creates one demo forecast-zone region (centered
on the map coordinates you gave) and one demo advisory, points the site's
front page at that demo advisory so the danger map has something to show
immediately, and publishes three reference pages wired into the menu —
*How to Read the Avalanche Forecast* (`/how-to-read-avalanche-forecast`),
the danger scale (`/avalanche-danger-scale`, North or South American to
match your preset), and *Avalanche Problems* (`/avalanche-problems`). On a
Spanish install all of these come out in Spanish. Delete the demo advisory
and forecast zone once you have real content (see below); the reference
pages are usually worth keeping and editing.

That's it — the site is live. All 63 modules (4 custom + ~32 contrib +
Backdrop core dependencies) enable automatically; nothing further needs
installing by hand.

## Hosting on a server (production)

The steps above use DDEV for a local install. For a public site on a VPS
(AWS EC2 / Lightsail, Linode, DigitalOcean, etc.), the only distribution-
specific part is the same "add core, add this distribution, run the
installer" flow — everything else is standard Backdrop hosting, so we don't
reproduce a full server tutorial here (it would go stale faster than it
helps). What the server needs:

- A **LAMP/LEMP stack**: Apache *or* nginx, **PHP 8.1** with the extensions
  Backdrop requires (`pdo_mysql`, `gd`, `curl`, `xml`, `mbstring`, `json`,
  `openssl`, `zip`), and **MySQL 5.7+ / MariaDB 10.3+**.
- Document root pointed at the site directory (the one containing `index.php`
  and `core/`), with clean-URL rewrites enabled — Backdrop ships the
  `.htaccess` for Apache; nginx needs the equivalent `try_files`/rewrite
  block from Backdrop's docs.
- HTTPS (Let's Encrypt / Certbot is the usual free option).
- A `settings.php` **based on Backdrop's own `settings.php` template** (in the
  core download), not hand-written from scratch. In particular it must keep
  **`$settings['backdrop_drupal_compatibility'] = TRUE;`** — Backdrop's default
  enables it, and several bundled contrib modules/themes (`tb_megamenu`,
  `views_bulk_operations`, `geolocation`, `responsive_bartik`/`responsive_sac`)
  call `drupal_*()` helpers that live in core's compatibility layer, which only
  loads when that flag is set. Omit it and every page white-screens with
  `Call to undefined function drupal_attributes()`.

Then follow the same **steps 2-5** above on the server (skip step 1 — that's
DDEV-only), pointing `$PROJECT` at your web root. Provider one-click images
and Backdrop's own guides cover the OS-level setup:

- DigitalOcean: their "LAMP" or "LEMP on Ubuntu" tutorials, or the
  Marketplace LAMP droplet.
- Linode: the "LAMP/LEMP stack" guides in Linode Docs.
- AWS: Lightsail's LAMP blueprint (simplest) or EC2 + Ubuntu with the stack
  installed manually.
- Backdrop core: [System requirements](https://docs.backdropcms.org/documentation/system-requirements)
  and [Installation instructions](https://docs.backdropcms.org/documentation/installation-instructions).

## Using this repo as your production code base (and keeping it updated)

Treat this repo as the **upstream code** for your site: the site's *code* comes
from here, but its *content and configuration* live only on the server. Keeping
those two straight is what makes updates safe.

**What lives where**

| Lives in this repo (code — safe to overwrite on deploy) | Lives only on the server (never in this repo) |
|---|---|
| `modules/`, `themes/`, `layouts/`, `profiles/avalanche_center/` | The database (all content, users, taxonomy) |
| Structural config shipped in `profiles/avalanche_center/config/` | The site's **active config** in `files/config_*/active/` |
| | Uploaded files in `files/`, and `settings.php` |

The distinction that trips people up: the profile's `config/` is the **install-
time default** structure. Once a site is installed, it reads its live settings
from **`files/config_*/active/`** on the server, which is *not* in this repo and
is *not* touched by a code deploy. So editing a shipped `config/*.json` here
changes only *new* installs — to change a running site you edit its active
config (`drush config-set …`, or *Configuration > Development > Configuration
management*).

**Deploying an update.** After you pull new template code, push just the four
code directories to the server and clear caches — never rsync `files/`, `core/`,
`settings.php`, or the database:

```sh
# from a checkout of this repo, on a machine with SSH access to the server
git pull --ff-only
rsync -a --delete modules/ themes/ layouts/ profiles/  user@server:/var/www/your-site/{modules,themes,layouts,profiles}/
# then on the server (or via drush over SSH):
drush updatedb -y      # run any pending schema updates
drush cache-clear all  # rebuild the registry so new/renamed code is picked up
```

`--delete` keeps the server's code dirs an exact mirror of the repo (so files
you removed upstream are removed on the server too) — which is exactly why you
must scope it to the four code dirs and never point it at the site root.

Two caveats when a deploy adds a **new module** or new fields: enabling it and
creating its field tables happens at *install* on a fresh site, but on an
existing site you enable it yourself (`drush pm-enable <module> -y`) after the
code lands. And if a deploy changes translated strings on a **Spanish** site,
re-import the profile PO and rebuild the JS translations (`_locale_import_po()`
+ `_locale_rebuild_js('es')`); an English site needs neither.

Scripting this (git pull → rsync the four dirs → drush updatedb + cache-clear
over SSH) into a one-command `deploy.sh` is the recommended way to make updates
routine and hard to get wrong.

### Upgrading Backdrop core

Core (the `core/` directory) is separate from this distribution and is upgraded
on its own schedule. To move to a newer **stable** release:

```sh
NEW=1.34.3   # the release you're moving to
git clone --depth 1 --branch "$NEW" https://github.com/backdrop/backdrop.git /tmp/bd
# replace ONLY core + the root files; never touch settings.php, files/, or the
# distribution dirs (modules/ themes/ layouts/ profiles/):
rm -rf "$PROJECT/core" && cp -r /tmp/bd/core "$PROJECT/"
cp /tmp/bd/index.php /tmp/bd/.htaccess /tmp/bd/robots.txt "$PROJECT/"
rm -rf /tmp/bd
drush updatedb -y && drush cache-clear all      # run from $PROJECT
```

Within a minor series (e.g. 1.34.0 → 1.34.3) the root files usually don't change
and there are no database updates — `updatedb` is a no-op — so it's effectively
just swapping `core/`. Always back up first, and **never downgrade** core after
running updates for a newer version (going from a `1.35.x-dev` snapshot back to
stable is a downgrade — check `select schema_version from system where
name='system'` against the target's update hooks first).

### Getting notified when a new release is available

The bundled **Avalanche Center Update Check** module flags the site's **Status
report** (*Reports > Status report*, `admin/reports/status`) when a newer
**tagged release** of the template exists upstream — so whoever runs the site
sees "Update available" without watching the repo. It's designed for the
git-checkout production model above (releases are git tags; you `git pull` to
update).

How it works: it compares the newest release tag in the server's **local git
checkout** against the newest tag on the **remote** (checked on cron, cached, so
the Status report makes no network call). When you `git pull`, the fetched tags
catch the local copy up and the flag clears. It reads git refs straight from the
filesystem — no shell/`exec` needed.

To enable the upstream check, add a **read-only** GitHub token to `settings.php`
(the repo is private, so the check needs one):

```php
$settings['avalanche_center_github_token'] = 'github_pat_...';   // fine-grained, read-only, this repo
// optional:
$settings['avalanche_center_repo']      = 'andyanderso/avalanche-center-template'; // default
$settings['avalanche_center_repo_root'] = '/var/www/your-site';  // where .git lives, only if auto-detect fails
$settings['avalanche_center_update_url'] = 'https://…/version.json'; // use a PUBLIC version marker instead of the token
```

If you'd rather not put a token on the server, point `avalanche_center_update_url`
at a public JSON marker (`{"version": "1.2.0"}` or a list of tag objects) that
you publish on each release — the module reads that instead of the GitHub API.
Without either, the row still shows the installed release but can't check
upstream. Cut releases as **git tags** (e.g. `v1.2.0`) for the comparison to work.

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

- **Spanish is chosen once, at install.** Selecting Spanish in the setup
  form installs the `es` translations, sets `es` as the default language,
  and switches demo/reference content to Spanish. There's no in-place
  English↔Spanish toggle after install; switching an existing site's
  language is a manual Backdrop `locale`/`language` operation. Note also
  that some source data on the Argentina site (glossary and conditions-alert
  taxonomy terms) is stored in English, so those specific strings fall back
  to English unless you translate them yourself.
- **No settings page for the danger-scale preset / map defaults** — the
  "Center setup" form only runs once, at install. Change them afterward
  with `drush config-set` or config import/export (see below).

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
