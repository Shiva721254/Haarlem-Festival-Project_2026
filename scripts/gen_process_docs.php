<?php
/**
 * Generate the Haarlem Festival process documentation (Word + Markdown).
 *
 * Sprint goals and deliverables are anchored to the real git history (feature
 * branches merged via pull requests into develop). Retrospective points reflect
 * issues actually encountered during the build. Team-specific details (member
 * names, ceremony dates, meeting notes) are marked for the team to complete.
 *
 * Run:  php scripts/gen_process_docs.php
 */

require __DIR__ . '/../app/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

const OUT_DIR = __DIR__ . '/../docs';

if (!is_dir(OUT_DIR)) {
    mkdir(OUT_DIR, 0775, true);
}

$INTRO =
    "This document describes how the Haarlem Festival ticketing website was built: " .
    "the way of working, the sprint planning and the sprint retrospectives. The " .
    "application is a plain-PHP MVC website (no framework) with MySQL, running in " .
    "Docker, with Stripe (test mode) for payment. Development followed an Agile/Scrum " .
    "approach with short, themed sprints, each delivered through one or more feature " .
    "branches that were reviewed and merged into the develop branch via pull requests.";

$WAY_OF_WORKING = [
    ["Version control & branching",
     "Git with a gitflow-style model: main (release), develop (integration) and short-lived " .
     "feature/* branches. Every change went through a pull request into develop, so develop " .
     "always held a working, integrated build; main was promoted from develop as a release."],
    ["Code review",
     "Each feature branch was opened as a pull request with a detailed description and merged " .
     "after review. Conflicts on shared files (e.g. the layout header) were resolved by rebasing " .
     "the branch onto the latest develop."],
    ["Architecture discipline",
     "A Controller -> Service -> Repository layering, with every service and repository consumed " .
     "through an interface (programming against abstractions). This kept features consistent and " .
     "independently reviewable."],
    ["Database changes",
     "All schema and seed changes were made as numbered, forward-only SQL migrations applied by " .
     "database/migrate.php, so every environment converged to the same state."],
    ["Definition of Done",
     "A feature was 'done' when it was implemented behind interfaces, ran end-to-end against the " .
     "running app (not just unit-level), passed a lint check, was committed in small reviewable " .
     "commits, and merged via PR into develop."],
];

