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
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit('The application is temporarily unable to connect to the database.');
}

