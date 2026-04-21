<?php
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'admin123');
define('DB_NAME', getenv('DB_NAME') ?: 'todo_app');

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// <?php
// // ─── Load .env file manually if not using Docker ──────────────────────────────
// function loadEnv($path) {
//     if (!file_exists($path)) return;
//     $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//     foreach ($lines as $line) {
//         if (strpos(trim($line), '#') === 0) continue; // skip comments
//         [$key, $value] = explode('=', $line, 2);
//         $key   = trim($key);
//         $value = trim($value);
//         putenv("$key=$value");
//         $_ENV[$key] = $value;
//     }
// }

// // Load .env from project root (only needed outside Docker)
// loadEnv(__DIR__ . '/.env');

// // ─── Read credentials from environment variables ───────────────────────────────
// define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
// define('DB_USER', getenv('DB_USER') ?: 'root');
// define('DB_PASS', getenv('DB_PASS') ?: '');
// define('DB_NAME', getenv('DB_NAME') ?: 'todo_app');
// define('DB_PORT', getenv('DB_PORT') ?: 3306);

// // ─── Connection function ───────────────────────────────────────────────────────
// function getConnection() {
//     $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

//     if ($conn->connect_error) {
//         http_response_code(500);
//         die(json_encode([
//             'error'   => 'Database connection failed',
//             'details' => $conn->connect_error   // remove this line in production
//         ]));
//     }

//     $conn->set_charset('utf8mb4');
//     return $conn;
// }