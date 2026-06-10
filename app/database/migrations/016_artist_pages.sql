-- Participant-level artist pages: career highlights, important tracks, a
-- (simulated) audio excerpt, and a gallery of at least three images.
-- The schedule of appearances is derived from event_artist at runtime.

ALTER TABLE artists
    ADD COLUMN career_highlights TEXT NULL,
    ADD COLUMN tracks TEXT NULL,
    ADD COLUMN audio_url VARCHAR(255) NULL;

CREATE TABLE IF NOT EXISTS artist_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artist_id INT NOT NULL,
    path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_artist_images_artist FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --- Example content (replace with final content via the admin) -------------
-- DANCE! artists (public facts).
UPDATE artists SET
    bio = 'Dutch DJ and producer, a defining name in big-room house.',
    career_highlights = 'Voted #1 in the DJ Mag Top 100 DJs in 2013 and 2014. Founder of the label Revealed Recordings and host of the Hardwell On Air radio show. Known for festival main-stage sets worldwide.',
    tracks = 'Spaceman; Apollo; Dare You; Call Me A Spaceman',
    audio_url = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3'
WHERE name = 'Hardwell';

UPDATE artists SET
    bio = 'Dutch DJ and producer, a leading figure in trance music.',
    career_highlights = 'Five-time #1 in the DJ Mag Top 100 DJs. Host of the long-running A State of Trance radio show, broadcast to millions of listeners. Grammy-nominated for the single "This Is What It Feels Like".',
    tracks = 'This Is What It Feels Like; In and Out of Love; Blah Blah Blah',
    audio_url = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3'
WHERE name = 'Armin van Buuren';

-- Haarlem Jazz artists (illustrative; replace with final content).
UPDATE artists SET
    bio = 'A high-energy band bringing New Orleans-style jazz and funk to the stage.',
    career_highlights = 'A regular festival act known for brass-driven grooves and an infectious live show that gets audiences dancing.',
    tracks = 'Gumbo Groove; Second Line; Bourbon Street',
    audio_url = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3'
WHERE name = 'Gumbo Kings';

UPDATE artists SET
    bio = 'An atmospheric act blending jazz, soul and lounge textures.',
    career_highlights = 'Praised for cinematic, late-night soundscapes and a distinctive vocal sound that suits intimate festival halls.',
    tracks = 'Sailing; Are You Listening?; In Motion',
    audio_url = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3'
WHERE name = 'Gare du Nord';

-- Three gallery images per seeded artist (placeholder photos; replace later).
INSERT INTO artist_images (artist_id, path, sort_order)
SELECT a.id, CONCAT('https://picsum.photos/seed/', a.id, 'a/640/420'), 1 FROM artists a
WHERE a.name IN ('Hardwell','Armin van Buuren','Gumbo Kings','Gare du Nord');
INSERT INTO artist_images (artist_id, path, sort_order)
SELECT a.id, CONCAT('https://picsum.photos/seed/', a.id, 'b/640/420'), 2 FROM artists a
WHERE a.name IN ('Hardwell','Armin van Buuren','Gumbo Kings','Gare du Nord');
INSERT INTO artist_images (artist_id, path, sort_order)
SELECT a.id, CONCAT('https://picsum.photos/seed/', a.id, 'c/640/420'), 3 FROM artists a
WHERE a.name IN ('Hardwell','Armin van Buuren','Gumbo Kings','Gare du Nord');
