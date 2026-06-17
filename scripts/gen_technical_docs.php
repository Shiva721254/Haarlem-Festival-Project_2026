<?php
/**
 * Generate the Haarlem Festival technical documentation (Word + Markdown) from the
 * real database schema and class structure. Diagrams are authored in Mermaid and
 * rendered to images via mermaid.ink for the Word file; the Markdown keeps the
 * Mermaid source so it renders on GitHub.
 *
 * Run:  php scripts/gen_technical_docs.php
 */

require __DIR__ . '/../app/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

const OUT_DIR = __DIR__ . '/../docs';

const ENTITY_ROWS = [
    ["users", "Accounts with a role (customer/employee/admin)."],
    ["event_types", "The six festival events (Jazz, DANCE!, Yummy, History, Magic, Stories)."],
    ["events", "A session/performance under a type, at a venue or restaurant; may be a pass."],
    ["ticket_types", "What can be bought for an event: price, VAT, capacity, donation flag."],
    ["venues / restaurants / artists", "Catalogue entities; artists link to events many-to-many and have detail content."],
    ["artist_images", "Gallery images for an artist's detail page."],
    ["carts / cart_items", "A guest or user cart; lines hold quantity, requests and custom price."],
    ["orders / order_items", "A placed order and its priced lines; status drives pay-later."],
    ["tickets", "Issued per order-item with a unique QR code; scanned at the entrance."],
    ["content_blocks / images", "CMS content (WYSIWYG HTML) and uploaded images."],
];

if (!is_dir(OUT_DIR)) {
    mkdir(OUT_DIR, 0775, true);
}

// --------------------------------------------------------------------------- //
// Mermaid diagram sources (authored from the actual schema / classes)
// --------------------------------------------------------------------------- //