$SPRINTS = [
    [
        "title" => "Sprint 1 â€” Foundation & Authentication",
        "goal" => "Stand up the project skeleton and the user/account foundation so later features " .
                  "have a base to build on.",
        "delivered" => [
            "Docker stack (nginx, PHP-FPM, MySQL, phpMyAdmin, MailHog) and the MVC framework " .
            "helpers (Container, View, Flash, base Repository, AuthMiddleware).",
            "Migration runner and the first schema (users).",
            "Registration, login/logout, email verification and password reset (MailHog).",
            "Self-service account management; role model (admin / employee / customer).",
            "Admin user management with search, sort and role filtering.",
        ],
        "well" => [
            "The interface-based layering and migration system paid off immediately and were reused everywhere.",
            "Authentication, CSRF and password hashing were in from the start.",
        ],
        "improve" => [
            "Branches were sometimes created from a stale local develop, causing avoidable conflicts.",
            "A real Gmail credential was briefly committed in the mail service.",
        ],
        "actions" => [
            "Always sync develop (checkout + pull) before creating a feature branch.",
            "Move all secrets to .env; the credential was removed and the key revoked.",
        ],
    ],
    [
        "title" => "Sprint 2 â€” Events, catalogue & content",
        "goal" => "Model the festival catalogue and present the events, and let admins manage content.",
        "delivered" => [
            "Event types, events, venues, restaurants and artists with admin CRUD.",
            "The real Festival 2026 programme seeded as data (Jazz, DANCE!, Yummy, History, " .
            "Magic@Teylers, Stories) with sessions, prices and capacities.",
            "Data-driven event overview and detail pages; data-driven navigation.",
            "Homepage CMS with a WYSIWYG editor and image upload.",
        ],
        "well" => [
            "Treating each programme line as an event reused the whole model â€” no schema churn.",
            "Seeding real data early surfaced realistic pricing/VAT questions sooner.",
        ],
        "improve" => [
            "An enum backing-value mismatch (role casing) and a MySQL DDL auto-commit issue broke a migration.",
        ],
        "actions" => [
            "Fixed the role default via a migration and removed the transaction wrapper around DDL " .
            "(MySQL auto-commits DDL).",
        ],
    ],
    [
        "title" => "Sprint 3 â€” Commerce: cart, checkout & entrance",
        "goal" => "Let visitors buy tickets end-to-end and let staff validate them at the door.",
        "delivered" => [
            "Shopping cart (guest + user) with live VAT-inclusive totals.",
            "Stripe (test) checkout, order creation and idempotent fulfilment.",
            "PDF tickets with QR codes and an invoice emailed via MailHog.",
            "Entrance ticket scanner for staff; admin orders list with CSV export and an order detail page.",
        ],
        "well" => [
            "The cart/checkout/order/ticket flow became the backbone that passes and reservations later reused.",
        ],
        "improve" => [
            "QR/PDF generation failed until the GD image extension was enabled in the PHP image.",
            "Parallel work on a teammate's repo briefly diverged the integration picture.",
        ],
        "actions" => [
            "Added GD to the PHP Dockerfile; re-synced develop and verified all teammates' work was intact.",
        ],
    ],
    [
        "title" => "Sprint 4 â€” Festival features & flows",
        "goal" => "Deliver the festival-specific selling features described in the brief.",
        "delivered" => [
            "All-access passes (day / multi-day) for Jazz and DANCE!, reusing the ticket flow.",
            "Personal program (a customer's purchased events).",
            "Restaurant reservations: EUR 10 per-person fee + special requests (allergies), visible to admins.",
            "Pay-later orders (24h) with retry and customer order history.",
            "Stories pay-as-you-like donations and the HaarlemPas 25% reduction.",
            "Magic@Teylers kids-event page with the app-download call to action.",
        ],
        "well" => [
            "Modelling passes as ticket types and discounts/donations as an effective line price avoided new plumbing.",
            "Each feature was a focused branch, kept reviews small.",
        ],
        "improve" => [
            "Two older branches (personal program, admin orders) drifted behind develop and needed rebasing.",
        ],
        "actions" => [
            "Rebased the lagging branches onto develop and resolved the conflicts before merging.",
        ],
    ],
    [
        "title" => "Sprint 5 â€” Experience, content, compliance & docs",
        "goal" => "Polish the visitor experience, add participant content, and meet the non-functional " .
                  "and documentation requirements.",
        "delivered" => [
            "Homepage showing every event with prices, the festival passes with real prices, a " .
            "condensed day-by-day schedule and a map of locations.",
            "Participant (artist) detail pages: gallery, career highlights, tracks, a simulated audio " .
            "sample and the schedule; an admin gallery-upload UI; real content sourced from " .
            "Wikipedia/Wikimedia Commons.",
            "Security & GDPR pass: hardened session cookie, privacy policy, registration consent, " .
            "self-service data export and account erasure (anonymisation).",
            "Technical documentation (ERD + UML class diagram) generated from the live schema.",
        ],
        "well" => [
            "Building data-driven pages meant prices/schedule stay correct as the catalogue changes.",
            "Being explicit about scope let us drop a non-required feature (reCAPTCHA) to keep things minimal.",
        ],
        "improve" => [
            "Some content depends on external/placeholder media that needs replacing for an offline build.",
        ],
        "actions" => [
            "Added an admin gallery upload so real, licensed media can replace placeholders.",
        ],
    ],
];

$OVERALL = [
    "The interface-based architecture and the migration system were the two decisions that paid " .
    "off most: features stayed consistent and every environment stayed reproducible.",
    "The most repeated process mistake was branching from a stale develop; the fix (sync before " .
    "branching, rebase when behind) removed almost all merge pain in later sprints.",
    "Verifying features against the running application â€” not just at unit level â€” caught issues " .
    "(GD extension, DDL auto-commit, pricing/VAT) that code review alone would have missed.",
    "Keeping scope tight against the brief (e.g. removing reCAPTCHA) kept the codebase minimal and focused.",
];

$TODO_TEAM = [
    "Team members and their roles / lead-designer assignments per event.",
    "Actual sprint dates and the dates of the sprint ceremonies (planning, review, retrospective).",
    "Notes/screenshots from the real retrospective meetings and the sprint board (e.g. Trello/Jira).",
    "Any impediments raised with the Product Owner / teacher and how they were resolved.",
];

/**
 * Build the Markdown deliverable.
 */
function build_markdown(string $intro, array $wow, array $sprints, array $overall, array $todo): void
{
    $lines = process_md_intro($intro, $wow);
    process_md_sprints($lines, $sprints);
    md_list_section($lines, "## 4. Overall retrospective", $overall);
    md_list_section($lines, "## 5. To complete with team-specific detail", $todo);
    write_text(OUT_DIR . "/process-documentation.md", implode("\n", $lines) . "\n");
}

function process_md_intro(string $intro, array $wow): array
{
    $lines = ["# Haarlem Festival â€” Process Documentation", "", "## 1. Introduction", "", $intro, "", "## 2. Way of working", ""];
    foreach ($wow as [$head, $body]) {
        $lines[] = "- **{$head}.** {$body}";
    }
    return array_merge($lines, ["", "## 3. Sprint log", ""]);
}

function process_md_sprints(array &$lines, array $sprints): void
{
    foreach ($sprints as $s) {
        process_md_sprint($lines, $s);
    }
}

