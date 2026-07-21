# Setting Up Your Avalanche Center Website

Welcome! Your Avalanche Center website has been prepared and is ready for you
to install and configure. This guide walks you through it from start to finish
— no coding or technical background required. It takes about 10–15 minutes.

**Before you begin, you'll need two things** (whoever sent you this link can
provide them):

- **Your site's web address** — the URL where the setup wizard is waiting, e.g.
  `https://your-site.example.com`.
- The go-ahead to **create the main administrator account** (you'll do this in
  Step 3).

> Fill this in for your reference:
> **My site address:** `________________________________`

---

## Step 1 — Open the installer and choose a language

Open your site's web address in a normal web browser (Chrome, Firefox, Safari,
or Edge). The first time the site is visited, it shows a setup wizard.

The first screen is **Choose language**.

> ⚠️ **Pick _English_ here — even if you want a Spanish website.** This screen
> only sets the language of the *setup wizard itself*. You'll choose your
> **site's** language a couple of steps from now (Step 4), and that's what
> actually makes the finished website Spanish. Choosing Spanish on this first
> screen isn't harmful, but it can show a couple of harmless technical warnings,
> so English is the smooth path.

Click **Save and continue**.

## Step 2 — Choose the installation profile

If you're asked which profile to install, choose **Avalanche Center**, then
continue. (If the wizard skips straight ahead, that's fine — it was already
selected for you.)

The installer will churn for a minute or two while it builds the site. Let it
finish.

## Step 3 — Configure the site and create your account

This is the standard site-configuration screen. You'll enter:

- A **site name** (your center's name — you can confirm or change it in the next
  step).
- The **administrator account**: a username, email address, and password. **This
  is the main login for your whole site — keep it safe.**
- Site email / time zone settings (the defaults are usually fine).

If the screen ever asks for **database details**, don't guess — the person who
set up the site for you has those; most setups fill this in automatically and
you won't see it at all.

Click to continue and you'll land on the one screen that's specific to avalanche
centers:

## Step 4 — The "Center setup" screen

This is where you make the site yours. Here's what each field does:

| Field | What it does |
|---|---|
| **Avalanche center name** | Becomes your site name and appears in page titles. It's pre-filled from Step 3 — leave it or edit it. |
| **Logo** *(optional)* | Upload your center's logo (PNG, JPG, GIF, or SVG). Leave it empty to use the built-in default; you can add or change it any time later. |
| **Language** | **English** or **Spanish**. Choosing Spanish makes the entire site — menus, danger scale, help pages, and starter content — come out in Spanish, and automatically pairs it with the South American danger scale. |
| **Danger-scale preset** | **NAC** (North American) or **SAC** (South American). This sets the colors, wording, and danger-scale page used on your forecasts. It follows your language choice by default (English → NAC, Spanish → SAC), but you can pick either one. |
| **Map center** (latitude / longitude / zoom) | Where your avalanche danger map is centered. Put the latitude and longitude in their **own separate boxes**. |
| **Weather service** *(optional)* | The name and link of your local weather office, shown in the site header/footer. |
| **Social media** *(optional)* | Facebook, Twitter/X, YouTube, Instagram, and email-signup links, shown wherever the site displays social links. |

Click **Save and continue**.

**What this creates for you:** a starter setup so the site isn't empty — one
sample forecast zone, one sample advisory (which the front page points at so the
danger map has something to show), and three ready-made reference pages linked in
the menu:

- **How to Read the Avalanche Forecast**
- **The Avalanche Danger Scale** (North or South American, matching your choice)
- **Avalanche Problems**

On a Spanish site these all appear in Spanish. Keep and edit the reference pages;
replace the sample advisory and zone with your own real content (next section).

## Step 5 — You're live! 🎉

The website is now installed and running at your address. Log in any time at
`your-site-address/user` with the administrator account you created in Step 3.

---

## Making it your own

Once you're logged in, here's how to turn the starter site into your real one.
Everything below is done by clicking around the admin area — no code.

**Replace the sample content.** Under **Content**, delete the "Demo Avalanche
Advisory" and its "Demo Forecast Zone" once you've created your real ones
(below) — otherwise visitors land on the sample.

**Add your forecast zones.** Go to **Structure → Taxonomy → Forecast Region**.
Each zone is an entry there, with a map tool to draw that zone's boundary (or
paste boundary data if you already have it). Your advisories will point at one of
these zones.

**Publish your first advisory.** Go to **Content → Add content → Advisory**. This
is the forecast itself — danger ratings by elevation, the bottom-line summary,
and which forecast zone it's for. This is what the danger map and front page
display.

**Set your home page.** The danger map appears on the front page. To make a real
advisory (or something else) your home page instead of the sample, go to
**Configuration → System → Basic site settings → Front page**.

**Adjust your branding and theme.** Under **Appearance** you can switch between
the two included designs and open a theme's **Settings** to change the logo,
name/slogan, weather-service info, social links, and elevation-band labels. Your
Step 4 choices are already filled in here.

**Edit the avalanche glossary.** The glossary lives at `your-site-address/avalanche-terms`
and is managed under **Structure → Taxonomy → Avalanche Terms**.

**Give your staff access.** Under **People**, create accounts for your
forecasters and assign them the **Editor** role — that lets them publish
advisories and content without full administrator control.

---

## Getting help

If anything looks off or you get stuck, reply to the email that sent you this
guide and include the site address and a screenshot — that's the fastest way to
get it sorted.
