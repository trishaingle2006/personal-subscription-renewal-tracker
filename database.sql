CREATE DATABASE IF NOT EXISTS subscription_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE subscription_tracker;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    username VARCHAR(30) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    category ENUM('Entertainment','Music','Fitness','Technology','Education','Food','Travel','Other') NOT NULL,
    plan_name VARCHAR(100) NOT NULL,
    billing_cycle ENUM('Monthly','Yearly') NOT NULL,
    amount DECIMAL(10,2) UNSIGNED NOT NULL,
    renewal_date DATE NOT NULL,
    status ENUM('Active','Paused','Cancelled') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_service_name (service_name),
    INDEX idx_category_status (category, status),
    INDEX idx_renewal_date (renewal_date)
) ENGINE=InnoDB;

INSERT INTO subscriptions (service_name, category, plan_name, billing_cycle, amount, renewal_date, status) VALUES
('Spotify', 'Music', 'Individual', 'Monthly', 119.00, '2026-09-18', 'Active'),
('FitTrack Pro', 'Fitness', 'Annual Plus', 'Yearly', 1499.00, '2027-01-10', 'Active'),
('O''Reilly Learning', 'Education', 'Online Learning', 'Monthly', 999.00, '2026-09-25', 'Paused'),
('CloudBox', 'Technology', '100 GB', 'Monthly', 130.00, '2026-10-02', 'Active'),
('Cinema Stream', 'Entertainment', 'Standard', 'Yearly', 1299.00, '2027-03-15', 'Cancelled');

