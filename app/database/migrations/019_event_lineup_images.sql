-- Event overview images used by the shared public lineup cards.
-- These paths point to committed files under public/assets/images.

SET @dance = (SELECT id FROM event_types WHERE slug = 'dance');
SET @jazz = (SELECT id FROM event_types WHERE slug = 'jazz');
SET @yummy = (SELECT id FROM event_types WHERE slug = 'yummy');
SET @magic = (SELECT id FROM event_types WHERE slug = 'magic');
SET @stories = (SELECT id FROM event_types WHERE slug = 'stories');

-- DANCE!
UPDATE events SET image = '/assets/images/dance/nicky-romero.jpg'
WHERE event_type_id = @dance AND title IN ('Nicky Romero & Afrojack (Back2Back)', 'Nicky Romero');
UPDATE events SET image = '/assets/images/dance/tiesto.jpg'
WHERE event_type_id = @dance AND title IN ('Tiesto', 'Tiesto (TiestoWorld)');
UPDATE events SET image = '/assets/images/dance/armin-van-buuren.jpg'
WHERE event_type_id = @dance AND title = 'Armin van Buuren';
UPDATE events SET image = '/assets/images/dance/martin-garrix.jpg'
WHERE event_type_id = @dance AND title = 'Martin Garrix';
UPDATE events SET image = '/assets/images/dance/hardwell.jpg'
WHERE event_type_id = @dance AND title IN ('Hardwell', 'Hardwell, Martin Garrix & Armin van Buuren (Back2Back)');
UPDATE events SET image = '/assets/images/dance/afrojack.jpg'
WHERE event_type_id = @dance AND title IN ('Afrojack', 'Afrojack, Tiesto & Nicky Romero (Back2Back)');

-- Haarlem Jazz
UPDATE events SET image = '/assets/images/jazz/gumbo-kings.jpg'
WHERE event_type_id = @jazz AND title = 'Gumbo Kings';
UPDATE events SET image = '/assets/images/jazz/wicked-jazz-sounds.jpg'
WHERE event_type_id = @jazz AND title = 'Wicked Jazz Sounds';
UPDATE events SET image = '/assets/images/jazz/ntjam-rosie.jpg'
WHERE event_type_id = @jazz AND title = 'Ntjam Rosie';
UPDATE events SET image = '/assets/images/jazz/karsu.jpg'
WHERE event_type_id = @jazz AND title = 'Karsu';
UPDATE events SET image = '/assets/images/jazz/myles-sanko.jpg'
WHERE event_type_id = @jazz AND title = 'Myles Sanko';
UPDATE events SET image = '/assets/images/jazz/eric-vloeimans.jpg'
WHERE event_type_id = @jazz AND title = 'Eric Vloeimans and Hotspot';
UPDATE events SET image = '/assets/images/jazz/rilan-bombardiers.png'
WHERE event_type_id = @jazz AND title = 'Rilan & The Bombadiers';
UPDATE events SET image = '/assets/images/jazz/lilith-merlot.jpg'
WHERE event_type_id = @jazz AND title = 'Lilith Merlot';
UPDATE events SET image = '/assets/images/jazz/han-bennink.jpg'
WHERE event_type_id = @jazz AND title = 'Han Bennink';
UPDATE events SET image = '/assets/images/jazz/jazz-live-club.jpg'
WHERE event_type_id = @jazz
  AND title IN ('Evolve', 'Wouter Hamel', 'Jonna Frazer', 'Uncle Sue', 'Ilse Huizinga', 'Gare du Nord', 'The Nordanians', 'Soul Six', 'Ruis Soundsystem');
UPDATE events SET image = '/assets/images/chris.jpg'
WHERE event_type_id = @jazz AND title = 'Chris Allen';

-- Yummy!
UPDATE events SET image = '/assets/images/yummy/cafe-de-roemer.png'
WHERE event_type_id = @yummy AND title = 'Cafe de Roemer';
UPDATE events SET image = '/assets/images/yummy/ratatouille.jpg'
WHERE event_type_id = @yummy AND title = 'Ratatouille';
UPDATE events SET image = '/assets/images/yummy/restaurant-ml.jpg'
WHERE event_type_id = @yummy AND title = 'Restaurant ML';
UPDATE events SET image = '/assets/images/yummy/restaurant-fris.jpg'
WHERE event_type_id = @yummy AND title = 'Restaurant Fris';
UPDATE events SET image = '/assets/images/yummy/new-vegas.jpeg'
WHERE event_type_id = @yummy AND title = 'New Vegas';
UPDATE events SET image = '/assets/images/yummy/grand-cafe-brinkman.webp'
WHERE event_type_id = @yummy AND title = 'Grand Cafe Brinkman';
UPDATE events SET image = '/assets/images/yummy/urban-frenchy-bistro-toujours.jpg'
WHERE event_type_id = @yummy AND title = 'Urban Frenchy Bistro Toujours';

-- Magic@Teylers
UPDATE events SET image = '/assets/images/magic/the-secret-of-professor-teyler.jpg'
WHERE event_type_id = @magic AND title = 'The Secret of Professor Teyler';

-- Stories in Haarlem
UPDATE events SET image = '/assets/images/stories/meneer-anansi.jpg'
WHERE event_type_id = @stories AND title = 'Meneer Anansi';
UPDATE events SET image = '/assets/images/stories/mister-anansi.jpg'
WHERE event_type_id = @stories AND title = 'Mister Anansi';
UPDATE events SET image = '/assets/images/stories/podcastlast.jpg'
WHERE event_type_id = @stories AND title = 'Podcastlast Haarlem Special';
UPDATE events SET image = '/assets/images/stories/ten-boom-family.jpg'
WHERE event_type_id = @stories AND title = 'De geschiedenis van familie ten Boom';
