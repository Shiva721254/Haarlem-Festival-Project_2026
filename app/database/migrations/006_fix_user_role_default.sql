-- The users.Role default was 'Customer' (capitalised), but the UserRole enum
-- backs onto lowercase values ('admin', 'employee', 'customer'). A row inserted
-- without an explicit role therefore crashed on login when mapped to the enum.
-- Align the column default with the enum, and correct any existing rows.

ALTER TABLE users ALTER COLUMN Role SET DEFAULT 'customer';

UPDATE users SET Role = 'customer' WHERE Role = 'Customer';
UPDATE users SET Role = 'admin'    WHERE Role = 'Admin';
UPDATE users SET Role = 'employee' WHERE Role = 'Employee';
