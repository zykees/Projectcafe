CREATE DATABASE projectcafe;

USE projectcafe;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    picture_url TEXT,
    google_id VARCHAR(255),
    line_id VARCHAR(255) NULL,
    remember_token VARCHAR(64) NULL,
    token_expires DATETIME NULL;
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
