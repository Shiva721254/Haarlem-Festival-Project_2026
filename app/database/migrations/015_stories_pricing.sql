-- Stories pricing: pay-as-you-like (donation) tickets, and a 25% HaarlemPas
-- reduction on Stories entry fees. Both reduce to an effective per-line price
-- stored on the cart; the order keeps it as the line's unit_price.

ALTER TABLE cart_items   ADD COLUMN custom_price DECIMAL(8,2) NULL;
ALTER TABLE ticket_types ADD COLUMN is_donation TINYINT(1) NOT NULL DEFAULT 0;

-- The pay-as-you-like Stories act gets a donation ticket (price is the
-- suggested minimum; the customer chooses the amount).
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active, is_donation)
SELECT e.id, 'Pay what you like', 0.00, 9, 40, 1, 1
FROM events e
JOIN event_types et ON et.id = e.event_type_id
WHERE et.slug = 'stories' AND e.title = 'De geschiedenis van familie ten Boom';
