<?php
/**
 * Simple PDO connection helper.
 * Update the values below to match your XAMPP/WAMP MySQL settings.
 */

// DB settings
$DB_HOST = 'localhost';
$DB_NAME = 'dps_db';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Throwable $e) {
    http_response_code(500);
    echo "Database connection failed. Please check f/config/db.php settings.\n";
    echo htmlspecialchars($e->getMessage());
    exit;
}
