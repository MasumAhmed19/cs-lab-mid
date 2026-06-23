-- Crime Report Database Setup
CREATE DATABASE IF NOT EXISTS crime_report;
USE crime_report;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(200),
    posted_by VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed users (plain text passwords - intentionally insecure for lab)
INSERT INTO users (username, password, email) VALUES
('admin', 'admin123', 'admin@lab.com'),
('alice', 'pass123', 'alice@lab.com'),
('attacker', 'pass123', 'attacker@lab.com'),
('bob', 'pass456', 'bob@lab.com');

-- Seed crime reports
INSERT INTO reports (title, description, location, posted_by) VALUES
('Robbery at Main Street', 'A robbery occurred near the bank at Main Street around 10pm.', 'Main Street', 'alice'),
('Car Theft', 'A blue Toyota Corolla was stolen from parking lot B.', 'Central Mall', 'bob'),
('Vandalism at Park', 'Graffiti found on the public restroom walls in city park.', 'City Park', 'alice');
