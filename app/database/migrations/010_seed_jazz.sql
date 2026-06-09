-- Haarlem Jazz programme. Thu/Fri/Sat at Patronaat (ticketed); Sunday free on
-- the Grote Markt. Prices: Main Hall EUR 15 (cap 300), other halls EUR 10.
-- All-access passes (EUR 35/day, EUR 80/3-day) are handled in a later step.

SET @jazz = (SELECT id FROM event_types WHERE slug = 'jazz');
SET @patronaat = (SELECT id FROM venues WHERE name = 'Patronaat');
SET @grotemarkt = (SELECT id FROM venues WHERE name = 'Grote Markt');

INSERT INTO events (event_type_id, venue_id, title, description, starts_at, ends_at, is_published) VALUES
 -- Thursday, Main Hall
 (@jazz, @patronaat, 'Gumbo Kings',  'Haarlem Jazz — Main Hall, Patronaat',   '2026-07-23 18:00:00', '2026-07-23 19:00:00', 1),
 (@jazz, @patronaat, 'Evolve',       'Haarlem Jazz — Main Hall, Patronaat',   '2026-07-23 19:30:00', '2026-07-23 20:30:00', 1),
 (@jazz, @patronaat, 'Ntjam Rosie',  'Haarlem Jazz — Main Hall, Patronaat',   '2026-07-23 21:00:00', '2026-07-23 22:00:00', 1),
 -- Thursday, Second Hall
 (@jazz, @patronaat, 'Wicked Jazz Sounds', 'Haarlem Jazz — Second Hall, Patronaat', '2026-07-23 18:00:00', '2026-07-23 19:00:00', 1),
 (@jazz, @patronaat, 'Wouter Hamel', 'Haarlem Jazz — Second Hall, Patronaat', '2026-07-23 19:30:00', '2026-07-23 20:30:00', 1),
 (@jazz, @patronaat, 'Jonna Frazer', 'Haarlem Jazz — Second Hall, Patronaat', '2026-07-23 21:00:00', '2026-07-23 22:00:00', 1),
 -- Friday, Main Hall
 (@jazz, @patronaat, 'Karsu',        'Haarlem Jazz — Main Hall, Patronaat',   '2026-07-24 18:00:00', '2026-07-24 19:00:00', 1),
 (@jazz, @patronaat, 'Uncle Sue',    'Haarlem Jazz — Main Hall, Patronaat',   '2026-07-24 19:30:00', '2026-07-24 20:30:00', 1),
 (@jazz, @patronaat, 'Chris Allen',  'Haarlem Jazz — Main Hall, Patronaat',   '2026-07-24 21:00:00', '2026-07-24 22:00:00', 1),
 -- Friday, Second Hall
 (@jazz, @patronaat, 'Myles Sanko',  'Haarlem Jazz — Second Hall, Patronaat', '2026-07-24 18:00:00', '2026-07-24 19:00:00', 1),
 (@jazz, @patronaat, 'Ilse Huizinga','Haarlem Jazz — Second Hall, Patronaat', '2026-07-24 19:30:00', '2026-07-24 20:30:00', 1),
 (@jazz, @patronaat, 'Eric Vloeimans and Hotspot', 'Haarlem Jazz — Second Hall, Patronaat', '2026-07-24 21:00:00', '2026-07-24 22:00:00', 1),
 -- Saturday, Main Hall
 (@jazz, @patronaat, 'Gare du Nord', 'Haarlem Jazz — Main Hall, Patronaat',   '2026-07-25 18:00:00', '2026-07-25 19:00:00', 1),
 (@jazz, @patronaat, 'Rilan & The Bombadiers', 'Haarlem Jazz — Main Hall, Patronaat', '2026-07-25 19:30:00', '2026-07-25 20:30:00', 1),
 (@jazz, @patronaat, 'Soul Six',     'Haarlem Jazz — Main Hall, Patronaat',   '2026-07-25 21:00:00', '2026-07-25 22:00:00', 1),
 -- Saturday, Third Hall
 (@jazz, @patronaat, 'Han Bennink',  'Haarlem Jazz — Third Hall, Patronaat',  '2026-07-25 18:00:00', '2026-07-25 19:00:00', 1),
 (@jazz, @patronaat, 'The Nordanians','Haarlem Jazz — Third Hall, Patronaat', '2026-07-25 19:30:00', '2026-07-25 20:30:00', 1),
 (@jazz, @patronaat, 'Lilith Merlot','Haarlem Jazz — Third Hall, Patronaat',  '2026-07-25 21:00:00', '2026-07-25 22:00:00', 1),
 -- Sunday, free on the Grote Markt
 (@jazz, @grotemarkt, 'Ruis Soundsystem', 'Haarlem Jazz — free open-air, Grote Markt', '2026-07-26 15:00:00', '2026-07-26 16:00:00', 1),
 (@jazz, @grotemarkt, 'Wicked Jazz Sounds','Haarlem Jazz — free open-air, Grote Markt', '2026-07-26 16:00:00', '2026-07-26 17:00:00', 1),
 (@jazz, @grotemarkt, 'Evolve',           'Haarlem Jazz — free open-air, Grote Markt', '2026-07-26 17:00:00', '2026-07-26 18:00:00', 1),
 (@jazz, @grotemarkt, 'The Nordanians',   'Haarlem Jazz — free open-air, Grote Markt', '2026-07-26 18:00:00', '2026-07-26 19:00:00', 1),
 (@jazz, @grotemarkt, 'Gumbo Kings',      'Haarlem Jazz — free open-air, Grote Markt', '2026-07-26 19:00:00', '2026-07-26 20:00:00', 1),
 (@jazz, @grotemarkt, 'Gare du Nord',     'Haarlem Jazz — free open-air, Grote Markt', '2026-07-26 20:00:00', '2026-07-26 21:00:00', 1);

-- Ticket types: Main Hall EUR 15 / cap 300, other halls EUR 10 / cap 200 or 150.
-- Sunday Grote Markt shows are free (no ticket type, no reservation needed).
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT e.id, 'Single ticket', 15.00, 9, 300, 1
FROM events e
WHERE e.event_type_id = @jazz AND e.description LIKE 'Haarlem Jazz — Main Hall%';

INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT e.id, 'Single ticket', 10.00, 9, 200, 1
FROM events e
WHERE e.event_type_id = @jazz AND e.description LIKE 'Haarlem Jazz — Second Hall%';

INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT e.id, 'Single ticket', 10.00, 9, 150, 1
FROM events e
WHERE e.event_type_id = @jazz AND e.description LIKE 'Haarlem Jazz — Third Hall%';

-- Line-up: each jazz event maps to the artist with the same name.
INSERT INTO event_artist (event_id, artist_id)
SELECT e.id, a.id
FROM events e JOIN artists a ON a.name = e.title
WHERE e.event_type_id = @jazz;
