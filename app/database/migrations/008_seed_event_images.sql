-- Give the seeded demo events (and a few artists/venues) real image paths that
-- exist under app/public/assets/images, so the overview/detail pages render
-- with pictures during development. Real content will come via CMS uploads.

UPDATE events SET image = '/assets/images/gumbo.jpg'        WHERE title = 'Opening Night: The Blue Notes';
UPDATE events SET image = '/assets/images/rose.jpg'         WHERE title = 'Quartet Amstel Live';
UPDATE events SET image = '/assets/images/grote-markt.png'  WHERE title = 'Sunrise Set with DJ Tulip';
UPDATE events SET image = '/assets/images/Patronaat.png'    WHERE title = 'Dinner at Ratatouille';

UPDATE artists SET image = '/assets/images/gumbo.jpg' WHERE name = 'The Blue Notes';
UPDATE artists SET image = '/assets/images/rose.jpg'  WHERE name = 'Quartet Amstel';
UPDATE artists SET image = '/assets/images/chris.jpg' WHERE name = 'DJ Tulip';

UPDATE venues SET image = '/assets/images/Patronaat.png'   WHERE name = 'Patronaat';
UPDATE venues SET image = '/assets/images/grote-markt.png' WHERE name = 'Grote Markt Stage';
