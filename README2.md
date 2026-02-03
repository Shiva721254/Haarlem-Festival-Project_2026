Use this site key in the HTML code your site serves to users:
6LcRjF8sAAAAAA8Yhjp-lQFxIpea53uZMsYTbMSR

Use this secret key for communication between your site and reCAPTCHA:
6LcRjF8sAAAAAKP6omk4A3pM-mAqZcNmQKMiZCgN

CREATE TABLE users (
    UserId INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE,
    
    -- Enum or Varchar for Role; default set to 'Customer'
    Role VARCHAR(50) NOT NULL DEFAULT 'Customer',
    
    -- Booleans stored as TINYINT(1) in SQL
    isVerified BOOLEAN DEFAULT FALSE,
    isActive BOOLEAN DEFAULT TRUE,
    
    -- Tokens and Verification
    verification_token VARCHAR(255) NULL,
    verification_token_expires_at DATETIME NULL,
    verified_at DATETIME NULL,
    
    -- Security
    Password VARCHAR(255) NOT NULL,
    reset_token_hash VARCHAR(255) NULL,
    reset_token_expires_at DATETIME NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);