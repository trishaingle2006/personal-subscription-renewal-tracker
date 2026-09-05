<?php
declare(strict_types=1);

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(
        env_value('DB_HOST', 'localhost'),
        env_value('DB_USER', 'root'),
        env_value('DB_PASSWORD', ''),
        env_value('DB_NAME', 'subscription_tracker'),
        (int) env_value('DB_PORT', '3306')
    );
    $conn->set_charset('utf8mb4');

    // Initialize a new hosted database safely. Existing tables and records are
    // left unchanged, so this can run on every application start.
    $conn->query(
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(80) NOT NULL,
            username VARCHAR(30) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS subscriptions (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $recordCount = (int) $conn->query('SELECT COUNT(*) FROM subscriptions')->fetch_row()[0];
    if ($recordCount === 0) {
        $conn->query(
            "INSERT INTO subscriptions
                (service_name, category, plan_name, billing_cycle, amount, renewal_date, status)
             VALUES
                ('Spotify', 'Music', 'Individual', 'Monthly', 119.00, '2026-09-18', 'Active'),
                ('FitTrack Pro', 'Fitness', 'Annual Plus', 'Yearly', 1499.00, '2027-01-10', 'Active'),
                ('O''Reilly Learning', 'Education', 'Online Learning', 'Monthly', 999.00, '2026-09-25', 'Paused'),
                ('CloudBox', 'Technology', '100 GB', 'Monthly', 130.00, '2026-10-02', 'Active'),
                ('Cinema Stream', 'Entertainment', 'Standard', 'Yearly', 1299.00, '2027-03-15', 'Cancelled')"
        );
    }
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit('The application is temporarily unable to connect to the database.');
}
