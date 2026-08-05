# Field Work Plan — SMS Feature Scope

**Module:** `avalanche_fwp` · **Status:** SMS channel built (Phase C), shipped, off
by default · **Last reviewed:** 2026-08-04

This document scopes the SMS text-messaging capability of the Field Work Plan
(FWP) system: what it does today, how it is wired, what it depends on, its
limitations, and what would be involved in extending it. It is descriptive of
the current code plus a forward-looking scope for decisions — not a change
order.

---

## 1. Purpose

The FWP is a staff/forecaster **trip-planning and safe-return** tool. When a
field worker files a plan, the system tracks an **expected return time** and
notifies people if the worker doesn't check in. SMS is the **optional second
delivery channel** for those notifications, alongside email.

- **Email is always sent** (core mail, no external dependency).
- **SMS is opt-in**, off until a center adds Twilio credentials and enables it.
- The two channels carry the **same event set** — SMS is a reach mechanism, not
  a separate feature with its own message types.

The safety rationale for SMS specifically: responsible parties and field workers
are often away from email at the moment that matters (a party is overdue). A
text is far more likely to be seen in time.

---

## 2. Current behavior (what is built)

### 2.1 The five notification events

Every notification is one of five types. SMS is sent for **all five** when SMS
is enabled and the recipient has a phone number on file.

| Type | Fires when | Recipient | Phone source |
|------|-----------|-----------|--------------|
| `submit` | A plan is published (optional — see `notify_on_submit`) | Responsible party | RP collection phone |
| `rp_pre` | `lead_minutes` before expected return, if not yet checked in | Responsible party | RP collection phone |
| `rp_due` | At expected return time, if not checked in | Responsible party | RP collection phone |
| `user_reminder` | At expected return time, if not checked in | Field worker (planner) | Profile mobile, else their trip-member row |
| `checkin` | Field worker taps "I'm back safe — check in" | Responsible party | RP collection phone |

**Idempotency:** each `(plan, type)` is logged in the `avalanche_fwp_notification`
table and sent **at most once**. Re-running cron never re-texts.

### 2.2 Timing / delivery mechanics

- `rp_pre`, `rp_due`, and `user_reminder` are driven by **`hook_cron`**. It scans
  published, not-checked-in plans whose return time is within ±1 day and fires
  the due messages.
- `submit` fires on node insert/update; `checkin` fires from the check-in action.
- **Cron cadence matters:** for the advance-warning lead time to be accurate to
  ~15 minutes, the site's system cron must run **every ~5 minutes**. A once-daily
  cron makes the timing meaningless.
- `lead_minutes` is admin-configurable (0–1440, default **15**). `0` disables the
  advance warning and only notifies at the return time itself.

### 2.3 Phone-number sourcing

- **Responsible party:** `field_responsible_party_phone_nu` on the plan's
  Responsible Party field-collection item.
- **Field worker:** their profile `field_mobile_phone`, falling back to the phone
  on their own trip-member row (`field_trip_member_phone_number`).
- Each channel is **skipped silently if its address is blank** — a plan with no RP
  phone simply gets email only.

### 2.4 Message content

SMS bodies are short plain-text strings (examples, abbreviated):

- `rp_due`: *"[name] was due back at [time] and has NOT checked in. Please follow
  your response protocol."*
- `user_reminder`: *"You were due back at [time]. Please check in so your
  responsible party knows you are safe: [check-in link]"*
- `checkin`: *"[name] has checked in safely from the field."*

All strings pass through `t()` (translatable) and are then `html_entity_decode()`d
before sending, so `&` and other characters in **tracking URLs (Garmin/InReach)**
and names aren't mangled into `&amp;` in the plain-text SMS/email.

### 2.5 Transport

- Delivery goes through the vendored **`twilio`** contrib module
  (`modules/twilio`, Backdrop 1.x port, bundles a legacy Twilio PHP SDK).
- `avalanche_fwp_send_sms($number, $message)` calls `twilio_send()` **only if that
  function exists** — so if the Twilio module is absent/disabled, SMS degrades to
  nothing (email still sends) rather than erroring.
- Twilio's own per-user phone-registration field is suppressed
  (`twilio_registration_form = 0`) — FWP texts the numbers stored on the plan, it
  does not use Twilio's user-account phone model.

---

## 3. Configuration surface

Two admin pages govern SMS.

**FWP settings** — `admin/config/system/avalanche-fwp`
- `enable_sms` (checkbox, **default off**) — "Also send SMS text messages (via
  Twilio)". Email is always sent regardless.
