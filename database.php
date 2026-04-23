<?php
// ─── Load .env file (for local/non-Docker usage) ──────────────────────────────
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        $parts = explode('=', $line, 2);
        $key   = trim($parts[0]);
        $value = trim($parts[1]);
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

// Load .env if exists (local development)
loadEnv(__DIR__ . '/.env');

// ─── Read from environment variables (Docker / CodeBuild / RDS) ───────────────
define('DB_HOST', getenv('DB_HOST') ?: 'database-1.cczouquwokjr.us-east-1.rds.amazonaws.com');
define('DB_USER', getenv('DB_USER') ?: 'admin');
define('DB_PASS', getenv('DB_PASS') ?: 'Admin1232003');   // ✅ Fixed: was DB_PASSWORD
define('DB_NAME', getenv('DB_NAME') ?: 'todo_app');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));

// ─── Connection ───────────────────────────────────────────────────────────────
loadEnv(__DIR__ . '/.env');

define('DB_HOST', getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost');
define('DB_USER', getenv('DB_USER') ? getenv('DB_USER') : 'root');
define('DB_PASS', getenv('DB_PASS') ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ? getenv('DB_NAME') : 'todo_app');
define('DB_PORT', (int)(getenv('DB_PORT') ? getenv('DB_PORT') : 3306));

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            'error'   => 'Connection failed',
            'details' => $conn->connect_error
        ]));
        die(json_encode(array(
            'error'   => 'Connection failed',
            'details' => $conn->connect_error
        )));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

