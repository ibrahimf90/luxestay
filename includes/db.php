<?php
// ── includes/db.php ──────────────────────────────────────────
// Database connection for WAMP / phpMyAdmin
// Adjust DB_PASS if you set a root password in WAMP.

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // default WAMP password is empty
define('DB_NAME', 'luxestay_db');
define('DB_PORT', 3306);

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}
