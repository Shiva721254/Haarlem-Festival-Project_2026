-- Sample catalog data so the event pages render during development.
-- Safe to keep: real content will be managed through the CMS later.

INSERT INTO event_types (slug, name, description, is_active) VALUES
    ('jazz',    'Haarlem Jazz',     'Three days of jazz across the city.', 1),
    ('dance',   'Dance',            'Late-night dance events and DJs.',     1),
    ('yummy',   'Yummy',            'Dine at participating restaurants.',   1),
    ('history', 'Historic Haarlem', 'Guided walks through Haarlem.',        1);

INSERT INTO venues (name, address, capacity, description) VALUES
    ('Grote Markt Stage', 'Grote Markt 1, Haarlem', 800, 'Open-air main stage on the central square.'),
    ('Patronaat',         'Zijlsingel 2, Haarlem',  500, 'Iconic Haarlem music venue.');

INSERT INTO artists (name, genre, bio) VALUES
    ('The Blue Notes',  'Jazz',      'A five-piece bringing classic bebop to the square.'),
    ('DJ Tulip',        'House',     'Amsterdam-based DJ known for sunrise sets.'),
    ('Quartet Amstel',  'Jazz',      'Contemporary jazz quartet.');

INSERT INTO restaurants (name, cuisine, description, address, stars, price_per_seat) VALUES
    ('Ratatouille',     'French',    'Refined French dining near the Grote Markt.', 'Spaarne 96, Haarlem', 2, 75.00),
    ('ML Restaurant',   'European',  'Michelin-listed seasonal menu.',              'Klokhuisplein 9, Haarlem', 1, 90.00);

INSERT INTO events (event_type_id, venue_id, restaurant_id, title, description, starts_at, ends_at, is_published) VALUES
    ((SELECT id FROM event_types WHERE slug='jazz'),
     (SELECT id FROM venues WHERE name='Grote Markt Stage'), NULL,
     'Opening Night: The Blue Notes', 'Kick off the festival with classic jazz.',
     '2026-08-21 20:00:00', '2026-08-21 23:00:00', 1),
    ((SELECT id FROM event_types WHERE slug='jazz'),
     (SELECT id FROM venues WHERE name='Patronaat'), NULL,
     'Quartet Amstel Live', 'An evening of contemporary jazz.',
     '2026-08-22 21:00:00', '2026-08-22 23:30:00', 1),
    ((SELECT id FROM event_types WHERE slug='dance'),
     (SELECT id FROM venues WHERE name='Patronaat'), NULL,
     'Sunrise Set with DJ Tulip', 'Dance until the sun comes up.',
     '2026-08-23 23:00:00', '2026-08-24 05:00:00', 1),
    ((SELECT id FROM event_types WHERE slug='yummy'), NULL,
     (SELECT id FROM restaurants WHERE name='Ratatouille'),
     'Dinner at Ratatouille', 'A three-course French tasting menu.',
     '2026-08-22 19:00:00', '2026-08-22 21:30:00', 1);

INSERT INTO event_artist (event_id, artist_id) VALUES
    ((SELECT id FROM events WHERE title='Opening Night: The Blue Notes'),
     (SELECT id FROM artists WHERE name='The Blue Notes')),
    ((SELECT id FROM events WHERE title='Quartet Amstel Live'),
     (SELECT id FROM artists WHERE name='Quartet Amstel')),
    ((SELECT id FROM events WHERE title='Sunrise Set with DJ Tulip'),
     (SELECT id FROM artists WHERE name='DJ Tulip'));
