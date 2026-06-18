-- Seed one account per role for development and demo purposes.
-- Passwords (bcrypt, cost 12):
--   admin@haarlem.nl     → Admin1234!
--   employee@haarlem.nl  → Employee1234!
--   customer@haarlem.nl  → Customer1234!

INSERT INTO users (Username, FirstName, LastName, Email, Password, Role, isVerified, isActive)
VALUES
    ('admin',    'Admin',    'Haarlem',  'admin@haarlem.nl',    '$2y$12$1HfzqNK/fmUmJDa1.dfcYOvwxGjt3SMSTXbkY6v..zr2OKOZixD.u', 'admin',    TRUE, TRUE),
    ('employee', 'Employee', 'Haarlem',  'employee@haarlem.nl', '$2y$12$b.8Y1ktvT35Xff6/jRkubOHe5qAxfFlMeJ9b.6ewAX8a0s4qMsr/i', 'employee', TRUE, TRUE),
    ('customer', 'Customer', 'Haarlem',  'customer@haarlem.nl', '$2y$12$uYGwnRNE7zOUXQ9RLClMUuM.hcMT3BwOU64HmBQD2NW4aI0Il4HRu', 'customer', TRUE, TRUE);
