-- Reservations: capture special requests (allergies, diets, wheelchair) per
-- cart/order line, and charge Yummy as a EUR 10 per-person reservation fee
-- (the menu price is settled at the restaurant, less this fee).

ALTER TABLE cart_items  ADD COLUMN special_requests VARCHAR(500) NULL;
ALTER TABLE order_items ADD COLUMN special_requests VARCHAR(500) NULL;

-- Yummy ticket types become a per-person reservation fee.
UPDATE ticket_types tt
JOIN events e ON e.id = tt.event_id
JOIN event_types et ON et.id = e.event_type_id
SET tt.name = 'Reservation (per person)', tt.price = 10.00
WHERE et.slug = 'yummy';
