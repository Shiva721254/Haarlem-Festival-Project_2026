-- Real content for the DANCE! artists, sourced from Wikipedia (text) and
-- Wikimedia Commons (portraits, freely licensed — attribution belongs to the
-- respective Commons authors). Audio stays a simulated sample (real tracks are
-- copyrighted). Replaces the placeholder picsum gallery for these artists with
-- the real portrait; more images can be uploaded via the admin gallery.

-- Armin van Buuren
UPDATE artists SET
    bio = 'Dutch DJ, musician and record producer.',
    career_highlights = 'Hosts A State of Trance (ASOT) since 2001, a weekly radio show broadcast to nearly 40 million listeners across 84 countries. Five-time DJ Mag Top 100 #1.',
    tracks = 'This Is What It Feels Like; In and Out of Love; Blah Blah Blah',
    image = 'https://upload.wikimedia.org/wikipedia/commons/9/90/Armin_van_Buuren%2C_November_2025_%28cropped%29.jpg'
WHERE name = 'Armin van Buuren';

-- Tiesto
UPDATE artists SET
    bio = 'Dutch DJ and record producer, often called the "Godfather of EDM".',
    career_highlights = 'Voted "The Greatest DJ of All Time" by Mix magazine (2010/2011) and "best DJ of the last 20 years" by DJ Mag readers in 2013.',
    tracks = 'Adagio for Strings; Red Lights; The Business',
    image = 'https://upload.wikimedia.org/wikipedia/commons/8/81/Ti%C3%ABsto.jpg'
WHERE name = 'Tiesto';

-- Hardwell
UPDATE artists SET
    bio = 'Dutch DJ and record producer.',
    career_highlights = 'Voted the world''s number one DJ by DJ Mag in 2013 and again in 2014. Founder of the label Revealed Recordings.',
    tracks = 'Spaceman; Apollo; Dare You',
    image = 'https://upload.wikimedia.org/wikipedia/commons/2/27/Hardwell_%282025%29.jpg'
WHERE name = 'Hardwell';

-- Martin Garrix
UPDATE artists SET
    bio = 'Dutch DJ, remixer and music producer.',
    career_highlights = 'Best known for "Animals", "In the Name of Love" and "Scared to Be Lonely". Ranked #1 on DJ Mag''s Top 100 DJs in 2016, 2017, 2018, 2022 and 2024.',
    tracks = 'Animals; In the Name of Love; Scared to Be Lonely',
    audio_url = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3',
    image = 'https://upload.wikimedia.org/wikipedia/commons/7/74/Martin_Garrix_%40_Web_Summit_2017.jpg'
WHERE name = 'Martin Garrix';

-- Afrojack
UPDATE artists SET
    bio = 'Dutch DJ, record producer and remixer.',
    career_highlights = 'Founded the record label Wall Recordings in 2007; released his debut album "Forget the World" in 2014.',
    tracks = 'Take Over Control; Ten Feet Tall; The Spark',
    audio_url = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-6.mp3',
    image = 'https://upload.wikimedia.org/wikipedia/commons/5/53/Afrojack_2015.jpg'
WHERE name = 'Afrojack';

-- Nicky Romero
UPDATE artists SET
    bio = 'Dutch DJ, record producer and remixer from Amerongen, Utrecht.',
    career_highlights = 'Known for progressive and electro-house productions and remixes; also records as Monocule.',
    tracks = 'Toulouse; I Could Be the One; Legacy',
    audio_url = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-7.mp3',
    image = 'https://upload.wikimedia.org/wikipedia/commons/d/d8/Nicky_Romero_Nofame.jpg'
WHERE name = 'Nicky Romero';

-- Replace placeholder gallery images for these artists with the real portrait.
DELETE ai FROM artist_images ai
JOIN artists a ON a.id = ai.artist_id
WHERE a.name IN ('Armin van Buuren','Tiesto','Hardwell','Martin Garrix','Afrojack','Nicky Romero')
  AND ai.path LIKE '%picsum.photos%';

INSERT INTO artist_images (artist_id, path, sort_order)
SELECT a.id, a.image, 1 FROM artists a
WHERE a.name IN ('Armin van Buuren','Tiesto','Hardwell','Martin Garrix','Afrojack','Nicky Romero')
  AND a.image IS NOT NULL;
