-- All-access passes. A pass is modelled as a ticket type on a flagged "pass
-- event" for its event type, so it flows through cart, checkout, tickets and
-- the personal program unchanged. The is_pass flag keeps passes out of the
-- normal performance grid so they can be shown in a dedicated section.

ALTER TABLE events ADD COLUMN is_pass BOOLEAN NOT NULL DEFAULT 0;

-- Pass "events" (one per event type that offers passes).
SET @jazz  = (SELECT id FROM event_types WHERE slug = 'jazz');
SET @dance = (SELECT id FROM event_types WHERE slug = 'dance');

INSERT INTO events (event_type_id, title, description, starts_at, is_published, is_pass) VALUES
 (@jazz,  'Haarlem Jazz — All-access pass', 'Access every Haarlem Jazz session at the Patronaat for the chosen period.', '2026-07-23 00:00:00', 1, 1),
 (@dance, 'DANCE! — All-access pass',       'Access every DANCE! session for the chosen period (subject to club capacity).', '2026-07-23 00:00:00', 1, 1);

-- Jazz passes: 1 day EUR 35, 3 days EUR 80.
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'All-access pass (1 day)',  35.00, 9, 150, 1 FROM events WHERE is_pass = 1 AND event_type_id = @jazz;
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'All-access pass (3 days)', 80.00, 9, 150, 1 FROM events WHERE is_pass = 1 AND event_type_id = @jazz;

-- Dance passes: 1 day EUR 125 (Fri) / EUR 150 (Sat/Sun), 3 days EUR 250.
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'All-access pass (Friday)',     125.00, 9, 80, 1 FROM events WHERE is_pass = 1 AND event_type_id = @dance;
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'All-access pass (Sat/Sun day)',150.00, 9, 80, 1 FROM events WHERE is_pass = 1 AND event_type_id = @dance;
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'All-access pass (3 days)',     250.00, 9, 80, 1 FROM events WHERE is_pass = 1 AND event_type_id = @dance;
