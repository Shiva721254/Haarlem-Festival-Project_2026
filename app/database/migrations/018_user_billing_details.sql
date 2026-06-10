-- Billing details for invoices: a phone number and address on the user account.
ALTER TABLE users
    ADD COLUMN phone   VARCHAR(40)  NULL,
    ADD COLUMN address VARCHAR(255) NULL;
