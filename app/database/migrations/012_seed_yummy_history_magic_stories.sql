-- Yummy (restaurant sessions), A Stroll through History (tour departures),
-- Magic@Teylers (kids event) and Stories in Haarlem. Seeded for Saturday
-- 25 July 2026 (the usability-test day); more days can be added later.
-- VAT 9% applies to food and cultural tickets. Reservation fees, family/reduced
-- pricing, pay-as-you-like and HaarlemPas discounts are handled in later steps.

SET @yummy   = (SELECT id FROM event_types WHERE slug='yummy');
SET @history = (SELECT id FROM event_types WHERE slug='history');
SET @magic   = (SELECT id FROM event_types WHERE slug='magic');
SET @stories = (SELECT id FROM event_types WHERE slug='stories');

-- ============================ YUMMY ========================================
-- One event per restaurant session; capacity = the restaurant's seats.
INSERT INTO events (event_type_id, restaurant_id, title, description, starts_at, ends_at, is_published) VALUES
 (@yummy,(SELECT id FROM restaurants WHERE name='Cafe de Roemer'),'Cafe de Roemer','Yummy! festival menu — reservation required','2026-07-25 18:00:00','2026-07-25 19:30:00',1),
 (@yummy,(SELECT id FROM restaurants WHERE name='Cafe de Roemer'),'Cafe de Roemer','Yummy! festival menu — reservation required','2026-07-25 19:30:00','2026-07-25 21:00:00',1),
 (@yummy,(SELECT id FROM restaurants WHERE name='Ratatouille'),'Ratatouille','Yummy! festival menu — reservation required','2026-07-25 17:00:00','2026-07-25 19:00:00',1),
 (@yummy,(SELECT id FROM restaurants WHERE name='Ratatouille'),'Ratatouille','Yummy! festival menu — reservation required','2026-07-25 19:00:00','2026-07-25 21:00:00',1),
 (@yummy,(SELECT id FROM restaurants WHERE name='Restaurant ML'),'Restaurant ML','Yummy! festival menu — reservation required','2026-07-25 17:00:00','2026-07-25 19:00:00',1),
 (@yummy,(SELECT id FROM restaurants WHERE name='Restaurant ML'),'Restaurant ML','Yummy! festival menu — reservation required','2026-07-25 19:00:00','2026-07-25 21:00:00',1),
 (@yummy,(SELECT id FROM restaurants WHERE name='Restaurant Fris'),'Restaurant Fris','Yummy! festival menu — reservation required','2026-07-25 17:30:00','2026-07-25 19:00:00',1),
 (@yummy,(SELECT id FROM restaurants WHERE name='New Vegas'),'New Vegas','Yummy! vegan festival menu — reservation required','2026-07-25 17:00:00','2026-07-25 18:30:00',1),
 (@yummy,(SELECT id FROM restaurants WHERE name='Grand Cafe Brinkman'),'Grand Cafe Brinkman','Yummy! festival menu — reservation required','2026-07-25 16:30:00','2026-07-25 18:00:00',1),
 (@yummy,(SELECT id FROM restaurants WHERE name='Urban Frenchy Bistro Toujours'),'Urban Frenchy Bistro Toujours','Yummy! festival menu — reservation required','2026-07-25 17:30:00','2026-07-25 19:00:00',1);

INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT e.id, 'Dinner reservation', r.price_per_seat, 9, s.seats_fallback, 1
FROM events e
JOIN restaurants r ON r.id = e.restaurant_id
JOIN (SELECT 'Cafe de Roemer' AS name, 35 AS seats_fallback UNION ALL
      SELECT 'Ratatouille',52 UNION ALL SELECT 'Restaurant ML',60 UNION ALL
      SELECT 'Restaurant Fris',45 UNION ALL SELECT 'New Vegas',36 UNION ALL
      SELECT 'Grand Cafe Brinkman',100 UNION ALL SELECT 'Urban Frenchy Bistro Toujours',48) s ON s.name = r.name
WHERE e.event_type_id = @yummy;

