-- What can actually be purchased for an event, and how much stock remains.
-- A single event can offer several ticket types (single ticket, day pass,
-- all-access pass). Availability is enforced via `capacity` vs `sold`.

CREATE TABLE IF NOT EXISTS ticket_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,                -- 'Single ticket', 'Day pass', 'All-access pass'
    price DECIMAL(8,2) NOT NULL,
    vat_rate DECIMAL(4,2) NOT NULL DEFAULT 21.00,  -- 21% or 9% per requirements
    capacity INT NOT NULL DEFAULT 0,           -- max sellable (90% rule applied at app layer)
    sold INT NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tt_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
