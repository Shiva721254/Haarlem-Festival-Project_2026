-- DANCE! programme. Back2Back (large stage, long set) and Club sessions.
-- Single-ticket capacity is 90% of total (10% kept for walk-ins/pass holders).
-- One-day/3-day passes are added in a later step.

SET @dance = (SELECT id FROM event_types WHERE slug = 'dance');

INSERT INTO events (event_type_id, venue_id, title, description, starts_at, ends_at, is_published) VALUES
 -- Friday
 (@dance, (SELECT id FROM venues WHERE name='Lichtfabriek'),        'Nicky Romero & Afrojack (Back2Back)', 'DANCE! — Back2Back, Lichtfabriek', '2026-07-24 20:00:00', '2026-07-25 02:00:00', 1),
 (@dance, (SELECT id FROM venues WHERE name='Slachthuis'),          'Tiesto',            'DANCE! — Club, Slachthuis',          '2026-07-24 22:00:00', '2026-07-24 23:30:00', 1),
 (@dance, (SELECT id FROM venues WHERE name='Jopenkerk'),           'Hardwell',          'DANCE! — Club, Jopenkerk',           '2026-07-24 23:00:00', '2026-07-25 00:30:00', 1),
 (@dance, (SELECT id FROM venues WHERE name='XO the Club'),         'Armin van Buuren',  'DANCE! — Club, XO the Club',         '2026-07-24 22:00:00', '2026-07-24 23:30:00', 1),
 (@dance, (SELECT id FROM venues WHERE name='Puncher comedy club'), 'Martin Garrix',     'DANCE! — Club, Puncher comedy club', '2026-07-24 22:00:00', '2026-07-24 23:30:00', 1),
 -- Saturday
 (@dance, (SELECT id FROM venues WHERE name='Caprera Openluchttheater'), 'Hardwell, Martin Garrix & Armin van Buuren (Back2Back)', 'DANCE! — Back2Back, Caprera Openluchttheater', '2026-07-25 14:00:00', '2026-07-25 23:00:00', 1),
 (@dance, (SELECT id FROM venues WHERE name='Jopenkerk'),           'Afrojack',          'DANCE! — Club, Jopenkerk',           '2026-07-25 22:00:00', '2026-07-25 23:30:00', 1),
 (@dance, (SELECT id FROM venues WHERE name='Lichtfabriek'),        'Tiesto (TiestoWorld)', 'DANCE! — TiestoWorld, Lichtfabriek', '2026-07-25 21:00:00', '2026-07-26 01:00:00', 1),
 (@dance, (SELECT id FROM venues WHERE name='Slachthuis'),          'Nicky Romero',      'DANCE! — Club, Slachthuis',          '2026-07-25 23:00:00', '2026-07-26 00:30:00', 1),
 -- Sunday
 (@dance, (SELECT id FROM venues WHERE name='Caprera Openluchttheater'), 'Afrojack, Tiesto & Nicky Romero (Back2Back)', 'DANCE! — Back2Back, Caprera Openluchttheater', '2026-07-26 14:00:00', '2026-07-26 23:00:00', 1),
 (@dance, (SELECT id FROM venues WHERE name='Jopenkerk'),           'Armin van Buuren',  'DANCE! — Club, Jopenkerk',           '2026-07-26 19:00:00', '2026-07-26 20:30:00', 1),
 (@dance, (SELECT id FROM venues WHERE name='XO the Club'),         'Hardwell',          'DANCE! — Club, XO the Club',         '2026-07-26 21:00:00', '2026-07-26 22:30:00', 1),
 (@dance, (SELECT id FROM venues WHERE name='Slachthuis'),          'Martin Garrix',     'DANCE! — Club, Slachthuis',          '2026-07-26 18:00:00', '2026-07-26 19:30:00', 1);

-- Ticket types (price + 90%-of-total capacity), one per session.
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 75.00, 9, 1350, 1 FROM events WHERE event_type_id=@dance AND title='Nicky Romero & Afrojack (Back2Back)' AND starts_at='2026-07-24 20:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 60.00, 9, 180, 1 FROM events WHERE event_type_id=@dance AND title='Tiesto' AND starts_at='2026-07-24 22:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 60.00, 9, 270, 1 FROM events WHERE event_type_id=@dance AND title='Hardwell' AND starts_at='2026-07-24 23:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 60.00, 9, 180, 1 FROM events WHERE event_type_id=@dance AND title='Armin van Buuren' AND starts_at='2026-07-24 22:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 60.00, 9, 180, 1 FROM events WHERE event_type_id=@dance AND title='Martin Garrix' AND starts_at='2026-07-24 22:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 110.00, 9, 1800, 1 FROM events WHERE event_type_id=@dance AND title='Hardwell, Martin Garrix & Armin van Buuren (Back2Back)' AND starts_at='2026-07-25 14:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 60.00, 9, 270, 1 FROM events WHERE event_type_id=@dance AND title='Afrojack' AND starts_at='2026-07-25 22:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 75.00, 9, 1350, 1 FROM events WHERE event_type_id=@dance AND title='Tiesto (TiestoWorld)' AND starts_at='2026-07-25 21:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 60.00, 9, 180, 1 FROM events WHERE event_type_id=@dance AND title='Nicky Romero' AND starts_at='2026-07-25 23:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 110.00, 9, 1800, 1 FROM events WHERE event_type_id=@dance AND title='Afrojack, Tiesto & Nicky Romero (Back2Back)' AND starts_at='2026-07-26 14:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 60.00, 9, 270, 1 FROM events WHERE event_type_id=@dance AND title='Armin van Buuren' AND starts_at='2026-07-26 19:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 90.00, 9, 1350, 1 FROM events WHERE event_type_id=@dance AND title='Hardwell' AND starts_at='2026-07-26 21:00:00';
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 60.00, 9, 180, 1 FROM events WHERE event_type_id=@dance AND title='Martin Garrix' AND starts_at='2026-07-26 18:00:00';

-- Line-up: single-artist sessions link by name.
INSERT INTO event_artist (event_id, artist_id)
SELECT e.id, a.id FROM events e JOIN artists a ON a.name = e.title
WHERE e.event_type_id = @dance;

-- Back2Back sessions: link each participating artist explicitly.
INSERT INTO event_artist (event_id, artist_id)
SELECT e.id, a.id FROM events e JOIN artists a ON a.name IN ('Nicky Romero','Afrojack')
WHERE e.title='Nicky Romero & Afrojack (Back2Back)';
INSERT INTO event_artist (event_id, artist_id)
SELECT e.id, a.id FROM events e JOIN artists a ON a.name IN ('Hardwell','Martin Garrix','Armin van Buuren')
WHERE e.title='Hardwell, Martin Garrix & Armin van Buuren (Back2Back)';
INSERT INTO event_artist (event_id, artist_id)
SELECT e.id, a.id FROM events e JOIN artists a ON a.name IN ('Afrojack','Tiesto','Nicky Romero')
WHERE e.title='Afrojack, Tiesto & Nicky Romero (Back2Back)';
INSERT INTO event_artist (event_id, artist_id)
SELECT e.id, a.id FROM events e JOIN artists a ON a.name='Tiesto'
WHERE e.title='Tiesto (TiestoWorld)';
