-- Usernames for username-or-email login.
-- Existing accounts get a stable generated username based on their email prefix.

ALTER TABLE users
    ADD COLUMN Username VARCHAR(80) NULL AFTER UserId;

UPDATE users
SET Username = CONCAT(
    LOWER(REGEXP_REPLACE(SUBSTRING_INDEX(Email, '@', 1), '[^a-zA-Z0-9_]', '')),
    UserId
)
WHERE Username IS NULL OR Username = '';

ALTER TABLE users
    MODIFY Username VARCHAR(80) NOT NULL,
    ADD UNIQUE KEY uq_users_username (Username);
