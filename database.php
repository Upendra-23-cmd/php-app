<?php

function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
        $_ENV[trim($key)] = trim($value);
    }
}

loadEnv(__DIR__ . '/.env');

define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_PASS', getenv('DB_PASS'));

// ✅ SINGLE SOURCE OF TRUTH
$DB_HOST = getenv('DB_HOST') ?: 'database-1.cczouquwokjr.us-east-1.rds.amazonaws.com';
$DB_USER = getenv('DB_USER') ?: 'admin';
$DB_PASS = getenv('DB_PASS') ?: 'Admin1232003';
$DB_NAME = getenv('DB_NAME') ?: 'todo_app';
$DB_PORT = getenv('DB_PORT') ?: 3306;

function getConnection() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT;

    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            'error' => 'DB Connection Failed',
            'details' => $conn->connect_error
        ]));
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
