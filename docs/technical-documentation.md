# Haarlem Festival — Technical Documentation

Ticketing website for The Festival (Inholland 2.3). Plain PHP MVC, MySQL, Docker,
Stripe (test) payment.

## 1. Architecture

Requests are routed to a **Controller**, which delegates to a **Service** (business
rules) that uses a **Repository** (data access). Every Service and Repository is
consumed through an **interface**, so the code is programmed against abstractions.
Repositories extend a base `Repository` wrapping PDO with prepared statements.
Cross-cutting: `AuthMiddleware` (auth, roles, CSRF), `View`, `Flash`, `Container`.

```mermaid
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
```

## 2. Database design

The schema is built by numbered, forward-only SQL **migrations**
(`database/migrate.php`). Money is `DECIMAL`; prices are VAT-inclusive and the VAT
portion is derived per line.

```mermaid
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
```

## 3. Domain model

Domain classes mirror the schema and carry small behaviours — e.g.
`TicketTypeModel::available()`, `CartItemModel::effectivePrice()` (donation /
HaarlemPas), `OrderModel::canPayLater()` (24-hour rule).

```mermaid
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
```

## 4. Key design decisions

- **Program against interfaces** — `IOrderService`, `IOrderRepository`, etc.
- **Migrations** — append-only, tracked, reproducible.
- **Passes as ticket types** — on a flagged `is_pass` event, reusing the whole flow.
- **Effective price** — donations and the HaarlemPas 25% both resolve to
  `cart_items.custom_price`, which becomes the order line `unit_price`.
- **Reservations** — EUR 10 per-person fee + special requests on the order line.
- **Pay later** — `pay_later_until = now + 24h`; retry re-validates deadline + stock.
- **Security & GDPR** — hashed passwords, CSRF on every POST, prepared statements,
  hardened session cookie, data export + anonymising erasure.