-- ============================ HISTORY ======================================
-- Saturday tour departures from St. Bavo Church (12 seats/tour, EUR 17.50,
-- plus a family ticket for up to 4 people at EUR 60). Reservation mandatory.
SET @bavo = (SELECT id FROM venues WHERE name='St. Bavo Church');
INSERT INTO events (event_type_id, venue_id, title, description, starts_at, ends_at, is_published) VALUES
 (@history,@bavo,'Stroll through History (English)','Guided walk, in English. Min. age 12; includes one drink.','2026-07-25 10:00:00','2026-07-25 12:30:00',1),
 (@history,@bavo,'Stroll through History (Dutch)',  'Guided walk, in Dutch. Min. age 12; includes one drink.','2026-07-25 10:00:00','2026-07-25 12:30:00',1),
 (@history,@bavo,'Stroll through History (English)','Guided walk, in English. Min. age 12; includes one drink.','2026-07-25 13:00:00','2026-07-25 15:30:00',1),
 (@history,@bavo,'Stroll through History (Dutch)',  'Guided walk, in Dutch. Min. age 12; includes one drink.','2026-07-25 13:00:00','2026-07-25 15:30:00',1),
 (@history,@bavo,'Stroll through History (Mandarin)','Guided walk, in Mandarin. Min. age 12; includes one drink.','2026-07-25 13:00:00','2026-07-25 15:30:00',1),
 (@history,@bavo,'Stroll through History (English)','Guided walk, in English. Min. age 12; includes one drink.','2026-07-25 16:00:00','2026-07-25 18:30:00',1),
 (@history,@bavo,'Stroll through History (Dutch)',  'Guided walk, in Dutch. Min. age 12; includes one drink.','2026-07-25 16:00:00','2026-07-25 18:30:00',1);

INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Regular (incl. 1 drink)', 17.50, 9, 12, 1 FROM events WHERE event_type_id=@history;
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Family ticket (max 4)', 60.00, 9, 3, 1 FROM events WHERE event_type_id=@history;

-- ============================ MAGIC@TEYLERS ================================
-- Kids program; participation is at the museum via the mobile app (no online
-- ticket here). One informational event per festival day would be added later;
-- seed Saturday's as the representative entry.
INSERT INTO events (event_type_id, venue_id, title, description, starts_at, ends_at, is_published) VALUES
 (@magic,(SELECT id FROM venues WHERE name='Teylers Museum'),'The Secret of Professor Teyler',
  'Interactive science experience for children (8–12). Download the festival app and buy your ticket at Teylers Museum — no additional online cost.',
  '2026-07-25 10:00:00','2026-07-25 17:00:00',1);

-- ============================ STORIES ======================================
INSERT INTO events (event_type_id, venue_id, title, description, starts_at, ends_at, is_published) VALUES
 (@stories,(SELECT id FROM venues WHERE name='Theater Elswout'),'Meneer Anansi','Stories for the whole family (Dutch). Ages 2+.','2026-07-25 10:00:00','2026-07-25 11:00:00',1),
 (@stories,(SELECT id FROM venues WHERE name='Theater Elswout'),'Mister Anansi','Stories for the whole family (English). Ages 2+.','2026-07-25 15:00:00','2026-07-25 16:00:00',1),
 (@stories,(SELECT id FROM venues WHERE name='De Schuur'),'Podcastlast Haarlem Special','Recording a podcast with a live audience (Dutch). Ages 12+.','2026-07-25 14:00:00','2026-07-25 15:15:00',1),
 (@stories,(SELECT id FROM venues WHERE name='Corrie ten Boom huis'),'De geschiedenis van familie ten Boom','Stories with impact (Dutch) — pay as you like. Ages 12+.','2026-07-25 13:00:00','2026-07-25 14:30:00',1);

-- Priced stories get a ticket type; the pay-as-you-like one is handled later.
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 10.00, 9, 100, 1 FROM events WHERE event_type_id=@stories AND title IN ('Meneer Anansi','Mister Anansi');
INSERT INTO ticket_types (event_id, name, price, vat_rate, capacity, is_active)
SELECT id, 'Single ticket', 12.50, 9, 80, 1 FROM events WHERE event_type_id=@stories AND title='Podcastlast Haarlem Special';
