# Haarlem Festival — Process Documentation

## 1. Introduction

This document describes how the Haarlem Festival ticketing website was built: the way of working, the sprint planning and the sprint retrospectives. The application is a plain-PHP MVC website (no framework) with MySQL, running in Docker, with Stripe (test mode) for payment. Development followed an Agile/Scrum approach with short, themed sprints, each delivered through one or more feature branches that were reviewed and merged into the develop branch via pull requests.

## 2. Way of working

- **Version control & branching.** Git with a gitflow-style model: main (release), develop (integration) and short-lived feature/* branches. Every change went through a pull request into develop, so develop always held a working, integrated build; main was promoted from develop as a release.
- **Code review.** Each feature branch was opened as a pull request with a detailed description and merged after review. Conflicts on shared files (e.g. the layout header) were resolved by rebasing the branch onto the latest develop.
- **Architecture discipline.** A Controller -> Service -> Repository layering, with every service and repository consumed through an interface (programming against abstractions). This kept features consistent and independently reviewable.
- **Database changes.** All schema and seed changes were made as numbered, forward-only SQL migrations applied by database/migrate.php, so every environment converged to the same state.
- **Definition of Done.** A feature was 'done' when it was implemented behind interfaces, ran end-to-end against the running app (not just unit-level), passed a lint check, was committed in small reviewable commits, and merged via PR into develop.

## 3. Sprint log

### Sprint 1 — Foundation & Authentication

**Goal.** Stand up the project skeleton and the user/account foundation so later features have a base to build on.

**Delivered**
- Docker stack (nginx, PHP-FPM, MySQL, phpMyAdmin, MailHog) and the MVC framework helpers (Container, View, Flash, base Repository, AuthMiddleware).
- Migration runner and the first schema (users).
- Registration, login/logout, email verification and password reset (MailHog).
- Self-service account management; role model (admin / employee / customer).
- Admin user management with search, sort and role filtering.

**Retrospective**

| What went well | What to improve | Actions |
|---|---|---|
| The interface-based layering and migration system paid off immediately and were reused everywhere. | Branches were sometimes created from a stale local develop, causing avoidable conflicts. | Always sync develop (checkout + pull) before creating a feature branch. |
| Authentication, CSRF and password hashing were in from the start. | A real Gmail credential was briefly committed in the mail service. | Move all secrets to .env; the credential was removed and the key revoked. |

### Sprint 2 — Events, catalogue & content

**Goal.** Model the festival catalogue and present the events, and let admins manage content.

**Delivered**
- Event types, events, venues, restaurants and artists with admin CRUD.
- The real Festival 2026 programme seeded as data (Jazz, DANCE!, Yummy, History, Magic@Teylers, Stories) with sessions, prices and capacities.
- Data-driven event overview and detail pages; data-driven navigation.
- Homepage CMS with a WYSIWYG editor and image upload.

**Retrospective**

| What went well | What to improve | Actions |
|---|---|---|
| Treating each programme line as an event reused the whole model — no schema churn. | An enum backing-value mismatch (role casing) and a MySQL DDL auto-commit issue broke a migration. | Fixed the role default via a migration and removed the transaction wrapper around DDL (MySQL auto-commits DDL). |
| Seeding real data early surfaced realistic pricing/VAT questions sooner. |  |  |

### Sprint 3 — Commerce: cart, checkout & entrance

**Goal.** Let visitors buy tickets end-to-end and let staff validate them at the door.

**Delivered**
- Shopping cart (guest + user) with live VAT-inclusive totals.
- Stripe (test) checkout, order creation and idempotent fulfilment.
- PDF tickets with QR codes and an invoice emailed via MailHog.
- Entrance ticket scanner for staff; admin orders list with CSV export and an order detail page.

**Retrospective**

| What went well | What to improve | Actions |
|---|---|---|
| The cart/checkout/order/ticket flow became the backbone that passes and reservations later reused. | QR/PDF generation failed until the GD image extension was enabled in the PHP image. | Added GD to the PHP Dockerfile; re-synced develop and verified all teammates' work was intact. |
|  | Parallel work on a teammate's repo briefly diverged the integration picture. |  |

### Sprint 4 — Festival features & flows

**Goal.** Deliver the festival-specific selling features described in the brief.

**Delivered**
- All-access passes (day / multi-day) for Jazz and DANCE!, reusing the ticket flow.
- Personal program (a customer's purchased events).
- Restaurant reservations: EUR 10 per-person fee + special requests (allergies), visible to admins.
- Pay-later orders (24h) with retry and customer order history.
- Stories pay-as-you-like donations and the HaarlemPas 25% reduction.
- Magic@Teylers kids-event page with the app-download call to action.

**Retrospective**

| What went well | What to improve | Actions |
|---|---|---|
| Modelling passes as ticket types and discounts/donations as an effective line price avoided new plumbing. | Two older branches (personal program, admin orders) drifted behind develop and needed rebasing. | Rebased the lagging branches onto develop and resolved the conflicts before merging. |
| Each feature was a focused branch, kept reviews small. |  |  |

### Sprint 5 — Experience, content, compliance & docs

**Goal.** Polish the visitor experience, add participant content, and meet the non-functional and documentation requirements.

**Delivered**
- Homepage showing every event with prices, the festival passes with real prices, a condensed day-by-day schedule and a map of locations.
- Participant (artist) detail pages: gallery, career highlights, tracks, a simulated audio sample and the schedule; an admin gallery-upload UI; real content sourced from Wikipedia/Wikimedia Commons.
- Security & GDPR pass: hardened session cookie, privacy policy, registration consent, self-service data export and account erasure (anonymisation).
- Technical documentation (ERD + UML class diagram) generated from the live schema.

**Retrospective**

| What went well | What to improve | Actions |
|---|---|---|
| Building data-driven pages meant prices/schedule stay correct as the catalogue changes. | Some content depends on external/placeholder media that needs replacing for an offline build. | Added an admin gallery upload so real, licensed media can replace placeholders. |
| Being explicit about scope let us drop a non-required feature (reCAPTCHA) to keep things minimal. |  |  |

## 4. Overall retrospective

- The interface-based architecture and the migration system were the two decisions that paid off most: features stayed consistent and every environment stayed reproducible.
- The most repeated process mistake was branching from a stale develop; the fix (sync before branching, rebase when behind) removed almost all merge pain in later sprints.
- Verifying features against the running application — not just at unit level — caught issues (GD extension, DDL auto-commit, pricing/VAT) that code review alone would have missed.
- Keeping scope tight against the brief (e.g. removing reCAPTCHA) kept the codebase minimal and focused.

## 5. To complete with team-specific detail

- Team members and their roles / lead-designer assignments per event.
- Actual sprint dates and the dates of the sprint ceremonies (planning, review, retrospective).
- Notes/screenshots from the real retrospective meetings and the sprint board (e.g. Trello/Jira).
- Any impediments raised with the Product Owner / teacher and how they were resolved.