- `notify_on_submit` — send the RP the "plan filed" heads-up on publish.
- `lead_minutes` — advance-warning window (0–1440, default 15).

**Twilio settings** — `admin/config/system/twilio`
- Account SID, Auth Token, and a sending (From) number.

**Enabling SMS on a center = both:** creds on the Twilio page **and** `enable_sms`
checked. Either one alone sends nothing over SMS.

---

## 4. Dependencies & assumptions

- **Twilio account required** — Account SID, Auth Token, and a provisioned sending
  number. This is a paid, per-message service (see cost note below).
- **System cron every ~5 min** — required for timing accuracy; a deployment
  concern, not a code one.
- **PHP 8.2 compatibility of the vendored SDK's send path is unverified live** —
  the legacy Twilio PHP SDK bundled in the module has not yet run a real send on
  PHP 8.2 (no center has entered creds yet). Documented fallback if it breaks: a
  direct Twilio REST `POST` from `avalanche_fwp_send_sms()`.
- **Phone numbers are free-text** — stored as entered, with no format validation
  or normalization. Twilio wants E.164 (`+56…`, `+1…`). See risks.

---

## 5. Known limitations / risks

| # | Item | Impact | Notes |
|---|------|--------|-------|
| 1 | **No phone-number validation/normalization** | Deliverability | Numbers are free-text; a badly formatted or non-E.164 number silently fails at Twilio. High priority if SMS is turned on. |
| 2 | **Fire-and-forget delivery** | Reliability | `twilio_send()` result is not checked, logged, or retried. A failed send looks identical to a success on our side. |
| 3 | **No inbound SMS** | UX | Check-in is a **web link only**. A worker can't reply "SAFE" to the text to check in — they must open the link. |
| 4 | **PHP 8.2 send path unverified** | Blocking for go-live | Must be tested the first time a center adds creds (see §4). |
| 5 | **No opt-out / STOP handling** | Compliance | No per-recipient SMS opt-out or carrier STOP keyword handling. |
| 6 | **Consent not modeled** | Compliance | Texting responsible parties/partners assumes consent; no record of it. Regulatory exposure varies by country (US TCPA-style rules, Chilean equivalents). |
| 7 | **Single language per site** | i18n | SMS body uses `language_default()`, not the recipient's preference. |
| 8 | **Segment length / cost** | Cost | Some messages exceed 160 chars (esp. with a URL) → billed as 2 segments. No cost cap or rate limit. |

---

## 6. Explicitly out of scope (today)

These are **not built**; listed so the boundary is clear and they can be
prioritized later:

- Inbound "reply SAFE to check in" SMS check-in.
- E.164 validation/normalization of stored phone numbers.
- Delivery receipts, retries, and failure alerting to an admin.
- Per-recipient channel preference (email-only / SMS-only).
- Per-recipient / per-message-language SMS localization.
- Rate limiting or monthly cost caps.
- Escalation chains (e.g. text a second contact if the first doesn't respond).

---

## 7. Enabling SMS on a live center — checklist

1. Vendor + enable the `twilio` module (already done on the Chile deploy).
2. Enter Twilio **Account SID, Auth Token, and sending number** at
   `admin/config/system/twilio`.
3. Check **"Also send SMS text messages"** (`enable_sms`) at
   `admin/config/system/avalanche-fwp`.
4. Confirm **system cron runs every ~5 minutes**.
5. Confirm the relevant **phone fields are populated** and in a Twilio-acceptable
   format (E.164, e.g. `+56…`).
6. **Live smoke test** each of the five types (esp. verify the PHP 8.2 send path
   works; switch to the REST fallback if `twilio_send()` fails).
7. Watch the first few real sends / Twilio console for delivery + cost.

---

## 8. Open questions for the center

- Which recipients should SMS reach — responsible party only, or field workers
  too? (Currently both.)
- Is inbound check-by-text ("reply SAFE") wanted enough to build? It materially
  improves the safety loop but adds a Twilio webhook + parsing.
- Do we need consent capture / opt-out to satisfy local regulation before turning
  SMS on for real recipients?
- Is per-number validation worth adding before go-live, given #1 above?

---

*Grounded in `modules/avalanche_fwp/` (`avalanche_fwp.module`,
`avalanche_fwp.admin.inc`, `avalanche_fwp.install`) as of 2026-08-04. See the FWP
module plan for build history and phase log.*
