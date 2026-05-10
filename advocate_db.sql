
SELECT 'CREATE DATABASE advocate'
WHERE NOT EXISTS ( SELECT FROM pg_database WHERE datname = 'advocate');

-- make connection to already created database --
\c advocate

CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE IF NOT EXISTS REGISTRATION_TABLE (
    id SERIAl PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    password TEXT NOT NULL,
    enrollment_number VARCHAR(20) UNIQUE NOT NULL,
    mobile VARCHAR(500) NOT NULL,
    email VARCHAR(500) NOT NULL,
    state VARCHAR(50),
    district VARCHAR(50),
    pin_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ADVOCATE_TABLE (
    id SERIAL PRIMARY KEY,
    advocate_id INTEGER REFERENCES REGISTRATION_TABLE(id) ON DELETE CASCADE,
    date_of_birth DATE,
    date_of_enrollment DATE,
    photograph_path VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


