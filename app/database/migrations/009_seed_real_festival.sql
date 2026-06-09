-- Replace the demo seed (migrations 007/008) with the real Festival 2026
-- programme: week 30, Thursday 23 – Sunday 26 July 2026.
-- Each programme line (a band slot, restaurant session, tour departure) is one
-- `events` row under its event type, with ticket types holding price/capacity.

-- --- Clear previous demo content (dev data reset; FK-safe order) -------------
DELETE FROM tickets;
DELETE FROM order_items;
DELETE FROM orders;
DELETE FROM cart_items;
DELETE FROM event_artist;
DELETE FROM ticket_types;
DELETE FROM events;
DELETE FROM artists;
DELETE FROM restaurants;
DELETE FROM venues;
DELETE FROM event_types;

-- --- Event types -------------------------------------------------------------
INSERT INTO event_types (slug, name, description, is_active) VALUES
 ('jazz',    'Haarlem Jazz',        'Different styles of jazz at Het Patronaat, with free open-air shows on the Grote Markt on Sunday.', 1),
 ('dance',   'DANCE!',              'Top DJs in characteristic Haarlem locations — Back2Back sessions and intimate club nights.', 1),
 ('yummy',   'Yummy! (Gourmet with a twist)', 'Haarlem restaurants serve a special festival menu at a reduced price.', 1),
 ('history', 'A Stroll through History', 'Guided walking tours past Haarlem''s historic landmarks, starting at the St. Bavo Church.', 1),
 ('magic',   'Magic@Teylers',       'A special interactive event for children (8–12): solve The Secret of Professor Teyler.', 1),
 ('stories', 'Stories in Haarlem',  'Storytelling, podcasts and more — in Dutch and English, for every age.', 1);

-- --- Venues ------------------------------------------------------------------
INSERT INTO venues (name, address, capacity) VALUES
 ('Patronaat',               'Zijlsingel 2, 2013 DN Haarlem', 800),
 ('Grote Markt',             'Grote Markt, Haarlem', 2000),
 ('Lichtfabriek',            'Minckelersweg 2, 2031 EM Haarlem', 1500),
 ('Slachthuis',              'Rockplein 6, 2033 KK Haarlem', 200),
 ('Jopenkerk',               'Gedempte Voldersgracht 2, 2011 WD Haarlem', 300),
 ('XO the Club',             'Grote Markt 8, 2011 RD Haarlem', 1500),
 ('Puncher comedy club',     'Grote Markt 10, 2011 RD Haarlem', 200),
 ('Caprera Openluchttheater','Hoge Duin en Daalseweg 2, 2061 AG Bloemendaal', 2000),
 ('St. Bavo Church',         'Grote Markt, Haarlem', 13),
 ('Teylers Museum',          'Spaarne 16, 2011 CH Haarlem', 100),
 ('Verhalenhuis Haarlem',    'van Egmondstraat 7, Haarlem-Noord', 60),
 ('De Schuur',               'Lange Begijnestraat 9, 2011 HH Haarlem', 80),
 ('Kweekcafe',               'Kleverlaan 9, 2023 JC Haarlem', 60),
 ('Corrie ten Boom huis',    'Barteljorisstraat 19, Haarlem', 40),
 ('Theater Elswout',         'Elswoutslaan 24-a, 2051 AE Overveen', 100);

-- --- Artists (Jazz bands + Dance DJs) ----------------------------------------
INSERT INTO artists (name, genre) VALUES
 ('Gumbo Kings','Jazz'), ('Evolve','Jazz'), ('Ntjam Rosie','Jazz'),
 ('Wicked Jazz Sounds','Jazz'), ('Wouter Hamel','Jazz'), ('Jonna Frazer','Jazz'),
 ('Karsu','Jazz'), ('Uncle Sue','Jazz'), ('Chris Allen','Jazz'),
 ('Myles Sanko','Jazz'), ('Ilse Huizinga','Jazz'), ('Eric Vloeimans and Hotspot','Jazz'),
 ('Gare du Nord','Jazz'), ('Rilan & The Bombadiers','Jazz'), ('Soul Six','Jazz'),
 ('Han Bennink','Jazz'), ('The Nordanians','Jazz'), ('Lilith Merlot','Jazz'),
 ('Ruis Soundsystem','Jazz'),
 ('Hardwell','Dance and house'), ('Armin van Buuren','Trance and techno'),
 ('Martin Garrix','Dance / electronic'), ('Tiesto','Trance, techno, house and electro'),
 ('Nicky Romero','Electrohouse / progressive house'), ('Afrojack','House');

-- --- Restaurants (Yummy) -----------------------------------------------------
INSERT INTO restaurants (name, cuisine, address, stars, price_per_seat) VALUES
 ('Cafe de Roemer','Dutch, fish and seafood, European','Botermarkt 17, 2011 XL Haarlem', 4, 35.00),
 ('Ratatouille','French, fish and seafood, European','Spaarne 96, 2011 CL Haarlem', 4, 45.00),
 ('Restaurant ML','Dutch, fish and seafood, European','Kleine Houtstraat 70, 2011 DR Haarlem', 4, 45.00),
 ('Restaurant Fris','Dutch, French, European','Twijnderslaan 7, 2012 BG Haarlem', 4, 45.00),
 ('New Vegas','Vegan','Koningstraat 5, 2011 TB Haarlem', 3, 35.00),
 ('Grand Cafe Brinkman','Dutch, European, Modern','Grote Markt 13, 2011 RC Haarlem', 3, 35.00),
 ('Urban Frenchy Bistro Toujours','Dutch, fish and seafood, European','Oude Groenmarkt 10-12, 2011 HL Haarlem', 3, 35.00);
