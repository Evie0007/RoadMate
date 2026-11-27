<?php
// Shared database connection for every RoadMate page.
// Use it at the top of any page with:
//     require_once __DIR__ . '/../includes/db.php';
// Then run queries with $conn (a mysqli connection).

$configFile = __DIR__ . '/../config/config.php';

if (!file_exists($configFile)) {
    http_response_code(500);
    exit('Missing config/config.php. Copy config/config.example.php to config/config.php and add your MySQL password.');
}

$config = require $configFile;

// Turn MySQL errors into PHP exceptions, so a failed query stops the page
// instead of failing silently. Trigger messages (like a double booking)
// arrive as mysqli_sql_exception with the message from the trigger.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(
        $config['host'],
        $config['user'],
        $config['password'],
        $config['database'],
        (int) $config['port']
    );
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('Could not connect to the database: ' . htmlspecialchars($e->getMessage()));
}