function process_md_sprint(array &$lines, array $s): void
{
    $lines = array_merge($lines, ["### {$s['title']}", "", "**Goal.** {$s['goal']}", "", "**Delivered**"]);
    foreach ($s["delivered"] as $d) {
        $lines[] = "- {$d}";
    }
    $lines = array_merge($lines, ["", "**Retrospective**", "", "| What went well | What to improve | Actions |", "|---|---|---|"]);
    process_md_retro_rows($lines, $s);
    $lines[] = "";
}

function process_md_retro_rows(array &$lines, array $s): void
{
    for ($i = 0; $i < max(count($s["well"]), count($s["improve"]), count($s["actions"])); $i++) {
        $lines[] = "| " . ($s["well"][$i] ?? "") . " | " . ($s["improve"][$i] ?? "") . " | " . ($s["actions"][$i] ?? "") . " |";
    }
}

function md_list_section(array &$lines, string $heading, array $items): void
{
    $lines = array_merge($lines, ["", $heading, ""]);
    foreach ($items as $x) {
        $lines[] = "- {$x}";
    }
}

function write_text(string $out, string $body): void
{
    file_put_contents($out, $body);
    echo "wrote {$out}\n";
}

/**
 * Build the Word (.docx) deliverable.
 */
function build_docx(string $intro, array $wow, array $sprints, array $overall, array $todo): void
{
    $phpWord = process_word();
    $section = $phpWord->addSection();
    process_cover($section);
    docx_intro($section, $intro, $wow);
    docx_sprints($section, $sprints);
    docx_list_section($section, "4. Overall retrospective", $overall);
    docx_todo($section);
    docx_items($section, $todo);
    save_word($phpWord, OUT_DIR . "/Process-Documentation.docx");
}

function docx_intro($section, string $intro, array $wow): void
{
    $section->addTitle("1. Introduction", 1);
    $section->addText($intro);
    docx_list_section($section, "2. Way of working", array_map(fn($x) => "{$x[0]}. {$x[1]}", $wow));
}

function docx_sprints($section, array $sprints): void
{
    $section->addTitle("3. Sprint log", 1);
    foreach ($sprints as $s) {
        docx_sprint($section, $s);
    }
}

function docx_todo($section): void
{
    $section->addTitle("5. To complete with team-specific detail", 1);
    $section->addText("This draft is generated from the development history. Add the following before submission:");
}

function process_word(): PhpWord
{
    Settings::setZipClass(Settings::PCLZIP);
    $phpWord = new PhpWord();
    $phpWord->addTitleStyle(0, ['bold' => true, 'size' => 26], ['alignment' => Jc::CENTER]);
    $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 16]);
    $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 13]);
    return $phpWord;
}

function process_cover($section): void
{
    $section->addTitle("Haarlem Festival", 0);
    $section->addText("Process Documentation", ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER]);
    $section->addPageBreak();
}

function docx_list_section($section, string $title, array $items): void
{
    $section->addTitle($title, 1);
    docx_items($section, $items);
}

function docx_items($section, array $items): void
{
    foreach ($items as $item) {
        $section->addListItem($item);
    }
}

function docx_sprint($section, array $s): void
{
    $section->addTitle($s["title"], 2);
    docx_goal($section, $s["goal"]);
    $section->addText("Delivered", ['bold' => true, 'italic' => true]);
    docx_items($section, $s["delivered"]);
    docx_retro($section, $s);
    $section->addTextBreak();
}

function docx_goal($section, string $goal): void
{
    $g = $section->addTextRun();
    $g->addText("Goal. ", ['bold' => true]);
    $g->addText($goal);
}

function docx_retro($section, array $s): void
{
    $section->addText("Retrospective", ['bold' => true]);
    $table = retro_table($section);
    for ($i = 0; $i < max(count($s["well"]), count($s["improve"]), count($s["actions"])); $i++) {
        retro_row($table, $s, $i);
    }
}

function retro_table($section)
{
    $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 60]);
    $table->addRow();
    foreach (["What went well", "What to improve", "Actions"] as $label) {
        $table->addCell(3000, ['bgColor' => 'DCE6F1'])->addText($label, ['bold' => true]);
    }
    return $table;
}

function retro_row($table, array $s, int $i): void
{
    $table->addRow();
    $table->addCell(3000)->addText($s["well"][$i] ?? "");
    $table->addCell(3000)->addText($s["improve"][$i] ?? "");
    $table->addCell(3000)->addText($s["actions"][$i] ?? "");
}

function save_word(PhpWord $phpWord, string $out): void
{
    IOFactory::createWriter($phpWord, 'Word2007')->save($out);
    echo "wrote {$out}\n";
}

build_markdown($INTRO, $WAY_OF_WORKING, $SPRINTS, $OVERALL, $TODO_TEAM);
build_docx($INTRO, $WAY_OF_WORKING, $SPRINTS, $OVERALL, $TODO_TEAM);
echo "done\n";