$ERD = <<<'MERMAID'
erDiagram
    users ||--o{ orders : places
    users ||--o{ carts : owns
    users ||--o{ content_blocks : edits
    users ||--o{ images : uploads
    event_types ||--o{ events : groups
    venues ||--o{ events : hosts
    restaurants ||--o{ events : hosts
    events ||--o{ ticket_types : offers
    events ||--o{ event_artist : "lines up"
    artists ||--o{ event_artist : performs
    artists ||--o{ artist_images : has
    ticket_types ||--o{ cart_items : "added as"
    ticket_types ||--o{ order_items : "sold as"
    carts ||--o{ cart_items : contains
    orders ||--o{ order_items : contains
    order_items ||--o{ tickets : issues

    users {
        int UserId PK
        varchar Email UK
        varchar FirstName
        varchar LastName
        varchar Role
        varchar Password
        tinyint isVerified
        tinyint isActive
    }
    event_types {
        int id PK
        varchar slug UK
        varchar name
        tinyint is_active
    }
    venues {
        int id PK
        varchar name
        varchar address
        int capacity
    }
    restaurants {
        int id PK
        varchar name
        varchar cuisine
        tinyint stars
        decimal price_per_seat
    }
    artists {
        int id PK
        varchar name
        varchar genre
        text career_highlights
        text tracks
        varchar audio_url
        varchar image
    }
    artist_images {
        int id PK
        int artist_id FK
        varchar path
        int sort_order
    }
    events {
        int id PK
        int event_type_id FK
        int venue_id FK
        int restaurant_id FK
        varchar title
        datetime starts_at
        tinyint is_published
        tinyint is_pass
    }
    event_artist {
        int event_id FK
        int artist_id FK
    }
    ticket_types {
        int id PK
        int event_id FK
        varchar name
        decimal price
        decimal vat_rate
        int capacity
        int sold
        tinyint is_donation
    }
    carts {
        int id PK
        int user_id FK
        varchar session_id
    }
    cart_items {
        int id PK
        int cart_id FK
        int ticket_type_id FK
        int quantity
        varchar special_requests
        decimal custom_price
    }
    orders {
        int id PK
        int user_id FK
        varchar status
        varchar invoice_number UK
        decimal subtotal
        decimal vat_total
        decimal total
        datetime pay_later_until
        datetime paid_at
    }
    order_items {
        int id PK
        int order_id FK
        int ticket_type_id FK
        int quantity
        decimal unit_price
        decimal vat_rate
        varchar special_requests
    }
    tickets {
        int id PK
        int order_item_id FK
        varchar qr_code UK
        varchar status
        datetime scanned_at
    }
    content_blocks {
        int id PK
        varchar page_slug
        varchar block_key
        mediumtext html
        int updated_by FK
    }
    images {
        int id PK
        varchar path
        int uploaded_by FK
    }

MERMAID;

$DOMAIN = <<<'MERMAID'
classDiagram
    class UserModel {
        +int UserId
        +string Email
        +string FirstName
        +string LastName
        +UserRole Role
        +bool isVerified
        +bool isActive
    }
    class EventModel {
        +int id
        +string title
        +string starts_at
        +bool is_published
        +bool is_pass
        +VenueModel venue
        +RestaurantModel restaurant
        +ArtistModel[] artists
    }
    class TicketTypeModel {
        +int id
        +int event_id
        +string name
        +float price
        +float vat_rate
        +int capacity
        +int sold
        +bool is_donation
        +available() int
        +isSoldOut() bool
    }
    class VenueModel {
        +int id
        +string name
        +string address
        +int capacity
    }
    class RestaurantModel {
        +int id
        +string name
        +string cuisine
        +int stars
        +float price_per_seat
    }
    class ArtistModel {
        +int id
        +string name
        +string genre
        +string bio
        +string career_highlights
        +string tracks
        +string audio_url
        +string[] images
        +trackList() string[]
    }
    class CartItemModel {
        +int ticket_type_id
        +int quantity
        +float price
        +float custom_price
        +string special_requests
        +effectivePrice() float
        +lineSubtotal() float
    }
    class OrderModel {
        +int id
        +int user_id
        +string status
        +string invoice_number
        +float subtotal
        +float vat_total
        +float total
        +string pay_later_until
        +OrderItemModel[] items
        +isPaid() bool
        +canPayLater() bool
    }
    class OrderItemModel {
        +int ticket_type_id
        +int quantity
        +float unit_price
        +float vat_rate
        +string special_requests
        +lineTotal() float
    }
    class TicketModel {
        +int id
        +int order_item_id
        +string qr_code
        +string status
    }

    EventModel "1" --> "0..1" VenueModel : at
    EventModel "1" --> "0..1" RestaurantModel : at
    EventModel "*" --> "*" ArtistModel : line-up
    EventModel "1" --> "*" TicketTypeModel : offers
    OrderModel "1" --> "*" OrderItemModel : contains
    OrderModel "*" --> "1" UserModel : placed by
    OrderItemModel "1" --> "1" TicketTypeModel : of
    OrderItemModel "1" --> "*" TicketModel : issues
    CartItemModel "1" --> "1" TicketTypeModel : of

MERMAID;

$ARCH = <<<'MERMAID'
classDiagram
    direction LR
    class CheckoutController {
        +start()
        +success()
        +cancel()
    }
    class CustomerOrderController {
        +index()
        +pay()
    }
    class IOrderService {
        <<interface>>
        +createFromCart(userId) array
        +getByUser(userId) OrderModel[]
        +getByIdForUser(id, userId) OrderModel
        +canStartPayment(order) array
        +getAllForAdmin(status) array
    }
    class OrderService {
        +createFromCart(userId) array
        +canStartPayment(order) array
    }
    class IOrderRepository {
        <<interface>>
        +create(order) int
        +getById(id) OrderModel
        +getByIdForUser(id, userId) OrderModel
        +markPaid(id, invoice)
        +issueTickets(id)
    }
    class OrderRepository {
        +create(order) int
        +issueTickets(id)
    }
    class Repository {
        <<abstract>>
        #fetchOne()
        #fetchAll()
        #execute()
    }
    class OrderModel
    class PaymentService {
        +createCheckoutSession(order, ...) string
    }

    CheckoutController ..> IOrderService : uses
    CustomerOrderController ..> IOrderService : uses
    CheckoutController ..> PaymentService : uses
    IOrderService <|.. OrderService : implements
    OrderService ..> IOrderRepository : uses
    IOrderRepository <|.. OrderRepository : implements
    OrderRepository --|> Repository : extends
    OrderService ..> OrderModel : returns

MERMAID;

$DIAGRAMS = [
    ["erd", "Entity-Relationship Diagram", $ERD],
    ["domain", "Domain Model (UML Class Diagram)", $DOMAIN],
    ["architecture", "Application Architecture (Layered MVC)", $ARCH],
];

/**
 * Render a Mermaid source to JPEG bytes via the mermaid.ink web service.
 */
function render(string $mermaidSrc): string
{
    $b64 = rtrim(strtr(base64_encode($mermaidSrc), '+/', '-_'), '=');
    $url = "https://mermaid.ink/img/" . $b64 . "?type=jpeg&bgColor=ffffff";
    $ctx = stream_context_create([
        'http' => ['header' => "User-Agent: Mozilla/5.0\r\n", 'timeout' => 60],
        'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $data = file_get_contents($url, false, $ctx);
    if ($data === false || $data === '') {
        throw new \RuntimeException("Failed to render mermaid diagram from {$url}");
    }
    return $data;
}

function caption($section, string $text): void
{
    $section->addText($text, ['italic' => true, 'size' => 9]);
}

function entity_table($section): void
{
    $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 60]);
    entity_header($table);
    foreach (entity_rows() as [$name, $desc]) {
        $table->addRow();
        $table->addCell(3000)->addText($name);
        $table->addCell(6000)->addText($desc);
    }
}

function entity_header($table): void
{
    $table->addRow();
    $table->addCell(3000, ['bgColor' => 'DCE6F1'])->addText("Entity", ['bold' => true]);
    $table->addCell(6000, ['bgColor' => 'DCE6F1'])->addText("Purpose", ['bold' => true]);
}

function entity_rows(): array
{
    return ENTITY_ROWS;
}

/**
 * Build the Word (.docx) deliverable.
 *
 * @param array<string,string> $images map of diagram key -> image file path
 */
function build_docx(array $images): void
{
    $phpWord = technical_word();
    $section = $phpWord->addSection();
    technical_cover($section);
    technical_intro($section);
    technical_architecture($section, $images["architecture"]);
    technical_database($section, $images["erd"]);
    technical_domain($section, $images["domain"]);
    technical_decisions($section);
    save_technical_word($phpWord);
}

function technical_word(): PhpWord
{
    Settings::setZipClass(Settings::PCLZIP);
    $phpWord = new PhpWord();
    $phpWord->addTitleStyle(0, ['bold' => true, 'size' => 26], ['alignment' => Jc::CENTER]);
    $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 16]);
    $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 13]);
    return $phpWord;
}

function technical_cover($section): void
{
    $section->addTitle("Haarlem Festival", 0);
    $section->addText("Technical Documentation", ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER]);
    $section->addText("Ticketing website for The Festival (Inholland, 2.3)", ['size' => 10], ['alignment' => Jc::CENTER]);
    $section->addText("Plain PHP MVC Â· MySQL Â· Docker", ['size' => 10], ['alignment' => Jc::CENTER]);
    $section->addPageBreak();
}

function technical_intro($section): void
{
    $section->addTitle("1. Introduction", 1);
    $section->addText("The Haarlem Festival website lets visitors browse six festival events, build a personal program, buy tickets and passes, make restaurant reservations, donate to pay-as-you-like events, and pay online (or within 24 hours). Staff scan tickets at the entrance and administrators manage the catalogue, content and orders.");
    $section->addText("It is built in plain PHP (no framework) following an MVC structure with a Controller -> Service -> Repository layering. It runs in Docker (nginx, PHP-FPM, MySQL 8, phpMyAdmin, MailHog) and uses Stripe (test mode) for payment.");
}

function technical_architecture($section, string $image): void
{
    $section->addTitle("2. Architecture", 1);
    $section->addText("Requests are routed (FastRoute) to a Controller, which validates input and delegates to a Service. Services hold the business rules and depend on Repositories for data access; every Service and Repository is consumed through an interface, so behaviour is programmed against abstractions, not concrete classes. Repositories extend a base Repository that wraps PDO with prepared statements.");
    $section->addText("Cross-cutting concerns: AuthMiddleware (authentication, role checks, CSRF), a small Container for wiring, View for rendering templates, and Flash for one-shot messages. The diagram below shows the pattern for the Orders slice.");
    $section->addImage($image, ['width' => 454, 'alignment' => Jc::CENTER]);
    caption($section, "Figure 1 â€” Layered MVC with interface-based services and repositories (Orders slice).");
}

function technical_database($section, string $image): void
{
    $section->addTitle("3. Database design", 1);
    $section->addText("The schema is created and evolved through numbered, forward-only SQL migrations run by database/migrate.php. Money is stored as DECIMAL; prices are VAT-inclusive and the VAT portion is derived per line. The entity-relationship diagram below reflects the live schema.");
    $section->addImage($image, ['width' => 468, 'alignment' => Jc::CENTER]);
    caption($section, "Figure 2 â€” Entity-Relationship Diagram (generated from the live database).");
    $section->addTitle("3.1 Key entities", 2);
    entity_table($section);
}

function technical_domain($section, string $image): void
{
    $section->addTitle("4. Domain model", 1);
    $section->addText("The domain classes mirror the schema and carry small behaviours. For example TicketTypeModel::available() derives remaining stock, CartItemModel::effectivePrice() applies a donation amount or HaarlemPas discount, and OrderModel::canPayLater() encodes the 24-hour pay-later rule.");
    $section->addImage($image, ['width' => 468, 'alignment' => Jc::CENTER]);
    caption($section, "Figure 3 â€” Domain model UML class diagram.");
}

function technical_decisions($section): void
{
    $section->addTitle("5. Key design decisions", 1);
    foreach (technical_decision_rows() as [$head, $body]) {
        $p = $section->addTextRun();
        $p->addText($head . ". ", ['bold' => true]);
        $p->addText($body);
    }
}

function technical_decision_rows(): array
{
    return [["Program against interfaces", "Each Service/Repository has an interface (e.g. IOrderService, IOrderRepository). Controllers and Services depend on the interface, which keeps layers swappable and testable."], ["Migrations", "Schema changes are append-only SQL files applied in order and tracked in a migrations table, so every environment converges to the same state."], ["Passes as ticket types", "All-access passes are modelled as ticket types on a flagged 'pass event' (events.is_pass), so they reuse the cart, checkout, ticket and personal-program flow unchanged."], ["Effective price", "Donations (pay-what-you-like) and the HaarlemPas 25% reduction both resolve to a per-line effective price (cart_items.custom_price), which becomes the order line's unit_price."], ["Reservations", "Restaurant reservations charge a EUR 10 per-person fee and capture special requests (allergies) on the order line, visible to admins on the order detail page."], ["Pay later", "Orders are created pending with pay_later_until = now + 24h; retry re-validates the deadline and stock before re-opening Stripe."], ["Security & GDPR", "Passwords are hashed; all POST requests carry a CSRF token; queries use prepared statements; the session cookie is HttpOnly/SameSite. Users can export or erase their data; erasure anonymises the row to preserve invoice integrity."]];
}

function save_technical_word(PhpWord $phpWord): void
{
    $out = OUT_DIR . "/Technical-Documentation.docx";
    IOFactory::createWriter($phpWord, 'Word2007')->save($out);
    echo "wrote {$out}\n";
}

/**
 * Build the Markdown deliverable (keeps Mermaid source for GitHub rendering).
 */
function build_markdown(string $arch, string $erd, string $domain): void
{
    write_technical_text(OUT_DIR . "/technical-documentation.md", technical_markdown($arch, $erd, $domain));
}

function technical_markdown(string $arch, string $erd, string $domain): string
{
    return md_title() . md_architecture($arch) . md_database($erd) . md_domain($domain) . md_decisions();
}

function md_title(): string
{
    return "# Haarlem Festival â€” Technical Documentation\n\nTicketing website for The Festival (Inholland 2.3). Plain PHP MVC, MySQL, Docker,\nStripe (test) payment.\n\n";
}

function md_architecture(string $arch): string
{
    return "## 1. Architecture\n\nRequests are routed to a **Controller**, which delegates to a **Service** (business\nrules) that uses a **Repository** (data access). Every Service and Repository is\nconsumed through an **interface**, so the code is programmed against abstractions.\nRepositories extend a base `Repository` wrapping PDO with prepared statements.\nCross-cutting: `AuthMiddleware` (auth, roles, CSRF), `View`, `Flash`, `Container`.\n\n```mermaid\n{$arch}```\n\n";
}

function md_database(string $erd): string
{
    return "## 2. Database design\n\nThe schema is built by numbered, forward-only SQL **migrations**\n(`database/migrate.php`). Money is `DECIMAL`; prices are VAT-inclusive and the VAT\nportion is derived per line.\n\n```mermaid\n{$erd}```\n\n";
}

function md_domain(string $domain): string
{
    return "## 3. Domain model\n\nDomain classes mirror the schema and carry small behaviours â€” e.g.\n`TicketTypeModel::available()`, `CartItemModel::effectivePrice()` (donation /\nHaarlemPas), `OrderModel::canPayLater()` (24-hour rule).\n\n```mermaid\n{$domain}```\n\n";
}

function md_decisions(): string
{
    return "## 4. Key design decisions\n\n- **Program against interfaces** â€” `IOrderService`, `IOrderRepository`, etc.\n- **Migrations** â€” append-only, tracked, reproducible.\n- **Passes as ticket types** â€” on a flagged `is_pass` event, reusing the whole flow.\n- **Effective price** â€” donations and the HaarlemPas 25% both resolve to\n  `cart_items.custom_price`, which becomes the order line `unit_price`.\n- **Reservations** â€” EUR 10 per-person fee + special requests on the order line.\n- **Pay later** â€” `pay_later_until = now + 24h`; retry re-validates deadline + stock.\n- **Security & GDPR** â€” hashed passwords, CSRF on every POST, prepared statements,\n  hardened session cookie, data export + anonymising erasure.\n";
}

function write_technical_text(string $out, string $body): void
{
    file_put_contents($out, $body);
    echo "wrote {$out}\n";
}

// --------------------------------------------------------------------------- //
// Render diagrams to JPEG, then build both deliverables.
$images = [];
foreach ($DIAGRAMS as [$key, $_title, $src]) {
    echo "rendering {$key} ... ";
    $data = render($src);
    $path = OUT_DIR . "/diagram_{$key}.jpg";
    file_put_contents($path, $data);
    $images[$key] = $path;
    echo "ok " . strlen($data) . " bytes\n";
}

build_docx($images);
build_markdown($ARCH, $ERD, $DOMAIN);
echo "done\n";

